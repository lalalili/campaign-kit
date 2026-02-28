<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit;

use Illuminate\Support\ServiceProvider;
use Lalalili\CampaignKit\Commands\GenerateLayoutPreviewsCommand;
use Lalalili\CampaignKit\Commands\InstallCampaignKitCommand;
use Lalalili\CampaignKit\Commands\ScaffoldCampaignLayoutCommand;
use Lalalili\CampaignKit\Contracts\CampaignCtaAdapterContract;
use Lalalili\CampaignKit\Contracts\CampaignImageResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignPriceResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignRepositoryContract;
use Lalalili\CampaignKit\Support\ConfigCampaignLayoutResolver;
use Lalalili\CampaignKit\Support\NullCampaignCtaAdapter;
use Lalalili\CampaignKit\Support\NullCampaignImageResolver;
use Lalalili\CampaignKit\Support\NullCampaignPriceResolver;
use Lalalili\CampaignKit\Support\NullCampaignRepository;

final class CampaignKitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/campaign-kit.php', 'campaign-kit');

        $this->app->singleton(CampaignRepositoryContract::class, NullCampaignRepository::class);
        $this->app->singleton(CampaignLayoutResolverContract::class, ConfigCampaignLayoutResolver::class);
        $this->app->singleton(CampaignPriceResolverContract::class, NullCampaignPriceResolver::class);
        $this->app->singleton(CampaignImageResolverContract::class, NullCampaignImageResolver::class);
        $this->app->singleton(CampaignCtaAdapterContract::class, NullCampaignCtaAdapter::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'campaign-kit');

        if ((bool) config('campaign-kit.routes.enabled', true)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/campaign-kit.php');
        }

        if ($this->app->runningInConsole()) {
            $this->registerPublishes();
            $this->commands([
                GenerateLayoutPreviewsCommand::class,
                InstallCampaignKitCommand::class,
                ScaffoldCampaignLayoutCommand::class,
            ]);
        }
    }

    private function registerPublishes(): void
    {
        $this->publishes([
            __DIR__ . '/../config/campaign-kit.php' => config_path('campaign-kit.php'),
        ], 'campaign-kit-config');

        $this->publishes([
            __DIR__ . '/../resources/views/campaigns' => resource_path('views/campaigns'),
        ], 'campaign-kit-views');

        $this->publishes([
            __DIR__ . '/../resources/assets/js/campaign-kit.js'             => resource_path('js/campaign-kit.js'),
            __DIR__ . '/../resources/assets/css/campaigns/type1.css'        => public_path('css/campaigns/type1.css'),
            __DIR__ . '/../resources/assets/css/campaigns/type1_mobile.css' => public_path('css/campaigns/type1_mobile.css'),
        ], 'campaign-kit-assets');

        $this->publishes([
            __DIR__ . '/../bin/capture-campaign-layout-preview.mjs' => base_path('bin/capture-campaign-layout-preview.mjs'),
        ], 'campaign-kit-bin');

        $this->publishes([
            __DIR__ . '/../stubs' => base_path('stubs/campaign-kit'),
        ], 'campaign-kit-stubs');
    }
}
