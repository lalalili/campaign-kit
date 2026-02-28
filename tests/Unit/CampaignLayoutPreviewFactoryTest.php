<?php

declare(strict_types=1);

use Lalalili\CampaignKit\Support\CampaignLayoutPreviewFactory;

it('builds deterministic preview payload for desktop and mobile', function (): void {
    $factory = new CampaignLayoutPreviewFactory([
        'wait_for_selector' => '.campaign-preview-root',
        'variants'          => [
            'desktop' => ['width' => 1000, 'height' => 700],
            'mobile'  => ['width' => 430, 'height' => 932],
        ],
        'types' => [
            1 => [
                'slug'  => 'default',
                'views' => [
                    'desktop' => 'campaign-kit::campaigns.previews.type1',
                    'mobile'  => 'campaign-kit::campaigns.previews.type1_mobile',
                ],
                'data' => [
                    'campaign_title'      => 'Preview Campaign',
                    'banner_image'        => 'campaign/layouts/default.webp',
                    'banner_mobile_image' => 'campaign/layouts/default-mobile.webp',
                    'primary_title'       => 'Primary',
                    'secondary_title'     => 'Secondary',
                    'additional_title'    => 'Additional',
                    'items'               => [
                        ['title' => 'Book A', 'author' => 'Author A', 'price' => '100', 'summary' => 'Summary A'],
                        ['title' => 'Book B', 'author' => 'Author B', 'price' => '120', 'summary' => 'Summary B'],
                        ['title' => 'Book C', 'author' => 'Author C', 'price' => '130', 'summary' => 'Summary C'],
                        ['title' => 'Book D', 'author' => 'Author D', 'price' => '140', 'summary' => 'Summary D'],
                    ],
                ],
            ],
        ],
    ]);

    $desktopData = $factory->make(1, 'desktop');
    $mobileData = $factory->make(1, 'mobile');

    expect($desktopData['campaignTitle'])->toBe('Preview Campaign');
    expect($desktopData['bannerImageUrl'])->toBe('/campaign/layouts/default.webp');
    expect($mobileData['bannerImageUrl'])->toBe('/campaign/layouts/default-mobile.webp');
    expect($desktopData['primaryItems'])->toHaveCount(2);
    expect($desktopData['secondaryItems'])->toHaveCount(2);
    expect($desktopData['primaryItems'][0]['image_url'])->toBe('/vendor/campaign-kit/images/default-book-thumbnail.svg');
    expect($factory->viewport('mobile'))->toBe(['width' => 430, 'height' => 932]);
    expect($factory->waitForSelector())->toBe('.campaign-preview-root');
    expect($factory->filename(1, 'desktop'))->toBe('default.webp');
    expect($factory->filename(1, 'mobile'))->toBe('default-mobile.webp');
});

it('uses placeholder banner url when config does not provide banner image', function (): void {
    $factory = new CampaignLayoutPreviewFactory([
        'variants' => [
            'desktop' => ['width' => 1000, 'height' => 700],
        ],
        'types' => [
            1 => [
                'slug'  => 'default',
                'views' => [
                    'desktop' => 'campaign-kit::campaigns.previews.type1',
                ],
                'data' => [
                    'campaign_title' => 'Preview Campaign',
                    'items'          => [
                        ['title' => 'Book A', 'author' => 'Author A', 'price' => '100', 'summary' => 'Summary A'],
                    ],
                ],
            ],
        ],
    ]);

    $desktopData = $factory->make(1, 'desktop');

    expect($desktopData['bannerImageUrl'])->toBe('/campaign/layouts/placeholder.webp');
});
