<?php

declare(strict_types=1);

namespace Contenir\Consent\Laminas\Mvc\View\Helper;

use Contenir\Consent\Category;
use Contenir\Consent\Exception\InvalidScriptException;
use Contenir\Consent\Laminas\Mvc\ConsentSite;
use Contenir\Consent\Script\Script;
use InvalidArgumentException;

use function sprintf;

/**
 * `$this->consentScript()`: one of the site's own scripts, such as a video
 * or map embed, written inert until the visitor allows its category. While
 * the Consent add-on is disabled it runs at once.
 *
 *     <?= $this->consentScript('preferences', src: 'https://www.youtube.com/iframe_api') ?>
 *     <?= $this->consentScript('marketing', code: 'initChat();') ?>
 *
 * @api
 */
final readonly class ConsentScript
{
    public function __construct(
        private ConsentSite $site,
    ) {}

    /**
     * @param string                     $category   necessary, analytics, marketing or preferences
     * @param ?string                    $src        Where to load the script from
     * @param ?string                    $code       The script's code, when it isn't loaded from $src
     * @param array<string, string|bool> $attributes Extra attributes such as async; true writes the bare name
     * @param ?string                    $nonce      The page's CSP nonce, when no NonceProviderInterface gives it
     *
     * @throws InvalidArgumentException When the category isn't one of the four, or neither or both of $src and $code are given.
     * @throws InvalidScriptException   When the script can't be written safely.
     */
    public function __invoke(
        string $category,
        ?string $src = null,
        ?string $code = null,
        array $attributes = [],
        ?string $nonce = null,
    ): string {
        $consent = Category::tryFrom($category);
        if (null === $consent) {
            throw new InvalidArgumentException(sprintf(
                '"%s" is not a consent category: use necessary, analytics, marketing or preferences',
                $category,
            ));
        }

        if ((null === $src) === (null === $code)) {
            throw new InvalidArgumentException('Give consentScript() either a src or code, not both');
        }

        return $this->site->script(
            null === $src
                ? Script::inline((string) $code, $consent, $attributes)
                : Script::src($src, $consent, $attributes),
            $nonce,
        );
    }
}
