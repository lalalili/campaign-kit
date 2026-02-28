<?php

use Illuminate\Support\Facades\Route;
use Lalalili\CampaignKit\Http\Controllers\CampaignLayoutPreviewController;
use Lalalili\CampaignKit\Http\Controllers\FrontEndCampaignController;

Route::middleware(config('campaign-kit.routes.middleware', []))->group(function (): void {
    Route::get('/campaign/layout-preview/{type}/{variant}', [CampaignLayoutPreviewController::class, 'show'])
        ->name('campaign.layout-preview');

    Route::get('/campaign/s/{slug}', [FrontEndCampaignController::class, 'showBySlug'])
        ->name('campaign.show.slug');

    Route::get('/campaign/{campaignId}', [FrontEndCampaignController::class, 'show'])
        ->name('campaign.show.id');
});
