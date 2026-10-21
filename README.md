# contenir/contenir-consent-laminas-mvc

[![Continuous Integration](https://github.com/contenir/contenir-consent-laminas-mvc/actions/workflows/continuous-integration.yml/badge.svg)](https://github.com/contenir/contenir-consent-laminas-mvc/actions/workflows/continuous-integration.yml)
[![codecov](https://codecov.io/gh/contenir/contenir-consent-laminas-mvc/graph/badge.svg)](https://codecov.io/gh/contenir/contenir-consent-laminas-mvc)

The Laminas MVC adapter for [contenir/contenir-consent](https://github.com/contenir/contenir-consent). It puts the cookie
banner on a site's pages, gates the site's own embeds until visitors allow them, and serves the banner's files.

Consent is set in the CMS, by the [Consent add-on](https://github.com/contenir-extension/consent). Settings are read
from the site's `config['settings']`, as the CMS writes them, so there is nothing to configure on the site.

## Requirements

- PHP 8.3, 8.4 or 8.5
- laminas/laminas-view 2.30 or later, and laminas/laminas-router 3.13 or later

## Install

```bash
composer require contenir/contenir-consent-laminas-mvc
```

Add `Contenir\Consent\Laminas\Mvc` to the application's modules (laminas-component-installer offers to).

The package is named after its repository, as every Contenir package is. Before 0.1.0 it was
`contenir/consent-laminas-mvc`.

## Usage

In the layout, put `consent()->head()` in `<head>`, before any tag, and `consent()->bodyEnd()` straight before
`</body>`. On a site with the Tracking add-on, `tracking()->head()` can come before or after `consent()->head()`:

```php
<head>
    <meta charset="utf-8">
    <?= $this->consent()->head($nonce) ?>
    <?= $this->tracking()->head($nonce) ?>
    …
</head>
<body>
    …
    <footer>
        <?= $this->consentPreferencesLink(['class' => 'footer-link']) ?>
    </footer>
    <?= $this->consent()->bodyEnd($nonce) ?>
</body>
```

| Helper | Writes |
|---|---|
| `consent()->head()` | the banner's stylesheet, and on a site without the Tracking add-on, Google Consent Mode's denied defaults |
| `consent()->bodyEnd()` | the banner's script and the call that starts it for this visitor |
| `consentScript($category, src: …)` or `consentScript($category, code: …)` | one of the site's own scripts, inert until the visitor allows `$category` |
| `consentPreferencesLink($attributes, $label)` | a link that opens the cookie preferences |

Gate a site's own embeds, such as videos, maps and chat widgets, with `consentScript()`:

```php
<?= $this->consentScript('preferences', src: 'https://www.youtube.com/iframe_api') ?>
<?= $this->consentScript('marketing', code: 'initChat();', attributes: ['id' => 'chat']) ?>
```

The category is `necessary`, `analytics`, `marketing` or `preferences`. List the embed's service in the config (below)
so the preferences say what it is.

Every script carries the page's nonce: the one passed to the helper, or else the one from a
`Contenir\Consent\Laminas\Mvc\NonceProviderInterface` service the application registers. One class can implement this
and contenir/tracking-laminas-mvc's interface of the same name.

## What it writes, and when

- **Nothing until the Consent add-on is enabled** on the site. Until then `consentScript()` writes each script to run
  at once, and `consentPreferencesLink()` writes nothing, since there is no banner to open.
- **The enabled add-ons** are read from `config/autoload/extensions.global.php` and `extensions.local.php`, as the
  CMS reads them: a list in the local file replaces the global one entirely.
- **The visitor's country** comes from the request header the settings name, Cloudflare's `CF-IPCountry` by default,
  for the "ask only the EEA, UK and Switzerland" mode. Pages cached by a proxy must vary on that header in that mode.
- **The banner's files** are served by the module at `/_consent/consent-<hash>.js` and `.css`, with a route ahead of
  the site's own (priority 1000), cacheable for a year. A change to either file changes its name.

The banner, its categories, the `contenir:consent` browser event and Google Consent Mode are described in
[contenir/contenir-consent](https://github.com/contenir/contenir-consent).

## Configuration

Nothing is needed. To add to the defaults:

```php
return [
    'contenir_consent' => [
        'extensions_config' => 'config/autoload', // where extensions.global.php and .local.php are
        'services'          => [                  // the site's own services, listed in the preferences
            'youtube' => [
                'name'        => 'YouTube',
                'provider'    => 'Google',
                'category'    => 'preferences',
                'cookies'     => ['YSC', 'VISITOR_INFO1_LIVE'],
                'privacy_url' => 'https://policies.google.com/privacy',
            ],
        ],
        'texts'             => [                  // replaces contenir/contenir-consent's Texts defaults, by name
            'acceptAll' => 'Accept all cookies',
        ],
    ],
];
```

To restyle the banner, set cookieconsent's `--cc-*` CSS variables on `#cc-main` in the site's stylesheet.

## Licence

BSD-3-Clause. See [LICENSE.md](LICENSE.md).
