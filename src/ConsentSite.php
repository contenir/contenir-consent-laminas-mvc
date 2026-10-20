<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc;

use Contenir\Consent\Banner;
use Contenir\Consent\ConsentConfig;
use Contenir\Consent\ConsentMode;
use Contenir\Consent\Country;
use Contenir\Consent\PreferencesLink;
use Contenir\Consent\Script\Script;
use Contenir\Consent\Script\ScriptRenderer;

use function htmlspecialchars;
use function sprintf;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Consent on this site, for this request: whether the Consent add-on is
 * enabled, its settings, whether the Tracking add-on writes the Consent
 * Mode defaults, the visitor's country, where the banner's files are and
 * the page's nonce. The three view helpers share it.
 *
 * @api
 */
final readonly class ConsentSite
{
    /**
     * @param bool     $enabled       Whether the Consent add-on is enabled on the site
     * @param bool     $tracking      Whether the Tracking add-on is, so it writes the Consent Mode defaults
     * @param ?Country $country       The visitor's country, from the country header
     * @param string   $scriptUrl     Where the banner's script is served
     * @param string   $stylesheetUrl Where its stylesheet is served
     *
     * @mago-expect lint:excessive-parameter-list One parameter per thing the helpers need to know.
     */
    public function __construct(
        public bool $enabled,
        public ConsentConfig $config,
        public bool $tracking,
        public ?Country $country,
        public string $scriptUrl,
        public string $stylesheetUrl,
        private ?NonceProviderInterface $nonces = null,
    ) {}

    /**
     * The scripts for the end of the body: the banner, started for this
     * visitor. Nothing while the add-on is disabled.
     */
    public function bodyEnd(?string $nonce = null): string
    {
        if (! $this->enabled) {
            return '';
        }

        return $this->renderer()->render(
            (new Banner($this->config))->scripts($this->scriptUrl, $this->country),
            $this->nonce($nonce),
        );
    }

    /**
     * The banner's stylesheet and, on a site without the Tracking add-on,
     * Google Consent Mode's defaults. Nothing while the add-on is disabled.
     */
    public function head(?string $nonce = null): string
    {
        if (! $this->enabled) {
            return '';
        }

        $link = sprintf('<link rel="stylesheet" href="%s">', $this->escape($this->stylesheetUrl));
        if ($this->tracking) {
            return $link;
        }

        return "{$link}\n{$this->renderer()->render([ConsentMode::script()], $this->nonce($nonce))}";
    }

    /**
     * The link that opens the preferences. Nothing while the add-on is
     * disabled, since there would be no banner to open.
     *
     * @param array<string, string> $attributes
     */
    public function preferencesLink(array $attributes = [], ?string $label = null): string
    {
        if (! $this->enabled) {
            return '';
        }

        return (new PreferencesLink($label ?? $this->config->texts->preferencesLink))->html($attributes);
    }

    /**
     * One of the site's own scripts: inert until the visitor allows its
     * category while the add-on is enabled, and running at once otherwise.
     */
    public function script(Script $script, ?string $nonce = null): string
    {
        return $this->renderer()->script($script, $this->nonce($nonce));
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8');
    }

    private function nonce(?string $nonce): ?string
    {
        return $nonce ?? $this->nonces?->nonce();
    }

    private function renderer(): ScriptRenderer
    {
        return new ScriptRenderer(gate: $this->enabled);
    }
}
