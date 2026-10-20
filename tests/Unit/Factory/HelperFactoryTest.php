<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit\Factory;

use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Laminas\Mvc\Factory\HelperFactory;
use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\ArrayContainer;
use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\Factory\SiteFactory;
use Contenir\Consent\Laminas\Mvc\View\Helper\Consent;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentPreferencesLink;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentScript;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class HelperFactoryTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function helpers(): array
    {
        return [
            'consent()'                => [Consent::class],
            'consentScript()'          => [ConsentScript::class],
            'consentPreferencesLink()' => [ConsentPreferencesLink::class],
        ];
    }

    #[Test]
    #[DataProvider('helpers')]
    public function buildsEachHelperAroundTheSitesConsent(string $helper): void
    {
        $container = new ArrayContainer([ConsentSite::class => SiteFactory::make()]);

        static::assertInstanceOf($helper, (new HelperFactory())($container, $helper));
    }

    #[Test]
    public function refusesAHelperItDoesNotBuild(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("HelperFactory doesn't build " . self::class);

        (new HelperFactory())(new ArrayContainer([ConsentSite::class => SiteFactory::make()]), self::class);
    }
}
