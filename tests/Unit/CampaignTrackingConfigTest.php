<?php

declare(strict_types=1);

use Lalalili\CampaignKit\Support\CampaignTrackingConfig;

it('normalizes supported tracking drivers and falls back to data_layer', function (): void {
    $config = new CampaignTrackingConfig();

    expect($config->resolveDriver('data_layer'))->toBe('data_layer');
    expect($config->resolveDriver('gtag'))->toBe('gtag');
    expect($config->resolveDriver('null'))->toBe('null');
    expect($config->resolveDriver('unknown'))->toBe('data_layer');
});

it('builds frontend tracking config with defaults and overrides', function (): void {
    config()->set('campaign-kit.tracking', [
        'enabled'             => false,
        'driver'              => 'gtag',
        'data_layer_name'     => 'myLayer',
        'gtag_measurement_id' => 'G-TEST123',
        'event_map'           => [
            'view_promotion' => 'promo_view',
        ],
    ]);

    $config = new CampaignTrackingConfig();
    $frontend = $config->toFrontendConfig();

    expect($frontend['enabled'])->toBeFalse();
    expect($frontend['driver'])->toBe('gtag');
    expect($frontend['dataLayerName'])->toBe('myLayer');
    expect($frontend['measurementId'])->toBe('G-TEST123');
    expect($frontend['eventMap']['view_promotion'])->toBe('promo_view');
});
