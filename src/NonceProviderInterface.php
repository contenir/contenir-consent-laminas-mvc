<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc;

/**
 * The CSP nonce for the page being rendered. Register one in the service
 * manager under this interface's name, and the consent helpers write it on
 * every script without being told it. One class can implement this and
 * contenir/tracking-laminas-mvc's interface of the same name.
 *
 * @api
 */
interface NonceProviderInterface
{
    /**
     * The nonce, or null when the page has none.
     */
    public function nonce(): ?string;
}
