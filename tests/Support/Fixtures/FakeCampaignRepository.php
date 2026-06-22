<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Tests\Support\Fixtures;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;
use Lalalili\CampaignKit\Support\AbstractCampaignRepository;

/**
 * 測試用 host repository：模擬 host 覆寫 baseQuery() / buildRenderData() 注入專屬結構。
 *
 * @extends AbstractCampaignRepository<FakeCampaign>
 */
class FakeCampaignRepository extends AbstractCampaignRepository
{
    public function __construct(
        private readonly CampaignLayoutResolverContract $layoutResolver,
    ) {
    }

    /**
     * @return Builder<FakeCampaign>
     */
    protected function baseQuery(): Builder
    {
        return FakeCampaign::query();
    }

    /**
     * @param FakeCampaign $campaign
     */
    protected function buildRenderData(Model $campaign, CampaignRequestContext $context): ?CampaignRenderData
    {
        $viewName = $this->layoutResolver->resolveViewName($this->resolveType($campaign), $context->variant);

        if (! is_string($viewName) || ! view()->exists($viewName)) {
            return null;
        }

        $title = (string) $campaign->campaign_title;

        return new CampaignRenderData(
            view: $viewName,
            title: $title,
            description: 'desc-for-' . $title,
            viewData: [
                'campaign' => $campaign,
                'flag'     => 'host-specific',
                'variant'  => $context->variant,
            ],
        );
    }
}
