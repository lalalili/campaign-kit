# Changelog

All notable changes to `lalalili/campaign-kit` are documented in this file.

## [0.1.7] - 2026-06-22

### Changed

- Reduced `AbstractCampaignRepository` to a minimal abstraction (`baseQuery()` + `buildRenderData()` abstract, plus `resolveType()` / `slugColumn()` helpers) so it cleanly serves divergent host rendering flows. Supersedes the richer `0.1.5`/`0.1.6` base API.

### Compatibility

- Default container bindings unchanged (`NullCampaignRepository` remains the `singletonIf` default); upgrade is additive.

## [0.1.6] - 2026-06-22

### Changed

- Made `AbstractCampaignRepository` generic (`@template TModel of Model`) and `baseQuery()` abstract for precise host typing without `assert()`.

## [0.1.5] - 2026-06-22

### Added

- Reusable `AbstractCampaignRepository` and opt-in `ConfigCampaignRepository` so host repositories can drop find/guard/view-resolution boilerplate.
- `campaign-kit.models` config block (`campaign_model`, `slug_column`).
- `illuminate/database` dependency.

## [0.1.4] - 2026-06-21

### Fixed

- Refined layout preview return-type array-shape annotation.

## [0.1.3] - 2026-05-05

### Added

- Laravel 13 support.

## [0.1.2] - 2026-03-02

### Changed

- Removed Composer fixed `version` field (versioning is tag-driven).

## [0.1.1] - 2026-03-01

### Fixed

- Support a configurable default item image for layout previews.

## [0.1.0] - 2026-03-01

### Added

- Initial release: configurable campaign pages, GA4 tracking, layout preview generation, and five host-implementable contracts (`CampaignRepositoryContract`, `CampaignLayoutResolverContract`, `CampaignPriceResolverContract`, `CampaignImageResolverContract`, `CampaignCtaAdapterContract`) with safe `Null*` / `Config*` defaults.
