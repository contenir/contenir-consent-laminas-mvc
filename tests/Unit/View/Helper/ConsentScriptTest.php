<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit\View\Helper;

use Contenir\Consent\Laminas\Mvc\Tests\TestAsset\Factory\SiteFactory;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentScript;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ConsentScriptTest extends TestCase
{
    #[Test]
    public function gatesAnInlineScript(): void
    {
        static::assertSame(
            '<script type="text/plain" data-category="marketing" id="chat">initChat();</script>',
            (new ConsentScript(SiteFactory::make()))('marketing', code: 'initChat();', attributes: ['id' => 'chat']),
        );
    }

    #[Test]
    public function gatesAScriptLoadedFromAUrl(): void
    {
        static::assertSame(
            '<script type="text/plain" data-category="preferences" src="https://www.youtube.com/iframe_api" nonce="n" '
                . 'async></script>',
            (new ConsentScript(SiteFactory::make()))(
                'preferences',
                src: 'https://www.youtube.com/iframe_api',
                attributes: ['async' => true],
                nonce: 'n',
            ),
        );
    }

    #[Test]
    public function refusesACategoryThatDoesNotExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            '"social" is not a consent category: use necessary, analytics, marketing or preferences',
        );

        (new ConsentScript(SiteFactory::make()))('social', src: '/a.js');
    }

    #[Test]
    public function refusesAScriptWithBothSrcAndCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Give consentScript() either a src or code, not both');

        (new ConsentScript(SiteFactory::make()))('marketing', src: '/a.js', code: 'a();');
    }

    #[Test]
    public function refusesAScriptWithNeitherSrcNorCode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Give consentScript() either a src or code, not both');

        (new ConsentScript(SiteFactory::make()))('marketing');
    }
}
