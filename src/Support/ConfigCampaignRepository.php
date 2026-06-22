<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CampaignKit\Contracts\CampaignCtaAdapterContract;
use Lalalili\CampaignKit\Contracts\CampaignImageResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignPriceResolverContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;
use RuntimeException;

/**
 * 開箱即用的 config 驅動 repository（opt-in）。
 *
 * 給「活動頁 blade 採用通用 viewData 結構」的簡單 host 使用：只要設定
 * config("campaign-kit.models.campaign_model") 並把 CampaignRepositoryContract
 * 綁到本類別，即可零子類運作。需要客製查詢或 viewData 的 host 應改 extends
 * {@see AbstractCampaignRepository}。
 *
 * @extends AbstractCampaignRepository<Model>
 */
class ConfigCampaignRepository extends AbstractCampaignRepository
{
    public function __construct(
        protected readonly CampaignLayoutResolverContract $layoutResolver,
        protected readonly CampaignPriceResolverContract $priceResolver,
        protected readonly CampaignImageResolverContract $imageResolver,
        protected readonly CampaignCtaAdapterContract $ctaAdapter,
    ) {
    }

    /**
     * @return Builder<Model>
     */
    protected function baseQuery(): Builder
    {
        return $this->campaignModel()::query();
    }

    /**
     * @return class-string<Model>
     */
    protected function campaignModel(): string
    {
        $model = config('campaign-kit.models.campaign_model');

        if (is_string($model) && is_subclass_of($model, Model::class)) {
            return $model;
        }

        throw new RuntimeException(
            'campaign-kit: campaign model 尚未設定。請設定 config("campaign-kit.models.campaign_model") 或改 extends AbstractCampaignRepository 並覆寫 baseQuery()。',
        );
    }

    /**
     * @param Model $campaign
     */
    protected function buildRenderData(Model $campaign, CampaignRequestContext $context): ?CampaignRenderData
    {
        $viewName = $this->layoutResolver->resolveViewName($this->resolveType($campaign), $context->variant);

        if (! is_string($viewName) || ! view()->exists($viewName)) {
            return null;
        }

        $title = $this->resolveTitle($campaign);

        return new CampaignRenderData(
            view: $viewName,
            title: $title,
            description: '',
            viewData: [
                'campaign'    => $campaign,
                'title'       => $title,
                'description' => '',
                'variant'     => $context->variant,
                'isMobile'    => $context->isMobile,
                'prices'      => $this->priceResolver->resolve($campaign),
                'images'      => $this->imageResolver->resolve($campaign),
                'ctaConfig'   => $this->ctaAdapter->toFrontendConfig(),
            ],
        );
    }

    protected function resolveTitle(Model $campaign): string
    {
        $title = $campaign->getAttribute('campaign_title');

        return is_scalar($title) ? (string) $title : '';
    }
}
