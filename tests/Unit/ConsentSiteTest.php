<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit;

use Contenir\Consent\Category;
use Contenir\Consent\ConsentConfig;
use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\Factory\SiteFactory;
use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\FixedNonce;
use Contenir\Consent\RegionalMode;
use Contenir\Consent\Script\Script;
use Contenir\Consent\Texts;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function explode;
use function preg_match;
use function str_starts_with;

#[Group('unit')]
final class ConsentSiteTest extends TestCase
{
    #[Test]
    public function escapesTheStylesheetsAddress(): void
    {
        $site = new ConsentSite(true, new ConsentConfig(), true, null, '/a.js', "/a'b.css?x=1&y=2");

        static::assertSame('<link rel="stylesheet" href="/a&apos;b.css?x=1&amp;y=2">', $site->head());
    }

    #[Test]
    public function gatesTheSitesOwnScriptsWhileTheAddOnIsEnabled(): void
    {
        $script = Script::src('https://maps.example/api.js', Category::Preferences);

        static::assertSame(
            [
                '<script type="text/plain" data-category="preferences" src="https://maps.example/api.js" nonce="n">'
                    . '</script>',
                '<script src="https://maps.example/api.js" nonce="n"></script>',
            ],
            [SiteFactory::make()->script($script, 'n'), SiteFactory::make(enabled: false)->script($script, 'n')],
        );
    }

    #[Test]
    public function leavesConsentModeToTheTrackingAddOn(): void
    {
        static::assertSame(
            '<link rel="stylesheet" href="/_consent/consent-abc.css">',
            SiteFactory::make(tracking: true)->head('n0nce'),
        );
    }

    #[Test]
    public function linksTheStylesheetAndDeniesConsentModeOnASiteWithoutTracking(): void
    {
        $lines = explode("\n", SiteFactory::make()->head('n0nce'));

        static::assertSame(
            ['<link rel="stylesheet" href="/_consent/consent-abc.css">', true],
            [$lines[0], str_starts_with($lines[1], '<script nonce="n0nce">window.dataLayer = window.dataLayer || [];')],
        );
    }

    #[Test]
    public function loadsAndStartsTheBannerForTheVisitorsCountry(): void
    {
        $site = SiteFactory::make(
            config: new ConsentConfig(regionalMode: RegionalMode::ByCountry),
            country: 'AU',
            nonces: new FixedNonce('n0nce'),
        );
        $lines = explode("\n", $site->bodyEnd());

        static::assertSame(
            ['<script src="/_consent/consent-abc.js" nonce="n0nce"></script>', 1],
            [$lines[0], preg_match('/^<script nonce="n0nce">window\.ContenirConsent.*"mode":"opt-out"/', $lines[1])],
        );
    }

    #[Test]
    public function prefersTheNonceItIsGiven(): void
    {
        $head = SiteFactory::make(nonces: new FixedNonce('from-app'))->head('given');

        static::assertSame(1, preg_match('/<script nonce="given">/', $head));
    }

    #[Test]
    public function takesTheNonceFromTheApplicationWhenGivenNone(): void
    {
        $head = SiteFactory::make(nonces: new FixedNonce('from-app'))->head();

        static::assertSame(1, preg_match('/<script nonce="from-app">/', $head));
    }

    #[Test]
    public function writesNothingWhileTheAddOnIsDisabled(): void
    {
        $site = SiteFactory::make(enabled: false);

        static::assertSame(['', '', ''], [$site->head('n'), $site->bodyEnd('n'), $site->preferencesLink()]);
    }

    #[Test]
    public function writesTheLinkThatOpensThePreferencesInTheSitesWords(): void
    {
        $site = SiteFactory::make(config: new ConsentConfig(texts: new Texts(preferencesLink: 'Cookies')));

        static::assertSame(
            [
                '<a href="#" data-cc="show-preferencesModal" class="f">Cookies</a>',
                '<a href="#" data-cc="show-preferencesModal">Your choices</a>',
            ],
            [$site->preferencesLink(['class' => 'f']), $site->preferencesLink(label: 'Your choices')],
        );
    }

    #[Test]
    public function writesTheSitesOwnScriptsWithTheApplicationsNonce(): void
    {
        $html = SiteFactory::make(nonces: new FixedNonce('app'))->script(Script::inline('a();', Category::Necessary));

        static::assertSame('<script nonce="app">a();</script>', $html);
    }
}
