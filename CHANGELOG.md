# Changelog

All notable changes to this project are documented here. The format follows
[Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and the project
adheres to [Semantic Versioning](https://semver.org/).

## [0.1.0] - 2026-10-21

### Changed

- The package is renamed `contenir/contenir-consent-laminas-mvc`, after its repository, and requires the library by
  its new name, `contenir/contenir-consent`. They were `contenir/consent-laminas-mvc` and `contenir/consent`, neither
  ever tagged. The namespace (`Contenir\Consent\Laminas\Mvc\`) is unchanged.
- Requires contenir/contenir-consent `^0.1` from Packagist, where it required `0.1.x-dev` through a `vcs` repository
  entry. A project at stable stability can install the adapter.

### Added

- `consent()->head()` and `consent()->bodyEnd()`: the banner's stylesheet, Google Consent Mode's denied defaults on a
  site without the Tracking add-on, and the banner started for the visitor's country.
- `consentScript()`, which gates a site's own embeds until visitors allow their category, and
  `consentPreferencesLink()`, which reopens the preferences.
- A route ahead of the site's own that serves the banner's script and stylesheet under hashed names, cacheable for a
  year.
- The site's consent (`ConsentSite`) from its settings, the enabled add-ons (local over global), the country header,
  the router and an optional `NonceProviderInterface`; the site's own services and texts from `contenir_consent`.
