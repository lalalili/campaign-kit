<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

use Illuminate\Http\Request;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;

final class ConfigCampaignLayoutResolver implements CampaignLayoutResolverContract
{
    public function resolveVariant(Request $request): string
    {
        $agent = strtolower((string) $request->userAgent());

        if (str_contains($agent, 'iphone') || str_contains($agent, 'android') || str_contains($agent, 'mobile')) {
            return 'mobile';
        }

        return 'desktop';
    }

    public function resolveViewName(int|string|null $type, string $variant): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }

        $types = (array) data_get(config('campaign-kit.preview', []), 'types', []);
        $typeConfig = $types[(string) $type] ?? $types[(int) $type] ?? null;

        if (! is_array($typeConfig)) {
            return null;
        }

        $viewName = data_get($typeConfig, 'views.' . $variant);

        return is_string($viewName) && $viewName !== '' ? $viewName : null;
    }
}
