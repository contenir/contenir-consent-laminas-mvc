<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\View\Helper;

use Contenir\Consent\Laminas\Mvc\ConsentSite;

/**
 * `$this->consent()` in a layout: the cookie banner. Put head() in <head>,
 * before any tag, and bodyEnd() straight before </body>:
 *
 *     <?= $this->consent()->head() ?>
 *     …
 *     <?= $this->consent()->bodyEnd() ?>
 *     </body>
 *
 * Both take the page's CSP nonce, or use the NonceProviderInterface the
 * application registered. Both write nothing until the Consent add-on is
 * enabled on the site.
 *
 * @api
 */
final readonly class Consent
{
    public function __construct(
        private ConsentSite $site,
    ) {}

    /**
     * The banner's script and the call that starts it for this visitor.
     */
    public function bodyEnd(?string $nonce = null): string
    {
        return $this->site->bodyEnd($nonce);
    }

    /**
     * The banner's stylesheet and, on a site without the Tracking add-on,
     * Google Consent Mode's denied defaults.
     */
    public function head(?string $nonce = null): string
    {
        return $this->site->head($nonce);
    }

    public function __invoke(): self
    {
        return $this;
    }
}
