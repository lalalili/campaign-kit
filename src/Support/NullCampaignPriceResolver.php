<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use Lalalili\CampaignKit\Contracts\CampaignPriceResolverContract;

final class NullCampaignPriceResolver implements CampaignPriceResolverContract
{
    public function resolve(mixed $campaign): array
    {
        return [];
    }
}
