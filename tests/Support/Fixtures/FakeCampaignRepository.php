<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Tests\Support\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;
use Lalalili\CampaignKit\Support\AbstractCampaignRepository;

/**
 * 測試用 host repository：模擬 host 覆寫 baseQuery() / buildViewData() 注入專屬結構。
 *
 * @extends AbstractCampaignRepository<FakeCampaign>
 */
class FakeCampaignRepository extends AbstractCampaignRepository
{
    /**
     * @return Builder<FakeCampaign>
     */
    protected function baseQuery(): Builder
    {
        return FakeCampaign::query();
    }

    /**
     * @param  FakeCampaign  $campaign
     * @return array<string, mixed>
     */
    protected function buildViewData(Model $campaign, CampaignRequestContext $context): array
    {
        return [
            'campaign' => $campaign,
            'flag'     => 'host-specific',
            'variant'  => $context->variant,
        ];
    }

    /**
     * @param FakeCampaign $campaign
     */
    protected function resolveDescription(Model $campaign): string
    {
        return 'desc-for-' . $this->resolveTitle($campaign);
    }
}
