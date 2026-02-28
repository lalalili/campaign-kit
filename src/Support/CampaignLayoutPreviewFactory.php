<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Support;

final class CampaignLayoutPreviewFactory
{
    /**
     * @param array<array-key, mixed> $previewConfig
     */
    public function __construct(
        private readonly array $previewConfig = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function make(int|string $type, string $variant): array
    {
        $typeConfig = $this->resolveTypeConfig($type);
        $data = $this->assocArray($typeConfig['data'] ?? []);
        $items = $this->normalizeItems($data['items'] ?? []);

        return [
            'campaignTitle'   => $this->stringFrom($data['campaign_title'] ?? null, 'Campaign Preview'),
            'bannerUrl'       => '#',
            'bannerImageUrl'  => $this->resolveBannerImageUrl($data, $variant),
            'primaryTitle'    => $this->stringFrom($data['primary_title'] ?? null, 'Primary Section'),
            'primaryIntro'    => $this->stringFrom($data['primary_intro'] ?? null),
            'secondaryTitle'  => $this->stringFrom($data['secondary_title'] ?? null, 'Secondary Section'),
            'secondaryIntro'  => $this->stringFrom($data['secondary_intro'] ?? null),
            'additionalTitle' => $this->stringFrom($data['additional_title'] ?? null, 'Additional Section'),
            'additionalIntro' => $this->stringFrom($data['additional_intro'] ?? null),
            'primaryItems'    => array_slice($items, 0, 2),
            'secondaryItems'  => array_slice($items, 2, 4),
            'additionalItems' => array_slice($items, 0, 8),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resolveTypeConfig(int|string $type): array
    {
        $types = $this->resolveTypes();
        $stringType = (string) $type;
        $typeConfig = $types[$stringType] ?? null;

        if (is_array($typeConfig)) {
            return $this->assocArray($typeConfig);
        }

        if (is_int($type) || ctype_digit($stringType)) {
            $numericTypeConfig = $types[(int) $stringType] ?? null;

            if (is_array($numericTypeConfig)) {
                return $this->assocArray($numericTypeConfig);
            }
        }

        return [];
    }

    /**
     * @return list<string>
     */
    public function types(): array
    {
        $typeValues = [];

        foreach (array_keys($this->resolveTypes()) as $type) {
            $typeValues[] = (string) $type;
        }

        return array_values(array_unique($typeValues));
    }

    /**
     * @return list<string>
     */
    public function variants(): array
    {
        $variants = [];

        foreach (array_keys($this->resolveVariantConfigs()) as $variant) {
            $variants[] = (string) $variant;
        }

        return $variants;
    }

    /**
     * @return array{width: int, height: int}|null
     */
    public function viewport(string $variant): ?array
    {
        $variantConfigs = $this->resolveVariantConfigs();
        $viewport = $variantConfigs[$variant] ?? null;

        if (! is_array($viewport)) {
            return null;
        }

        $viewportConfig = $this->assocArray($viewport);

        return [
            'width'  => $this->intFrom($viewportConfig['width'] ?? null, 1366),
            'height' => $this->intFrom($viewportConfig['height'] ?? null, 1024),
        ];
    }

    public function waitForSelector(): string
    {
        $config = $this->resolveConfig();

        return $this->stringFrom($config['wait_for_selector'] ?? null, '.campaign-preview-root');
    }

    public function outputDir(): string
    {
        $config = $this->resolveConfig();

        return $this->stringFrom($config['output_dir'] ?? null, public_path('campaign/layouts'));
    }

    public function ignoreHttpsErrors(): bool
    {
        $config = $this->resolveConfig();

        return (bool) ($config['ignore_https_errors'] ?? false);
    }

    public function captureScriptPath(): string
    {
        $config = $this->resolveConfig();
        $configured = $config['capture_script'] ?? null;

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return dirname(__DIR__, 2) . '/bin/capture-campaign-layout-preview.mjs';
    }

    public function slug(int|string $type): string
    {
        $typeConfig = $this->resolveTypeConfig($type);
        $slug = $this->stringFrom($typeConfig['slug'] ?? null, (string) $type);
        $trimmedSlug = trim($slug);

        return $trimmedSlug !== '' ? $trimmedSlug : (string) $type;
    }

    public function filename(int|string $type, string $variant): string
    {
        $slug = $this->slug($type);

        if ($variant === 'mobile') {
            return $slug . '-mobile.webp';
        }

        return $slug . '.webp';
    }

    /**
     * @return array<array-key, mixed>
     */
    private function resolveConfig(): array
    {
        if ($this->previewConfig !== []) {
            return $this->previewConfig;
        }

        $rawConfig = config('campaign-kit.preview', []);
        $config = is_array($rawConfig) ? $rawConfig : [];
        $typesFile = $config['types_file'] ?? null;

        if (is_string($typesFile) && $typesFile !== '' && file_exists($typesFile)) {
            $extraTypes = require $typesFile;

            if (is_array($extraTypes)) {
                $baseTypes = isset($config['types']) && is_array($config['types'])
                    ? $config['types']
                    : [];
                $config['types'] = array_replace($baseTypes, $extraTypes);
            }
        }

        return $config;
    }

    /**
     * @param mixed $rawItems
     * @return array<int, array<string, string>>
     */
    private function normalizeItems(mixed $rawItems): array
    {
        if (! is_array($rawItems)) {
            return [];
        }

        $defaultItemImageUrl = $this->resolveDefaultItemImageUrl();
        $items = [];

        foreach ($rawItems as $index => $rawItem) {
            if (! is_array($rawItem)) {
                continue;
            }

            $item = $this->assocArray($rawItem);

            $items[] = [
                'prod_no'    => $this->stringFrom($item['prod_no'] ?? null, '978000000000'),
                'title'      => $this->stringFrom($item['title'] ?? null, 'Preview Title'),
                'author'     => $this->stringFrom($item['author'] ?? null, 'Preview Author'),
                'price'      => $this->stringFrom($item['price'] ?? null, '0'),
                'summary'    => $this->stringFrom($item['summary'] ?? null),
                'image_url'  => $this->resolveItemImageUrl($item['image_url'] ?? null, $defaultItemImageUrl),
                'item_index' => $this->stringFrom($item['item_index'] ?? null, (string) $index),
            ];
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function resolveBannerImageUrl(array $data, string $variant): string
    {
        $mobileBanner = $this->stringFrom($data['banner_mobile_image'] ?? null);
        $defaultBanner = $this->stringFrom($data['banner_image'] ?? null);

        if ($variant === 'mobile' && $mobileBanner !== '') {
            return $this->toPublicUrl($mobileBanner);
        }

        if ($defaultBanner !== '') {
            return $this->toPublicUrl($defaultBanner);
        }

        return '/campaign/layouts/placeholder.webp';
    }

    private function toPublicUrl(string $path): string
    {
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }

    private function resolveDefaultItemImageUrl(): string
    {
        $config = $this->resolveConfig();
        $configured = $this->stringFrom($config['default_item_image'] ?? null);

        if ($configured !== '') {
            return $this->toPublicUrl($configured);
        }

        return '/vendor/campaign-kit/images/default-book-thumbnail.svg';
    }

    private function resolveItemImageUrl(mixed $value, string $default): string
    {
        $resolved = $this->stringFrom($value);

        if ($resolved === '') {
            return $default;
        }

        if (
            str_starts_with($resolved, 'http://')
            || str_starts_with($resolved, 'https://')
            || str_starts_with($resolved, 'data:')
        ) {
            return $resolved;
        }

        return $this->toPublicUrl($resolved);
    }

    /**
     * @return array<array-key, mixed>
     */
    private function resolveTypes(): array
    {
        $config = $this->resolveConfig();
        $types = $config['types'] ?? [];

        return is_array($types) ? $types : [];
    }

    /**
     * @return array<array-key, mixed>
     */
    private function resolveVariantConfigs(): array
    {
        $config = $this->resolveConfig();
        $variants = $config['variants'] ?? [];

        return is_array($variants) ? $variants : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function assocArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $result = [];

        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $result[$key] = $item;
            }
        }

        return $result;
    }

    private function stringFrom(mixed $value, string $default = ''): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return $default;
    }

    private function intFrom(mixed $value, int $default): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        return $default;
    }
}
