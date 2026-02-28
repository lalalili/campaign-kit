<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Lalalili\CampaignKit\Support\CampaignLayoutPreviewFactory;

final class CampaignLayoutPreviewController extends Controller
{
    public function __construct(
        private readonly CampaignLayoutPreviewFactory $previewFactory,
    ) {
    }

    public function show(string $type, string $variant): View
    {
        if (! in_array($variant, $this->previewFactory->variants(), true)) {
            return $this->unsupported($type, $variant, 'unsupported_variant');
        }

        $typeConfig = $this->previewFactory->resolveTypeConfig($type);

        if ($typeConfig === []) {
            return $this->unsupported($type, $variant, 'unsupported_type');
        }

        $viewName = data_get($typeConfig, 'views.' . $variant);

        if (! is_string($viewName) || ! view()->exists($viewName)) {
            return $this->unsupported($type, $variant, 'missing_view');
        }

        return view()->make($viewName, $this->previewFactory->make($type, $variant));
    }

    private function unsupported(string $type, string $variant, string $reason): View
    {
        $configuredView = config('campaign-kit.preview.unsupported_view', 'campaign-kit::campaigns.previews.unsupported');
        $view = is_string($configuredView) && trim($configuredView) !== ''
            ? $configuredView
            : 'campaign-kit::campaigns.previews.unsupported';

        if (! view()->exists($view)) {
            $view = 'campaign-kit::campaigns.previews.unsupported';
        }

        $resolvedView = $view;

        return view()->make($resolvedView, [
            'type'    => $type,
            'variant' => $variant,
            'reason'  => $reason,
        ]);
    }
}
