<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignRepositoryContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;

/**
 * 可重用的 Campaign repository 骨架（template method）。
 *
 * 處理共通的「查詢 / null 守衛 / view 名稱解析 / CampaignRenderData 組裝」，
 * 把 host 專屬的查詢與 viewData 組裝交給子類的 {@see baseQuery()} 與
 * {@see buildViewData()}。各 host 只需 extends 本類別並覆寫需要客製的 hook，
 * 即可避免重複撰寫查詢與守衛樣板。
 *
 * @template TModel of Model
 */
abstract class AbstractCampaignRepository implements CampaignRepositoryContract
{
    public function __construct(
        protected readonly CampaignLayoutResolverContract $layoutResolver,
    ) {
    }

    public function findById(string $campaignId, CampaignRequestContext $context): ?CampaignRenderData
    {
        $campaign = $this->baseQuery()->find($campaignId);

        return $campaign instanceof Model ? $this->buildRenderData($campaign, $context) : null;
    }

    public function findBySlug(string $slug, CampaignRequestContext $context): ?CampaignRenderData
    {
        $campaign = $this->baseQuery()->where($this->slugColumn(), $slug)->first();

        return $campaign instanceof Model ? $this->buildRenderData($campaign, $context) : null;
    }

    /**
     * 取得活動查詢；host 通常加上有效期間 scope（例如 ->valid()）。
     *
     * @return Builder<TModel>
     */
    abstract protected function baseQuery(): Builder;

    /**
     * host 專屬的 viewData 組裝（餵給活動頁 blade 的變數）。
     *
     * @param  TModel  $campaign
     * @return array<string, mixed>
     */
    abstract protected function buildViewData(Model $campaign, CampaignRequestContext $context): array;

    /**
     * @param TModel $campaign
     */
    protected function buildRenderData(Model $campaign, CampaignRequestContext $context): ?CampaignRenderData
    {
        $relations = $this->eagerLoads();

        if ($relations !== []) {
            $campaign->loadMissing($relations);
        }

        $viewName = $this->resolveViewName($campaign, $context);

        if (! is_string($viewName) || ! view()->exists($viewName)) {
            return null;
        }

        return new CampaignRenderData(
            view: $viewName,
            title: $this->resolveTitle($campaign),
            description: $this->resolveDescription($campaign),
            viewData: $this->buildViewData($campaign, $context),
        );
    }

    protected function slugColumn(): string
    {
        $column = config('campaign-kit.models.slug_column', 'url_slug');

        return is_string($column) && $column !== '' ? $column : 'url_slug';
    }

    protected function typeColumn(): string
    {
        return 'type';
    }

    protected function titleColumn(): string
    {
        return 'campaign_title';
    }

    /**
     * 需要 eager-load 的關聯（host 覆寫）。
     *
     * @return array<int, string>
     */
    protected function eagerLoads(): array
    {
        return [];
    }

    /**
     * @param TModel $campaign
     */
    protected function resolveTitle(Model $campaign): string
    {
        $title = $campaign->getAttribute($this->titleColumn());

        return is_scalar($title) ? (string) $title : '';
    }

    /**
     * @param TModel $campaign
     */
    protected function resolveDescription(Model $campaign): string
    {
        return '';
    }

    /**
     * @param TModel $campaign
     */
    protected function resolveViewName(Model $campaign, CampaignRequestContext $context): ?string
    {
        return $this->layoutResolver->resolveViewName($this->resolveType($campaign), $context->variant);
    }

    /**
     * @param TModel $campaign
     */
    protected function resolveType(Model $campaign): int|string|null
    {
        $type = $campaign->getAttribute($this->typeColumn());

        if ($type instanceof BackedEnum) {
            return $type->value;
        }

        return is_int($type) || is_string($type) ? $type : null;
    }
}
