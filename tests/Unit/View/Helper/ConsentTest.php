<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit\View\Helper;

use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\Factory\SiteFactory;
use Contenir\Consent\Laminas\Mvc\View\Helper\Consent;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ConsentTest extends TestCase
{
    #[Test]
    public function returnsItselfWhenCalled(): void
    {
        $helper = new Consent(SiteFactory::make());

        static::assertSame($helper, $helper());
    }

    #[Test]
    public function writesTheSitesBodyEnd(): void
    {
        $site = SiteFactory::make();

        static::assertSame($site->bodyEnd('n'), (new Consent($site))->bodyEnd('n'));
    }

    #[Test]
    public function writesTheSitesHead(): void
    {
        $site = SiteFactory::make();

        static::assertSame($site->head('n'), (new Consent($site))->head('n'));
    }
}
