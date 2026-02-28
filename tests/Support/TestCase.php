<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Tests\Support;

use Lalalili\CampaignKit\CampaignKitServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [CampaignKitServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        $app['config']->set('app.url', 'https://example.test');
        $app['config']->set('view.paths', [__DIR__ . '/../../resources/views']);
        $app['config']->set('campaign-kit.routes.enabled', true);
        $app['config']->set('campaign-kit.preview.ignore_https_errors', true);
        $app['config']->set('campaign-kit.preview.output_dir', storage_path('framework/testing/campaign-kit-layouts'));
        $app['config']->set('campaign-kit.preview.types', [
            1 => [
                'slug'  => 'default',
                'views' => [
                    'desktop' => 'campaign-kit::campaigns.previews.type1',
                    'mobile'  => 'campaign-kit::campaigns.previews.type1_mobile',
                ],
                'data' => [
                    'campaign_title' => 'Preview Campaign',
                    'items'          => [
                        ['title' => 'Book A', 'author' => 'Author A', 'price' => '100', 'summary' => 'Summary A'],
                        ['title' => 'Book B', 'author' => 'Author B', 'price' => '120', 'summary' => 'Summary B'],
                        ['title' => 'Book C', 'author' => 'Author C', 'price' => '130', 'summary' => 'Summary C'],
                        ['title' => 'Book D', 'author' => 'Author D', 'price' => '140', 'summary' => 'Summary D'],
                    ],
                ],
            ],
        ]);
    }
}
