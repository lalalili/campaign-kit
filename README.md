# lalalili/campaign-kit

Reusable campaign page toolkit for Laravel projects.

This package provides:

- Stable campaign frontend routes (preview / slug / id)
- Config-driven campaign layout preview factory
- GA4 tracking driver layer (`data_layer`, `gtag`, `null`)
- Preview image generation command (Playwright PNG + Imagick WebP)
- Contracts for host-project adapters (repository, pricing, images, CTA)

## Scope

In package scope:

- Campaign route/controller orchestration
- Preview skeleton rendering and preview asset generation flow
- Tracking config normalization and JS runtime API contract
- Extension contracts for host adapters

Out of package scope (host app adapter layer):

- Eloquent query logic and domain authorization
- Product/event pricing/image lookup details
- Cart/CTA business flow implementation
- Admin UI implementation

## Requirements

- PHP `^8.4`
- Laravel `^12.0`
- `ext-imagick` (for WebP conversion)
- Node.js runtime (for preview capture script)

## Install

### Option A: Local path repository

In app `composer.json`:

```json
{
  "repositories": [
    {
      "type": "path",
      "url": "packages/campaign-kit",
      "options": {
        "symlink": true
      }
    }
  ],
  "require": {
    "lalalili/campaign-kit": "^0.1"
  }
}
```

Then run:

```bash
composer update lalalili/campaign-kit
```

### Option B: Private VCS repository

In app `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "git@github.com:lalalili/campaign-kit.git"
    }
  ],
  "require": {
    "lalalili/campaign-kit": "^0.1"
  }
}
```

Then run:

```bash
composer update lalalili/campaign-kit
```

## Laravel Setup

Auto-discovery loads `CampaignKitServiceProvider`.

Install publishable resources:

```bash
php artisan campaign-kit:install
```

Publish tags manually if needed:

- `campaign-kit-config`
- `campaign-kit-views`
- `campaign-kit-assets`
- `campaign-kit-bin`
- `campaign-kit-stubs`

## Default Routes

When `config('campaign-kit.routes.enabled') === true`, these routes are registered:

- `GET /campaign/layout-preview/{type}/{variant}` → `campaign.layout-preview`
- `GET /campaign/s/{slug}` → `campaign.show.slug`
- `GET /campaign/{campaignId}` → `campaign.show.id`

## Host Adapter Contracts

Bind these contracts in host app:

- `CampaignRepositoryContract`
- `CampaignLayoutResolverContract`
- `CampaignPriceResolverContract`
- `CampaignImageResolverContract`
- `CampaignCtaAdapterContract`

If host does not bind them, package falls back to null/default adapters.

## JavaScript API

Global API contract:

- `window.CampaignKit.initType1(config)`
- `window.CampaignKit.setTracker(driver, options)`

Supported tracking drivers:

- `data_layer` (default)
- `gtag`
- `null`

Driver and event mapping come from `config('campaign-kit.tracking')`.

## Commands

### Install package resources

```bash
php artisan campaign-kit:install
```

### Scaffold a new layout type

```bash
php artisan campaign-kit:scaffold-layout 2 --slug=summer-sale
```

Generates:

- `resources/views/campaigns/type{type}.blade.php`
- `resources/views/campaigns/type{type}_mobile.blade.php`
- `resources/views/campaigns/previews/type{type}.blade.php`
- `resources/views/campaigns/previews/type{type}_mobile.blade.php`
- `public/css/campaigns/type{type}.css`
- `public/css/campaigns/type{type}_mobile.css`
- `resources/js/campaign-type{type}.js`
- updates `campaign-kit-layouts.php`

### Generate preview WebP images

```bash
php artisan campaign-kit:generate-layout-previews --base-url=https://example.test
```

Useful options:

- `--type=1 --type=2` or comma-separated
- `--variant=desktop,mobile`
- `--keep-on-fail` (do not overwrite valid existing WebP on capture failure)

Output filename convention:

- `{slug}.webp`
- `{slug}-mobile.webp`

## Preview Pipeline

1. Open preview route URL (`/campaign/layout-preview/{type}/{variant}`)
2. Capture PNG via Playwright script (`bin/capture-campaign-layout-preview.mjs`)
3. Convert PNG to WebP via Imagick

## Config

Main config file: `config/campaign-kit.php`

Key sections:

- `redirect`
- `routes`
- `tracking`
- `preview`

You can also externalize preview type mapping via:

- `preview.types_file` (default `base_path('campaign-kit-layouts.php')`)

## Testing Scope

Package tests cover package-level behavior:

- Preview routes and placeholder behavior
- Frontend controller context/redirect behavior
- Preview factory and resolver behavior
- Tracking config normalization
- Generate preview command and `--keep-on-fail`

Host app tests should cover integration behavior:

- Contract bindings to app adapters
- Route compatibility and controller mapping
- View/DOM/JS compatibility with project-specific templates
- End-to-end domain flows

## Local Quality Checks

Inside package directory:

```bash
composer install
composer test
composer analyse
```

