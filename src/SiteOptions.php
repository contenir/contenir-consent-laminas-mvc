<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc;

use Contenir\Consent\Texts;

use function array_filter;
use function array_intersect_key;
use function get_object_vars;
use function is_array;
use function is_string;

/**
 * What the application's config says about consent: the site's settings
 * (config['settings'], as the CMS writes them) and contenir_consent:
 *
 *     'contenir_consent' => [
 *         'extensions_config' => 'config/autoload', // where extensions.global.php and .local.php are
 *         'services'          => [                  // the site's own services, listed in the preferences
 *             'youtube' => ['name' => 'YouTube', 'provider' => 'Google', 'category' => 'preferences'],
 *         ],
 *         'texts'             => ['acceptAll' => 'Accept all cookies'], // replaces Texts' defaults by name
 *     ],
 *
 * @api
 */
final readonly class SiteOptions
{
    public const string EXTENSIONS_CONFIG = 'config/autoload';

    /**
     * @param array<array-key, mixed> $settings
     * @param array<array-key, mixed> $services
     */
    public function __construct(
        public array $settings = [],
        public array $services = [],
        public Texts $texts = new Texts(),
        public string $extensionsConfig = self::EXTENSIONS_CONFIG,
    ) {}

    /**
     * @mago-expect analysis:mixed-assignment The application's config is untyped; each part is checked here.
     */
    public static function fromConfig(mixed $config): self
    {
        $config    = is_array($config) ? $config : [];
        $options   = is_array($config['contenir_consent'] ?? null) ? $config['contenir_consent'] : [];
        $directory = $options['extensions_config'] ?? null;

        return new self(
            is_array($config['settings'] ?? null) ? $config['settings'] : [],
            is_array($options['services'] ?? null) ? $options['services'] : [],
            self::texts($options['texts'] ?? null),
            is_string($directory) ? $directory : self::EXTENSIONS_CONFIG,
        );
    }

    /**
     * Texts' defaults, with those $texts names replaced; anything else in
     * it is ignored.
     */
    private static function texts(mixed $texts): Texts
    {
        if (! is_array($texts)) {
            return new Texts();
        }

        /** @var array<string, string> $known */
        $known = array_filter(array_intersect_key($texts, get_object_vars(new Texts())), is_string(...));

        return new Texts(...$known);
    }
}
