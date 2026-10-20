<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Integration;

use Contenir\Consent\Assets;
use Contenir\Consent\GoogleConsentMode;
use Contenir\Consent\Laminas\Mvc\ConfigProvider;
use Contenir\Consent\Laminas\Mvc\Factory\ConsentSiteFactory;
use Contenir\Consent\Laminas\Mvc\NonceProviderInterface;
use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\ArrayContainer;
use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\FixedNonce;
use Contenir\Consent\Laminas\Mvc\Tests\Trait\TempDirectoryTrait;
use Laminas\Http\Request;
use Laminas\Router\Http\TreeRouteStack;
use Laminas\Router\RouteStackInterface;
use Override;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function preg_match;

/**
 * The site's consent built from config files on disk, a request and the
 * module's route.
 */
#[Group('integration')]
final class ConsentSiteFactoryTest extends TestCase
{
    use TempDirectoryTrait;

    #[Test]
    public function asksTheRouterWhereTheBannersFilesAre(): void
    {
        $router = TreeRouteStack::factory(['routes' => (new ConfigProvider())()['router']['routes']]);
        $router->setBaseUrl('/site');

        $site = (new ConsentSiteFactory())($this->container(services: ['Router' => $router]));

        static::assertSame(
            ['/site/_consent/' . Assets::scriptName(), '/site/_consent/' . Assets::stylesheetName()],
            [$site->scriptUrl, $site->stylesheetUrl],
        );
    }

    #[Test]
    public function ignoresANonceServiceThatIsNotANonceProvider(): void
    {
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => ['consent']]]);

        $site = (new ConsentSiteFactory())($this->container(services: [NonceProviderInterface::class => 'nonce']));

        static::assertSame(1, preg_match('/^<script src="[^"]+"><\/script>/', $site->bodyEnd()));
    }

    #[Test]
    public function knowsNoCountryWithoutTheHeaderOrAnHttpRequest(): void
    {
        $withoutHeader = (new ConsentSiteFactory())($this->container(services: ['Request' => new Request()]));
        $notHttp       = (new ConsentSiteFactory())($this->container(services: ['Request' => 'cli']));

        static::assertSame([null, null], [$withoutHeader->country, $notHttp->country]);
    }

    #[Test]
    public function knowsWhichAddOnsTheSiteHasEnabled(): void
    {
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => ['consent', 'tracking']]]);

        $site = (new ConsentSiteFactory())($this->container());

        static::assertSame([true, true], [$site->enabled, $site->tracking]);
    }

    #[Test]
    public function letsTheLocalListSwitchConsentOff(): void
    {
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => ['consent']]]);
        $this->writeConfig('extensions.local.php', ['extensions' => ['enabled' => ['tracking']]]);

        $site = (new ConsentSiteFactory())($this->container());

        static::assertSame([false, true], [$site->enabled, $site->tracking]);
    }

    #[Test]
    public function readsTheExtensionsConfigFromConfigAutoloadByDefault(): void
    {
        $site = (new ConsentSiteFactory())(new ArrayContainer(['config' => []]));

        static::assertFalse($site->enabled);
    }

    #[Test]
    public function readsTheSettingsAndTheSitesOwnServicesAndTexts(): void
    {
        $site = (new ConsentSiteFactory())($this->container([
            'settings'         => ['extensions' => ['consent' => ['google_consent_mode' => 'advanced']]],
            'contenir_consent' => [
                'extensions_config' => $this->directory,
                'services'          => ['youtube' => ['name' => 'YouTube', 'category' => 'preferences']],
                'texts'             => ['acceptAll' => 'Yes please'],
            ],
        ]));

        static::assertSame(
            [GoogleConsentMode::Advanced, 'youtube', 'Yes please'],
            [
                $site->config->googleConsentMode,
                $site->config->services->services[1]->key,
                $site->config->texts->acceptAll,
            ],
        );
    }

    #[Test]
    public function readsTheVisitorsCountryFromTheHeaderTheSettingsName(): void
    {
        $request = new Request();
        $request->getHeaders()->addHeaderLine('CloudFront-Viewer-Country', 'nz');

        $site = (new ConsentSiteFactory())($this->container(
            ['settings' => ['extensions' => ['consent' => ['country_header' => 'CloudFront-Viewer-Country']]]],
            ['Request' => $request],
        ));

        static::assertSame('NZ', $site->country?->code);
    }

    #[Test]
    public function servesTheFilesFromConsentWithoutARouterThatAnswers(): void
    {
        $router = $this->createStub(RouteStackInterface::class);
        $router->method('assemble')->willReturn(null);

        $withoutRouter = (new ConsentSiteFactory())($this->container());
        $silentRouter  = (new ConsentSiteFactory())($this->container(services: ['Router' => $router]));

        static::assertSame(
            ['/_consent/' . Assets::scriptName(), '/_consent/' . Assets::stylesheetName()],
            [$withoutRouter->scriptUrl, $silentRouter->stylesheetUrl],
        );
    }

    #[Test]
    public function takesTheNonceFromTheApplication(): void
    {
        $this->writeConfig('extensions.global.php', ['extensions' => ['enabled' => ['consent']]]);

        $site = (new ConsentSiteFactory())($this->container(services: [
            NonceProviderInterface::class => new FixedNonce('app-nonce'),
        ]));

        static::assertSame(1, preg_match('/<script nonce="app-nonce">/', $site->bodyEnd()));
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

    /**
     * @param array<string, mixed> $config
     * @param array<string, mixed> $services
     */
    private function container(array $config = [], array $services = []): ArrayContainer
    {
        return new ArrayContainer([
            'config' => [
                'contenir_consent' => ['extensions_config' => $this->directory],
                ...$config,
            ],
            ...$services,
        ]);
    }
}
