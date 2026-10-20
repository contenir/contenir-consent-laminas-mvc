<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc;

/**
 * Registers the consent helpers and the route for the banner's files with a
 * Laminas MVC application.
 *
 * @api
 */
final class Module
{
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return (new ConfigProvider())();
    }
}
