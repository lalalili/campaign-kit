<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignRepositoryContract;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;

final class FrontEndCampaignController extends Controller
{
    public function __construct(
        private readonly CampaignRepositoryContract $repository,
        private readonly CampaignLayoutResolverContract $layoutResolver,
    ) {
    }

    public function show(string $campaignId, Request $request): View|RedirectResponse
    {
        $context = $this->makeContext($request);
        $renderData = $this->repository->findById($campaignId, $context);

        if ($renderData === null) {
            return $this->expiredRedirect();
        }

        /** @var view-string $viewName */
        $viewName = $renderData->view;

        return view($viewName, $renderData->viewData);
    }

    public function showBySlug(string $slug, Request $request): View|RedirectResponse
    {
        $context = $this->makeContext($request);
        $renderData = $this->repository->findBySlug($slug, $context);

        if ($renderData === null) {
            return $this->expiredRedirect();
        }

        /** @var view-string $viewName */
        $viewName = $renderData->view;

        return view($viewName, $renderData->viewData);
    }

    private function makeContext(Request $request): CampaignRequestContext
    {
        $variant = $this->layoutResolver->resolveVariant($request);

        return new CampaignRequestContext(
            variant: $variant,
            isMobile: $variant === 'mobile',
        );
    }

    private function expiredRedirect(): RedirectResponse
    {
        $routeName = $this->configString('campaign-kit.redirect.route_name', 'home');
        $target = Route::has($routeName) ? route($routeName) : '/';
        $toastSessionKey = $this->configString('campaign-kit.redirect.toast_session_key', 'toast-alert');
        $messageKey = $this->configString('campaign-kit.redirect.expired_message_key', 'global.campaign_expired');

        return redirect($target)->with($toastSessionKey, __($messageKey));
    }

    private function configString(string $key, string $default): string
    {
        $value = config($key, $default);

        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $default;
    }
}
