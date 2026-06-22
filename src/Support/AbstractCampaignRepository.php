<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Lalalili\CampaignKit\Contracts\CampaignRepositoryContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;

/**
 * 可重用的 Campaign repository 骨架。
 *
 * 處理兩個 host 真正共用的部分：依 id / slug 查詢 + null 守衛，並委派給子類的
 * {@see baseQuery()}（通常加上有效期間 scope）與 {@see buildRenderData()}
 * （host 專屬的 view 解析與 viewData 組裝）。另提供 {@see resolveType()} 等
 * 共用小工具。各 host 只需 extends 本類別即可省去查詢與守衛樣板。
 *
 * @template TModel of Model
 */
abstract class AbstractCampaignRepository implements CampaignRepositoryContract
{
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
     * host 專屬：解析 view 名稱、做 view 存在守衛、組裝 CampaignRenderData。
     *
     * @param TModel $campaign
     */
    abstract protected function buildRenderData(Model $campaign, CampaignRequestContext $context): ?CampaignRenderData;

    protected function slugColumn(): string
    {
        $column = config('campaign-kit.models.slug_column', 'url_slug');

        return is_string($column) && $column !== '' ? $column : 'url_slug';
    }

    protected function typeColumn(): string
    {
        return 'type';
    }

    /**
     * 取出活動 type 的純量值（BackedEnum 取 value，否則取 int/string）。
     *
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
