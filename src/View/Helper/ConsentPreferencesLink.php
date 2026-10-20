<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\View\Helper;

use Contenir\Consent\Laminas\Mvc\ConsentSite;

/**
 * `$this->consentPreferencesLink()`: a link, for the footer or the privacy
 * page, that opens the cookie preferences so a visitor can change their
 * choice. While the Consent add-on is disabled it writes nothing.
 *
 *     <?= $this->consentPreferencesLink(['class' => 'footer-link']) ?>
 *
 * @api
 */
final readonly class ConsentPreferencesLink
{
    public function __construct(
        private ConsentSite $site,
    ) {}

    /**
     * @param array<string, string> $attributes Extra attributes such as class
     * @param ?string               $label      The link's words, instead of "Cookie preferences"
     */
    public function __invoke(array $attributes = [], ?string $label = null): string
    {
        return $this->site->preferencesLink($attributes, $label);
    }
}
