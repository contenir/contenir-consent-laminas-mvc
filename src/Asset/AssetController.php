<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Asset;

use Contenir\Consent\Assets;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Stdlib\DispatchableInterface;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;
use Override;

use function basename;

/**
 * Serves the banner's script and stylesheet at /_consent/consent-<hash>.js
 * and .css, cacheable for a year since a change to either changes its
 * name. Any other name is not found.
 *
 * @api
 */
final class AssetController implements DispatchableInterface
{
    /**
     * A year, in seconds.
     */
    public const int MAX_AGE = 31_536_000;

    #[Override]
    public function dispatch(RequestInterface $request, ?ResponseInterface $response = null): Response
    {
        $response = $response instanceof Response ? $response : new Response();
        $file     = $request instanceof Request ? basename($request->getUri()->getPath() ?? '') : '';

        [$type, $body] = match ($file) {
            Assets::scriptName()     => ['text/javascript; charset=utf-8', Assets::script()],
            Assets::stylesheetName() => ['text/css; charset=utf-8', Assets::stylesheet()],
            default                  => [null, ''],
        };

        if (null === $type) {
            $response->setStatusCode(Response::STATUS_CODE_404);
            $response->setContent('Not found');

            return $response;
        }

        $response->getHeaders()
            ->addHeaders([
                'Content-Type'           => $type,
                'Cache-Control'          => 'public, max-age=' . self::MAX_AGE . ', immutable',
                'X-Content-Type-Options' => 'nosniff',
            ]);

        $response->setStatusCode(Response::STATUS_CODE_200);
        $response->setContent($body);

        return $response;
    }
}
