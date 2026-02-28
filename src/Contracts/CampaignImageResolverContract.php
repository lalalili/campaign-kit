<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Contracts;

interface CampaignImageResolverContract
{
    /**
     * @return array<string, string>
     */
    public function resolve(mixed $campaign): array;
}
