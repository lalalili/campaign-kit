<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Contracts;

interface CampaignCtaAdapterContract
{
    /**
     * @return array<string, mixed>
     */
    public function toFrontendConfig(): array;
}
