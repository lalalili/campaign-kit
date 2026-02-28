<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use Lalalili\CampaignKit\Contracts\CampaignImageResolverContract;

final class NullCampaignImageResolver implements CampaignImageResolverContract
{
    public function resolve(mixed $campaign): array
    {
        return [];
    }
}
