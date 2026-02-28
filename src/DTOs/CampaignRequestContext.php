<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\DTOs;

final readonly class CampaignRequestContext
{
    public function __construct(
        public string $variant,
        public bool $isMobile,
    ) {
    }
}
