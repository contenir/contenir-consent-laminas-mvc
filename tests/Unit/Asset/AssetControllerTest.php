<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit\Asset;

use Contenir\Consent\Assets;
use Contenir\Consent\Laminas\Mvc\Asset\AssetController;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Stdlib\Request as StdlibRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class AssetControllerTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function unknownFiles(): array
    {
        return [
            'an old script'     => ['/_consent/consent-000000000000.js'],
            'an old stylesheet' => ['/_consent/consent-000000000000.css'],
            'something else'    => ['/_consent/'],
        ];
    }

    #[Test]
    public function answersOkWhateverTheResponseSaidBefore(): void
    {
        $response = (new Response())->setStatusCode(Response::STATUS_CODE_500);

        (new AssetController())->dispatch($this->request('/_consent/' . Assets::scriptName()), $response);

        static::assertSame(200, $response->getStatusCode());
    }

    #[Test]
    public function answersOnTheResponseItIsGiven(): void
    {
        $response = new Response();

        static::assertSame(
            $response,
            (new AssetController())->dispatch($this->request('/_consent/' . Assets::scriptName()), $response),
        );
    }

    #[Test]
    #[DataProvider('unknownFiles')]
    public function findsNoOtherFile(string $path): void
    {
        $response = (new AssetController())->dispatch($this->request($path));

        static::assertSame([404, 'Not found'], [$response->getStatusCode(), $response->getContent()]);
    }

    #[Test]
    public function findsNothingForARequestThatIsNotHttp(): void
    {
        static::assertSame(
            404,
            (new AssetController())->dispatch(new StdlibRequest())
                ->getStatusCode(),
        );
    }

    #[Test]
    public function servesTheScriptCacheableForAYear(): void
    {
        $response = (new AssetController())->dispatch($this->request('/_consent/' . Assets::scriptName()));

        static::assertSame(
            [
                200,
                'text/javascript; charset=utf-8',
                'immutable, max-age=31536000, public',
                'nosniff',
                Assets::script(),
            ],
            $this->described($response),
        );
    }

    #[Test]
    public function servesTheStylesheetCacheableForAYear(): void
    {
        $response = (new AssetController())->dispatch($this->request('/_consent/' . Assets::stylesheetName()));

        static::assertSame(
            [200, 'text/css; charset=utf-8', 'immutable, max-age=31536000, public', 'nosniff', Assets::stylesheet()],
            $this->described($response),
        );
    }

    /**
     * @return list<mixed>
     */
    private function described(Response $response): array
    {
        $headers = $response->getHeaders();

        return [
            $response->getStatusCode(),
            $headers->get('Content-Type')?->getFieldValue(),
            $headers->get('Cache-Control')?->getFieldValue(),
            $headers->get('X-Content-Type-Options')?->getFieldValue(),
            $response->getContent(),
        ];
    }

    private function request(string $path): Request
    {
        $request = new Request();
        $request->setUri("https://example.com{$path}");

        return $request;
    }
}
