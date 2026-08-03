# Changelog

All notable changes to `lalalili/campaign-kit` are documented in this file.

## [1.0.1] - 2026-08-04

### Fixed

- Extend the `symfony/process` constraint to include Symfony 8.1 so Composer can resolve a non-advisory-blocked version.

## [1.0.0] - 2026-07-27

### Changed

- 首個穩定版。此後遵循
  [SEMVER.md](https://github.com/lalalili/.github/blob/main/SEMVER.md)
  定義的 public API 契約,宿主可安全使用 `^1.0` 約束。
- 對其他 lalalili 套件的約束一律收斂為 `^1.0`,取代先前 `^0.x`
  與多段 OR 的寫法。
- `repositories` 改用 GitHub VCS,不再依賴宿主 `packages/` 底下的
  兄弟目錄;測試資源改從 `vendor/lalalili/*` 讀取。
- 移除 `minimum-stability` / `prefer-stable` 宣告,授權統一為 MIT。

### 為什麼是 1.0.0

Composer 對 `^0.1.1` 的解讀是 `>=0.1.1 <0.2.0`,0.x 期間每發一個 minor
都需要所有宿主手動改 `composer.json`,否則 `composer update` 永遠拿不到
新版。本套件生態曾因此讓宿主停在數十個 commit 之前而無人察覺。

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
