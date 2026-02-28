<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Lalalili\CampaignKit\Contracts\CampaignLayoutResolverContract;
use Lalalili\CampaignKit\Contracts\CampaignRepositoryContract;
use Lalalili\CampaignKit\DTOs\CampaignRenderData;
use Lalalili\CampaignKit\DTOs\CampaignRequestContext;

use function Pest\Laravel\get;

it('resolves request context and renders campaign by id and slug', function (): void {
    $resolver = new class () implements CampaignLayoutResolverContract {
        public function resolveVariant(Request $request): string
        {
            return 'mobile';
        }

        public function resolveViewName(int|string|null $type, string $variant): ?string
        {
            return null;
        }
    };

    $repository = new class () implements CampaignRepositoryContract {
        public ?CampaignRequestContext $idContext = null;
        public ?CampaignRequestContext $slugContext = null;

        public function findById(string $campaignId, CampaignRequestContext $context): ?CampaignRenderData
        {
            $this->idContext = $context;
            if ($campaignId === '') {
                return null;
            }

            /** @var view-string $view */
            $view = 'campaign-kit::campaigns.previews.unsupported';

            return new CampaignRenderData(
                view: $view,
                title: 't',
                description: 'd',
                viewData: [
                    'type'    => '1',
                    'variant' => 'mobile',
                    'reason'  => 'test-id',
                ],
            );
        }

        public function findBySlug(string $slug, CampaignRequestContext $context): ?CampaignRenderData
        {
            $this->slugContext = $context;
            if ($slug === '') {
                return null;
            }

            /** @var view-string $view */
            $view = 'campaign-kit::campaigns.previews.unsupported';

            return new CampaignRenderData(
                view: $view,
                title: 't',
                description: 'd',
                viewData: [
                    'type'    => '1',
                    'variant' => 'mobile',
                    'reason'  => 'test-slug',
                ],
            );
        }
    };

    app()->instance(CampaignLayoutResolverContract::class, $resolver);
    app()->instance(CampaignRepositoryContract::class, $repository);

    get('/campaign/123')
        ->assertSuccessful()
        ->assertSee('Preview is not available for this type');

    get('/campaign/s/test-slug')
        ->assertSuccessful()
        ->assertSee('Preview is not available for this type');

    expect($repository->idContext?->variant)->toBe('mobile');
    expect($repository->idContext?->isMobile)->toBeTrue();
    expect($repository->slugContext?->variant)->toBe('mobile');
    expect($repository->slugContext?->isMobile)->toBeTrue();
});

it('redirects to fallback path when campaign is missing', function (): void {
    $resolver = new class () implements CampaignLayoutResolverContract {
        public function resolveVariant(Request $request): string
        {
            return 'desktop';
        }

        public function resolveViewName(int|string|null $type, string $variant): ?string
        {
            return null;
        }
    };

    $repository = new class () implements CampaignRepositoryContract {
        public function findById(string $campaignId, CampaignRequestContext $context): ?CampaignRenderData
        {
            return null;
        }

        public function findBySlug(string $slug, CampaignRequestContext $context): ?CampaignRenderData
        {
            return null;
        }
    };

    app()->instance(CampaignLayoutResolverContract::class, $resolver);
    app()->instance(CampaignRepositoryContract::class, $repository);

    config()->set('campaign-kit.redirect.route_name', 'missing-home');
    config()->set('campaign-kit.redirect.toast_session_key', 'toast-alert');

    get('/campaign/404')
        ->assertRedirect('/')
        ->assertSessionHas('toast-alert');
});
