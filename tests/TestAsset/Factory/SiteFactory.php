<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Tests\TestAsset\Factory;

use Contenir\Consent\ConsentConfig;
use Contenir\Consent\Country;
use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Laminas\Mvc\NonceProviderInterface;

/**
 * Builds a site's consent for tests: enabled, without the Tracking add-on,
 * for a visitor from nowhere in particular, unless told otherwise.
 */
final class SiteFactory
{
    public const string SCRIPT = '/_consent/consent-abc.js';

    public const string STYLESHEET = '/_consent/consent-abc.css';

    public static function make(
        bool $enabled = true,
        bool $tracking = false,
        ConsentConfig $config = new ConsentConfig(),
        ?string $country = null,
        ?NonceProviderInterface $nonces = null,
    ): ConsentSite {
        return new ConsentSite(
            $enabled,
            $config,
            $tracking,
            Country::fromHeader($country),
            self::SCRIPT,
            self::STYLESHEET,
            $nonces,
        );
    }
}
