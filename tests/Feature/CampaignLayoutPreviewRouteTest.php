<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

use function Pest\Laravel\get;

it('renders desktop layout preview page', function (): void {
    $response = get('/campaign/layout-preview/1/desktop');

    $response->assertSuccessful()
        ->assertSee('campaign-preview-root')
        ->assertSee('campaign-type1__inner')
        ->assertDontSee('sf-toolbar');
});

it('renders mobile layout preview page', function (): void {
    $response = get('/campaign/layout-preview/1/mobile');

    $response->assertSuccessful()
        ->assertSee('campaign-preview-root')
        ->assertSee('campaign-type1--mobile')
        ->assertDontSee('sf-toolbar');
});

it('renders unsupported preview placeholder for unknown type', function (): void {
    $response = get('/campaign/layout-preview/999/desktop');

    $response->assertSuccessful()
        ->assertSee('Preview is not available for this type');
});

it('renders unsupported preview placeholder for unsupported variant', function (): void {
    $response = get('/campaign/layout-preview/1/tablet');

    $response->assertSuccessful()
        ->assertSee('Preview is not available for this type');
});

it('registers campaign routes with web middleware by default', function (): void {
    $routes = Route::getRoutes();

    $previewRoute = $routes->getByName('campaign.layout-preview');
    $slugRoute = $routes->getByName('campaign.show.slug');
    $idRoute = $routes->getByName('campaign.show.id');

    expect($previewRoute?->gatherMiddleware() ?? [])->toContain('web');
    expect($slugRoute?->gatherMiddleware() ?? [])->toContain('web');
    expect($idRoute?->gatherMiddleware() ?? [])->toContain('web');
});
