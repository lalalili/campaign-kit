<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Contracts;

use Illuminate\Http\Request;

interface CampaignLayoutResolverContract
{
    public function resolveVariant(Request $request): string;

    public function resolveViewName(int|string|null $type, string $variant): ?string;
}
