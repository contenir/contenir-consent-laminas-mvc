<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit\View\Helper;

use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\Factory\SiteFactory;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentPreferencesLink;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ConsentPreferencesLinkTest extends TestCase
{
    #[Test]
    public function writesTheLinkWithTheAttributesAndLabelGiven(): void
    {
        static::assertSame(
            '<a href="#" data-cc="show-preferencesModal" class="f">Cookies</a>',
            (new ConsentPreferencesLink(SiteFactory::make()))(['class' => 'f'], label: 'Cookies'),
        );
    }
}
