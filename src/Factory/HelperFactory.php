<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\Factory;

use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Laminas\Mvc\View\Helper\Consent;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentPreferencesLink;
use Contenir\Consent\Laminas\Mvc\View\Helper\ConsentScript;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

/**
 * Builds each consent view helper around the site's ConsentSite.
 *
 * @api
 */
final class HelperFactory
{
    /**
     * @throws ContainerExceptionInterface
     * @throws LogicException When asked for a helper this factory doesn't build.
     */
    public function __invoke(
        ContainerInterface $container,
        string $requestedName,
    ): Consent|ConsentScript|ConsentPreferencesLink {
        $site = $container->get(ConsentSite::class);

        return match ($requestedName) {
            Consent::class                => new Consent($site),
            ConsentScript::class          => new ConsentScript($site),
            ConsentPreferencesLink::class => new ConsentPreferencesLink($site),
            default                       => throw new LogicException("HelperFactory doesn't build {$requestedName}"),
        };
    }
}
