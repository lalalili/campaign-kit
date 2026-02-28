<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use Lalalili\CampaignKit\Contracts\CampaignCtaAdapterContract;

final class NullCampaignCtaAdapter implements CampaignCtaAdapterContract
{
    public function toFrontendConfig(): array
    {
        return [
            'enabled' => false,
        ];
    }
}
