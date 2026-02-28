<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use Lalalili\CampaignKit\Contracts\CampaignRepositoryContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;

final class NullCampaignRepository implements CampaignRepositoryContract
{
    public function findById(string $campaignId, CampaignRequestContext $context): ?CampaignRenderData
    {
        return null;
    }

    public function findBySlug(string $slug, CampaignRequestContext $context): ?CampaignRenderData
    {
        return null;
    }
}
