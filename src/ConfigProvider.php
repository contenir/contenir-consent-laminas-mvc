<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc;

use Contenir\Consent\Laminas\Mvc\Asset\AssetController;
use Contenir\Consent\Laminas\Mvc\Factory\ConsentSiteFactory;
use Contenir\Consent\Laminas\Mvc\Factory\HelperFactory;
use Contenir\Consent\Laminas\Mvc\View\Helper\Consent;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentPreferencesLink;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentScript;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;

/**
 * What the module registers: the site's ConsentSite, the consent(),
 * consentScript() and consentPreferencesLink() view helpers, and the route
 * that serves the banner's files ahead of the site's own routes.
 *
 * @api
 */
final class ConfigProvider
{
    /**
     * The route that serves the banner's files.
     */
    public const string ROUTE = 'contenir-consent-asset';

    /**
     * Ahead of a site's catch-all page route.
     */
    public const int ROUTE_PRIORITY = 1000;

    /**
     * @return array<string, mixed>
     */
    public function __invoke(): array
    {
        return [
            'service_manager' => [
                'factories' => [
                    ConsentSite::class => ConsentSiteFactory::class,
                ],
            ],
            'view_helpers'    => [
                'aliases'   => [
                    'consent'                => Consent::class,
                    'consentScript'          => ConsentScript::class,
                    'consentPreferencesLink' => ConsentPreferencesLink::class,
                ],
                'factories' => [
                    Consent::class                => HelperFactory::class,
                    ConsentScript::class          => HelperFactory::class,
                    ConsentPreferencesLink::class => HelperFactory::class,
                ],
            ],
            'controllers'     => [
                'factories' => [
                    AssetController::class => InvokableFactory::class,
                ],
            ],
            'router'          => [
                'routes' => [
                    self::ROUTE => [
                        'type'     => Segment::class,
                        'priority' => self::ROUTE_PRIORITY,
                        'options'  => [
                            'route'       => '/_consent/:file',
                            'constraints' => ['file' => 'consent-[a-f0-9]{12}\.(?:js|css)'],
                            'defaults'    => ['controller' => AssetController::class],
                        ],
                    ],
                ],
            ],
        ];
    }
}
