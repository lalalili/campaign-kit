<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Tests\Support\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;
use Lalalili\CampaignKit\Support\AbstractCampaignRepository;

/**
 * 測試用 host repository：模擬 host 覆寫 buildViewData() 注入專屬結構。
 */
class FakeCampaignRepository extends AbstractCampaignRepository
{
    /**
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

    protected function resolveDescription(Model $campaign): string
    {
        return 'desc-for-' . $this->resolveTitle($campaign);
    }
}
