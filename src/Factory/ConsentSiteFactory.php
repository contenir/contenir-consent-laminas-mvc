<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Factory;

use Contenir\Consent\Assets;
use Contenir\Consent\ConsentConfig;
use Contenir\Consent\Country;
use Contenir\Consent\Exception\InvalidServiceException;
use Contenir\Consent\Laminas\Mvc\ConfigProvider;
use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Laminas\Mvc\EnabledAddOns;
use Contenir\Consent\Laminas\Mvc\NonceProviderInterface;
use Contenir\Consent\Laminas\Mvc\SiteOptions;
use Laminas\Http\Header\HeaderInterface;
use Laminas\Http\Request;
use Laminas\Router\RouteStackInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

use function is_string;

/**
 * Builds the site's consent from the application's config (SiteOptions),
 * the add-ons the site has enabled, the request's country header and the
 * router, which says where the banner's files are served.
 *
 * @api
 */
final class ConsentSiteFactory
{
    public const string HANDLE = 'consent';

    public const string TRACKING = 'tracking';

    /**
     * Where the banner's files are served when the application has no
     * router to ask.
     */
    public const string ASSET_PATH = '/_consent/';

    /**
     * @throws ContainerExceptionInterface
     *
     * @mago-expect analysis:mixed-assignment The router assembles untyped values; the URL is checked here.
     */
    private function assetUrl(ContainerInterface $container, string $file): string
    {
        $router = $container->has('Router') ? $container->get('Router') : null;
        $url    = $router instanceof RouteStackInterface
            ? $router->assemble(['file' => $file], ['name' => ConfigProvider::ROUTE])
            : null;

        return is_string($url) ? $url : self::ASSET_PATH . $file;
    }

    /**
     * @throws ContainerExceptionInterface
     *
     * @mago-expect analysis:mixed-assignment The container's services are untyped; the request is checked here.
     */
    private function country(ContainerInterface $container, string $header): ?Country
    {
        $request = $container->has('Request') ? $container->get('Request') : null;

        /** @var HeaderInterface|false|null $line One header of that name, or none */
        $line = $request instanceof Request ? $request->getHeader($header) : null;

        return Country::fromHeader($line instanceof HeaderInterface ? $line->getFieldValue() : null);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws InvalidServiceException When one of the site's services can't be used.
     */
    public function __invoke(ContainerInterface $container): ConsentSite
    {
        $options = SiteOptions::fromConfig($container->get('config'));
        $enabled = EnabledAddOns::fromDirectory($options->extensionsConfig);
        $consent = ConsentConfig::fromSettings($options->settings, $options->services, $options->texts);
        $nonces  = $container->has(NonceProviderInterface::class)
            ? $container->get(NonceProviderInterface::class)
            : null;

        return new ConsentSite(
            $enabled->has(self::HANDLE),
            $consent,
            $enabled->has(self::TRACKING),
            $this->country($container, $consent->countryHeader),
            $this->assetUrl($container, Assets::scriptName()),
            $this->assetUrl($container, Assets::stylesheetName()),
            $nonces instanceof NonceProviderInterface ? $nonces : null,
        );
    }
}
