<?php

return [
    'redirect' => [
        'route_name'          => 'home',
        'expired_message_key' => 'global.campaign_expired',
        'toast_session_key'   => 'toast-alert',
    ],

    'routes' => [
        'enabled'               => true,
        'middleware'            => ['web'],
        'preview_path_template' => '/campaign/layout-preview/{type}/{variant}',
    ],

    'tracking' => [
        'enabled'             => true,
        'driver'              => 'data_layer',
        'data_layer_name'     => 'dataLayer',
        'gtag_measurement_id' => null,
        'currency'            => 'TWD',
        'affiliation'         => '',
        'event_map'           => [
            'view_promotion'   => 'view_promotion',
            'select_promotion' => 'select_promotion',
            'select_item'      => 'select_item',
            'add_to_cart'      => 'add_to_cart',
        ],
    ],

    'preview' => [
        'wait_for_selector'   => '.campaign-preview-root',
        'ignore_https_errors' => false,
        'output_dir'          => public_path('campaign/layouts'),
        'default_item_image'  => '/vendor/campaign-kit/images/default-book-thumbnail.svg',
        'unsupported_view'    => 'campaign-kit::campaigns.previews.unsupported',
        'types_file'          => base_path('campaign-kit-layouts.php'),
        'variants'            => [
            'desktop' => [
                'width'  => 1366,
                'height' => 1024,
            ],
            'mobile' => [
                'width'  => 430,
                'height' => 932,
            ],
        ],
        'types' => [
            1 => [
                'slug'  => 'default',
                'views' => [
                    'desktop' => 'campaign-kit::campaigns.previews.type1',
                    'mobile'  => 'campaign-kit::campaigns.previews.type1_mobile',
                ],
                'data' => [
                    'campaign_title'   => 'Campaign Preview',
                    'primary_title'    => 'Primary Section',
                    'primary_intro'    => 'Primary intro.',
                    'secondary_title'  => 'Secondary Section',
                    'secondary_intro'  => 'Secondary intro.',
                    'additional_title' => 'Additional Section',
                    'additional_intro' => 'Additional intro.',
                    'items'            => [
                        [
                            'prod_no' => '978000000001',
                            'title'   => 'Preview Book A',
                            'author'  => 'Author A',
                            'price'   => '420',
                            'summary' => 'Preview summary A.',
                        ],
                        [
                            'prod_no' => '978000000002',
                            'title'   => 'Preview Book B',
                            'author'  => 'Author B',
                            'price'   => '460',
                            'summary' => 'Preview summary B.',
                        ],
                        [
                            'prod_no' => '978000000003',
                            'title'   => 'Preview Book C',
                            'author'  => 'Author C',
                            'price'   => '380',
                            'summary' => 'Preview summary C.',
                        ],
                        [
                            'prod_no' => '978000000004',
                            'title'   => 'Preview Book D',
                            'author'  => 'Author D',
                            'price'   => '500',
                            'summary' => 'Preview summary D.',
                        ],
                    ],
                ],
            ],
        ],
    ],
];
