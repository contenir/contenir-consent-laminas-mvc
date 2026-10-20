<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit;

use Contenir\Consent\Laminas\Mvc\Asset\AssetController;
use Contenir\Consent\Laminas\Mvc\ConfigProvider;
use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Laminas\Mvc\Factory\ConsentSiteFactory;
use Contenir\Consent\Laminas\Mvc\Factory\HelperFactory;
use Contenir\Consent\Laminas\Mvc\Module;
use Contenir\Consent\Laminas\Mvc\View\Helper\Consent;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentPreferencesLink;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentScript;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\Factory\InvokableFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ConfigProviderTest extends TestCase
{
    #[Test]
    public function givesTheModuleTheSameConfig(): void
    {
        static::assertSame((new ConfigProvider())(), (new Module())->getConfig());
    }

    #[Test]
    public function registersTheSitesConsentTheHelpersAndTheAssetRoute(): void
    {
        static::assertSame(
            [
                'service_manager' => ['factories' => [ConsentSite::class => ConsentSiteFactory::class]],
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
                'controllers'     => ['factories' => [AssetController::class => InvokableFactory::class]],
                'router'          => [
                    'routes' => [
                        'contenir-consent-asset' => [
                            'type'     => Segment::class,
                            'priority' => 1000,
                            'options'  => [
                                'route'       => '/_consent/:file',
                                'constraints' => ['file' => 'consent-[a-f0-9]{12}\.(?:js|css)'],
                                'defaults'    => ['controller' => AssetController::class],
                            ],
                        ],
                    ],
                ],
            ],
            (new ConfigProvider())(),
        );
    }
}
