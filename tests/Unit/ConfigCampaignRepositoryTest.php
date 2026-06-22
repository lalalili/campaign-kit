<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lalalili\CampaignKit\Contracts\CampaignCtaAdapterContract;
use Lalalili\CampaignKit\Contracts\CampaignImageResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignPriceResolverContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;
use Lalalili\CampaignKit\Support\ConfigCampaignRepository;
use Lalalili\CampaignKit\Tests\Support\Fixtures\FakeCampaign;

beforeEach(function (): void {
    Schema::create('fake_campaigns', function (Blueprint $table): void {
        $table->increments('id');
        $table->string('url_slug');
        $table->string('campaign_title');
        $table->integer('type')->default(1);
    });

    config()->set('campaign-kit.models.campaign_model', FakeCampaign::class);
    config()->set('campaign-kit.preview.types', [
        1 => [
            'views' => [
                'desktop' => 'campaign-kit::campaigns.previews.type1',
                'mobile'  => 'campaign-kit::campaigns.previews.type1_mobile',
            ],
        ],
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('fake_campaigns');
});

it('produces a generic config-driven view data payload using the default resolvers', function (): void {
    FakeCampaign::create(['url_slug' => 'generic', 'campaign_title' => 'Generic', 'type' => 1]);

    $repository = new ConfigCampaignRepository(
        app(CampaignLayoutResolverContract::class),
        app(CampaignPriceResolverContract::class),
        app(CampaignImageResolverContract::class),
        app(CampaignCtaAdapterContract::class),
    );

    $data = $repository->findBySlug('generic', new CampaignRequestContext('desktop', false));

    assert($data instanceof CampaignRenderData);

    expect($data->title)->toBe('Generic')
        ->and($data->viewData)->toHaveKeys([
            'campaign', 'title', 'description', 'variant', 'isMobile', 'prices', 'images', 'ctaConfig',
        ])
        ->and($data->viewData['prices'])->toBe([])
        ->and($data->viewData['images'])->toBe([])
        ->and($data->viewData['ctaConfig'])->toBe(['enabled' => false]);
});
