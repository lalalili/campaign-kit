<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;
use Lalalili\CampaignKit\Tests\Support\Fixtures\FakeCampaign;
use Lalalili\CampaignKit\Tests\Support\Fixtures\FakeCampaignRepository;

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

function makeFakeRepository(): FakeCampaignRepository
{
    return new FakeCampaignRepository(app(CampaignLayoutResolverContract::class));
}

it('returns null when the campaign id is not found', function (): void {
    expect(makeFakeRepository()->findById('999', new CampaignRequestContext('desktop', false)))
        ->toBeNull();
});

it('builds render data for an existing campaign found by id', function (): void {
    FakeCampaign::create(['url_slug' => 'summer', 'campaign_title' => 'Summer Sale', 'type' => 1]);

    $data = makeFakeRepository()->findById('1', new CampaignRequestContext('desktop', false));

    assert($data instanceof CampaignRenderData);

    expect($data->view)->toBe('campaign-kit::campaigns.previews.type1')
        ->and($data->title)->toBe('Summer Sale')
        ->and($data->description)->toBe('desc-for-Summer Sale')
        ->and($data->viewData)->toMatchArray(['flag' => 'host-specific', 'variant' => 'desktop']);
});

it('finds a campaign by slug and resolves the mobile view', function (): void {
    FakeCampaign::create(['url_slug' => 'winter', 'campaign_title' => 'Winter', 'type' => 1]);

    $data = makeFakeRepository()->findBySlug('winter', new CampaignRequestContext('mobile', true));

    expect($data?->view)->toBe('campaign-kit::campaigns.previews.type1_mobile');
});

it('returns null when no view resolves for the campaign type', function (): void {
    FakeCampaign::create(['url_slug' => 'unknown', 'campaign_title' => 'Unknown', 'type' => 99]);

    expect(makeFakeRepository()->findBySlug('unknown', new CampaignRequestContext('desktop', false)))
        ->toBeNull();
});
