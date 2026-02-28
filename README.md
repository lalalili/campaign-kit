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

`campaign-kit-assets` also publishes the default preview book thumbnail to:

- `public/vendor/campaign-kit/images/default-book-thumbnail.svg`

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

## GA4 Configuration & Usage

### 1) Configure tracking driver and event mapping

`config/campaign-kit.php`:

```php
'tracking' => [
    'enabled'             => true,
    'driver'              => 'data_layer', // data_layer | gtag | null
    'data_layer_name'     => 'dataLayer',
    'gtag_measurement_id' => env('GA4_MEASUREMENT_ID'),
    'currency'            => 'TWD',
    'affiliation'         => 'My Store',
    'event_map'           => [
        'view_promotion'   => 'view_promotion',
        'select_promotion' => 'select_promotion',
        'select_item'      => 'select_item',
        'add_to_cart'      => 'add_to_cart',
    ],
],
```

### 2) Pass tracking config to frontend runtime

In host campaign view script:

```html
<script>
    window.__CAMPAIGN_KIT__ = {
        ...(window.__CAMPAIGN_KIT__ || {}),
        tracking: @json($campaignTrackingConfig ?? []),
    };

    window.CampaignKit?.setTracker?.(
        window.__CAMPAIGN_KIT__.tracking?.driver || 'data_layer',
        window.__CAMPAIGN_KIT__.tracking || {}
    );

    window.CampaignKit?.initType1?.({
        campaignId: @js((string) $campaign->id),
        campaignName: @js((string) $campaign->campaign_title),
        currency: window.__CAMPAIGN_KIT__.tracking?.currency || 'TWD',
        affiliation: window.__CAMPAIGN_KIT__.tracking?.affiliation || '',
    });
</script>
```

### 3) Event behavior

Type1 runtime will emit:

- `view_promotion`
- `select_promotion`
- `select_item`
- `add_to_cart`

When `tracking.enabled = false` or driver is `null`, tracking becomes no-op.

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

## Layout Thumbnail Generation

### 1) Configure preview generation

`config/campaign-kit.php`:

```php
'routes' => [
    'preview_path_template' => '/campaign/layout-preview/{type}/{variant}',
],

'preview' => [
    'wait_for_selector'   => '.campaign-preview-root',
    'ignore_https_errors' => false,
    'output_dir'          => public_path('campaign/layouts'),
    'default_item_image'  => '/vendor/campaign-kit/images/default-book-thumbnail.svg',
    'variants'            => [
        'desktop' => ['width' => 1366, 'height' => 1024],
        'mobile'  => ['width' => 430, 'height' => 932],
    ],
    'types_file'          => base_path('campaign-kit-layouts.php'),
],
```

### 2) Runtime requirements for generation

- Node.js available in shell PATH
- Playwright installed (`npm i -D playwright` + `npx playwright install chromium`)
- PHP `ext-imagick` enabled

### 3) Generate images

```bash
php artisan campaign-kit:generate-layout-previews --base-url=https://example.test
```

Common examples:

```bash
# only type 1 desktop
php artisan campaign-kit:generate-layout-previews --type=1 --variant=desktop

# multiple types
php artisan campaign-kit:generate-layout-previews --type=1 --type=2 --variant=desktop,mobile

# keep existing webp when capture fails
php artisan campaign-kit:generate-layout-previews --keep-on-fail
```

The command flow is:

1. Open preview route (`/campaign/layout-preview/{type}/{variant}`)
2. Capture PNG by Playwright (`bin/capture-campaign-layout-preview.mjs`)
3. Convert PNG to WebP by Imagick

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
