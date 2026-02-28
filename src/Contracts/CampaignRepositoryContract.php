<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Contracts;

use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;

interface CampaignRepositoryContract
{
    public function findById(string $campaignId, CampaignRequestContext $context): ?CampaignRenderData;

    public function findBySlug(string $slug, CampaignRequestContext $context): ?CampaignRenderData;
}
