<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Contracts;

interface CampaignPriceResolverContract
{
    /**
     * @return array<string, string|null>
     */
    public function resolve(mixed $campaign): array;
}
