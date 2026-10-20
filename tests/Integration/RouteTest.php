<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Integration;

use Contenir\Consent\Assets;
use Contenir\Consent\Laminas\Mvc\Asset\AssetController;
use Contenir\Consent\Laminas\Mvc\ConfigProvider;
use Laminas\Http\Request;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\Router\Http\TreeRouteStack;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The module's route among a site's own, with a catch-all page route.
 */
#[Group('integration')]
final class RouteTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string}>
     */
    public static function paths(): array
    {
        return [
            'the script'     => ['/_consent/' . Assets::scriptName(), ConfigProvider::ROUTE],
            'the stylesheet' => ['/_consent/' . Assets::stylesheetName(), ConfigProvider::ROUTE],
            'another file'   => ['/_consent/other.js', 'page'],
            'a short hash'   => ['/_consent/consent-abc.js', 'page'],
            'a page'         => ['/about', 'page'],
        ];
    }

    #[Test]
    #[DataProvider('paths')]
    public function routesTheBannersFilesAheadOfTheSitesPages(string $path, string $route): void
    {
        $request = new Request();
        $request->setUri("https://example.com{$path}");

        static::assertSame($route, $this->router()->match($request)?->getMatchedRouteName());
    }

    #[Test]
    public function sendsTheBannersFilesToTheAssetController(): void
    {
        $request = new Request();
        $request->setUri('https://example.com/_consent/' . Assets::scriptName());

        static::assertSame(AssetController::class, $this->router()->match($request)?->getParam('controller'));
    }

    private function router(): TreeRouteStack
    {
        /** @var array<string, array<string, mixed>> $routes */
        $routes = (new ConfigProvider())()['router']['routes'];

        return TreeRouteStack::factory([
            'routes' => [
                'home' => ['type' => Literal::class, 'options' => ['route' => '/']],
                'page' => [
                    'type'    => Segment::class,
                    'options' => ['route' => '/:slug', 'constraints' => ['slug' => '.+']],
                ],
                ...$routes,
            ],
        ]);
    }
}
