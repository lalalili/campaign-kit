<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Lalalili\CampaignKit\Support\ConfigCampaignLayoutResolver;

it('resolves mobile variant from mobile user agent', function (): void {
    $resolver = new ConfigCampaignLayoutResolver();
    $request = Request::create('/campaign/1', 'GET', server: [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
    ]);

    expect($resolver->resolveVariant($request))->toBe('mobile');
});

it('resolves desktop variant from non-mobile user agent', function (): void {
    $resolver = new ConfigCampaignLayoutResolver();
    $request = Request::create('/campaign/1', 'GET', server: [
        'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64)',
    ]);

    expect($resolver->resolveVariant($request))->toBe('desktop');
});

it('resolves configured view name and returns null for unsupported inputs', function (): void {
    config()->set('campaign-kit.preview.types', [
        1 => [
            'views' => [
                'desktop' => 'campaign-kit::campaigns.previews.type1',
                'mobile'  => 'campaign-kit::campaigns.previews.type1_mobile',
            ],
        ],
    ]);

    $resolver = new ConfigCampaignLayoutResolver();

    expect($resolver->resolveViewName(1, 'desktop'))->toBe('campaign-kit::campaigns.previews.type1');
    expect($resolver->resolveViewName(1, 'mobile'))->toBe('campaign-kit::campaigns.previews.type1_mobile');
    expect($resolver->resolveViewName(1, 'tablet'))->toBeNull();
    expect($resolver->resolveViewName(null, 'desktop'))->toBeNull();
});
