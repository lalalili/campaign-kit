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
use RuntimeException;

/**
 * 可重用的 Campaign repository 骨架（template method）。
 *
 * 處理共通的「查詢 / null 守衛 / view 名稱解析 / CampaignRenderData 組裝」，
 * 把 host 專屬的 viewData 組裝交給子類的 {@see buildViewData()}。各 host 只需
 * extends 本類別並覆寫需要客製的 hook，即可避免重複撰寫查詢與守衛樣板。
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

    /**
     * host 專屬的 viewData 組裝（餵給活動頁 blade 的變數）。
     *
     * @return array<string, mixed>
     */
    abstract protected function buildViewData(Model $campaign, CampaignRequestContext $context): array;

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
            'campaign-kit: campaign model 尚未設定。請設定 config("campaign-kit.models.campaign_model") 或覆寫 campaignModel()。',
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

    protected function resolveTitle(Model $campaign): string
    {
        $title = $campaign->getAttribute($this->titleColumn());

        return is_scalar($title) ? (string) $title : '';
    }

    protected function resolveDescription(Model $campaign): string
    {
        return '';
    }

    protected function resolveViewName(Model $campaign, CampaignRequestContext $context): ?string
    {
        return $this->layoutResolver->resolveViewName($this->resolveType($campaign), $context->variant);
    }

    protected function resolveType(Model $campaign): int|string|null
    {
        $type = $campaign->getAttribute($this->typeColumn());

        if ($type instanceof BackedEnum) {
            return $type->value;
        }

        return is_int($type) || is_string($type) ? $type : null;
    }
}
