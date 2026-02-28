<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\DTOs;

final readonly class CampaignRenderData
{
    /**
     * @param view-string $view
     * @param array<string, mixed> $viewData
     */
    public function __construct(
        public string $view,
        public string $title,
        public string $description,
        public array $viewData,
    ) {
    }
}
