<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Integration;

use Contenir\Consent\Assets;
use Contenir\Consent\Laminas\Mvc\Module;
use Contenir\Consent\Laminas\Mvc\Tests\Trait\TempDirectoryTrait;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\HelperPluginManager;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\TemplatePathStack;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function preg_match;
use function preg_quote;

/**
 * A layout calling the consent helpers through laminas-view, with the
 * module's configuration.
 */
#[Group('integration')]
final class LayoutTest extends TestCase
{
    use TempDirectoryTrait;

    #[Test]
    public function writesOnlyTheEmbedRunningWhileConsentIsDisabled(): void
    {
        $html = $this->renderer()->render('layout');

        static::assertSame(
            1,
            preg_match(
                '#<head>\n</head>\n<body>\n<script src="https://www\.youtube\.com/iframe_api" nonce="page-nonce"></script>'
                    . '<footer></footer>\n</body>#',
                $html,
            ),
        );
    }

    #[Test]
    public function writesTheBannerGatedEmbedsAndThePreferencesLink(): void
    {
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => ['consent']]]);

        $html = $this->renderer()->render('layout');

        static::assertSame(
            1,
            preg_match(
                '#<head>\n<link rel="stylesheet" href="/_consent/'
                    . preg_quote(Assets::stylesheetName(), delimiter: '#')
                    . '">\n'
                    . '<script nonce="page-nonce">window\.dataLayer.*</script></head>\n<body>\n'
                    . '<script type="text/plain" data-category="preferences" src="https://www\.youtube\.com/iframe_api" nonce="page-nonce"></script>'
                    . '<footer><a href="\#" data-cc="show-preferencesModal">Cookie preferences</a></footer>\n'
                    . '<script src="/_consent/'
                    . preg_quote(Assets::scriptName(), delimiter: '#')
                    . '" nonce="page-nonce"></script>\n'
                    . '<script nonce="page-nonce">window\.ContenirConsent && window\.ContenirConsent\.start\(.*\);</script></body>#s',
                $html,
            ),
        );
    }

    #[Override]
    protected function setUp(): void
    {
        $this->makeTempDirectory();
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->removeTempDirectory();
    }

    private function renderer(): PhpRenderer
    {
        /** @var array{service_manager: array<string, mixed>, view_helpers: array<string, mixed>} $config */
        $config   = (new Module())->getConfig();
        $services = new ServiceManager($config['service_manager']);
        $services->setService('config', ['contenir_consent' => ['extensions_config' => $this->directory]]);

        $renderer = new PhpRenderer();
        $renderer->setHelperPluginManager(new HelperPluginManager($services, $config['view_helpers']));
        $renderer->setResolver(new TemplatePathStack(['script_paths' => [__DIR__ . '/../TestAsset/view']]));

        return $renderer;
    }
}
