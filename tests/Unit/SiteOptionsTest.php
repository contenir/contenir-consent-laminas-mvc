<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\Unit;

use Contenir\Consent\Laminas\Mvc\SiteOptions;
use Contenir\Consent\Texts;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class SiteOptionsTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function emptyConfigs(): array
    {
        return [
            'no config'                   => [null],
            'nothing in it'               => [[]],
            'parts that are not arrays'   => [['settings' => 'x', 'contenir_consent' => 'y']],
            'options that are not arrays' => [
                ['contenir_consent' => ['services' => 'x', 'texts' => 'y', 'extensions_config' => 3]],
            ],
        ];
    }

    #[Test]
    public function readsTheExtensionsConfigFromConfigAutoloadByDefault(): void
    {
        static::assertSame('config/autoload', (new SiteOptions())->extensionsConfig);
    }

    #[Test]
    public function readsTheSettingsServicesTextsAndWhereTheExtensionsConfigIs(): void
    {
        $options = SiteOptions::fromConfig([
            'settings'         => ['extensions' => ['consent' => ['revision' => 2]]],
            'contenir_consent' => [
                'extensions_config' => '/site/config/autoload',
                'services'          => ['youtube' => ['name' => 'YouTube']],
                'texts'             => ['acceptAll' => 'Yes please', 'unknown' => 'x', 'rejectAll' => 3],
            ],
        ]);

        static::assertEquals(
            new SiteOptions(
                ['extensions' => ['consent' => ['revision' => 2]]],
                ['youtube' => ['name' => 'YouTube']],
                new Texts(acceptAll: 'Yes please'),
                '/site/config/autoload',
            ),
            $options,
        );
    }

    #[Test]
    #[DataProvider('emptyConfigs')]
    public function startsFromNothingWhenTheConfigSaysNothing(mixed $config): void
    {
        static::assertEquals(new SiteOptions(), SiteOptions::fromConfig($config));
    }
}
