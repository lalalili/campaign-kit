<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Lalalili\CampaignKit\Services\CampaignLayoutPreviewCaptureService;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

beforeEach(function (): void {
    File::deleteDirectory(storage_path('framework/testing/campaign-kit-layouts'));
    File::ensureDirectoryExists(storage_path('framework/testing/campaign-kit-layouts'));
});

afterEach(function (): void {
    File::deleteDirectory(storage_path('framework/testing/campaign-kit-layouts'));
});

it('generates desktop and mobile webp files via capture service', function (): void {
    $service = new class () extends CampaignLayoutPreviewCaptureService {
        /**
         * @var list<array{
         *     url: string,
         *     viewport: array{width: int, height: int},
         *     waitForSelector: string,
         *     outputPath: string,
         *     scriptPath: string,
         *     ignoreHttpsErrors: bool
         * }>
         */
        public array $captureCalls = [];
        public int $convertCalls = 0;

        public function capture(
            string $url,
            array $viewport,
            string $waitForSelector,
            string $outputPath,
            string $scriptPath,
            bool $ignoreHttpsErrors = false
        ): void {
            $this->captureCalls[] = [
                'url'      => $url,
                'viewport' => [
                    'width'  => (int) ($viewport['width'] ?? 0),
                    'height' => (int) ($viewport['height'] ?? 0),
                ],
                'waitForSelector'   => $waitForSelector,
                'outputPath'        => $outputPath,
                'scriptPath'        => $scriptPath,
                'ignoreHttpsErrors' => $ignoreHttpsErrors,
            ];

            $png = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4////fwAJ+wP9QhN1XQAAAABJRU5ErkJggg==',
                true
            );

            if ($png === false) {
                throw new RuntimeException('Failed to decode preview png fixture.');
            }

            file_put_contents($outputPath, $png);
        }

        public function convertPngToWebp(string $pngPath, string $webpPath): void
        {
            $this->convertCalls++;
            file_put_contents($webpPath, 'webp-preview:' . basename($pngPath));
        }
    };

    app()->instance(CampaignLayoutPreviewCaptureService::class, $service);

    $status = Artisan::call('campaign-kit:generate-layout-previews', [
        '--type'     => [1],
        '--variant'  => 'desktop,mobile',
        '--base-url' => 'https://example.test',
    ]);

    expect($status)->toBe(ConsoleCommand::SUCCESS);

    expect($service->captureCalls)->toHaveCount(2);
    expect($service->convertCalls)->toBe(2);
    expect($service->captureCalls[0]['url'])->toContain('/campaign/layout-preview/1/');
    expect($service->captureCalls[0]['waitForSelector'])->toBe('.campaign-preview-root');
    expect($service->captureCalls[0]['ignoreHttpsErrors'])->toBeTrue();
    expect($service->captureCalls[0]['viewport'])->toHaveKeys(['width', 'height']);
    expect($service->captureCalls[0]['scriptPath'])->toContain('capture-campaign-layout-preview.mjs');
    expect(file_exists(storage_path('framework/testing/campaign-kit-layouts/default.webp')))->toBeTrue();
    expect(file_exists(storage_path('framework/testing/campaign-kit-layouts/default-mobile.webp')))->toBeTrue();
});

it('returns failure and keeps existing file when keep-on-fail is enabled', function (): void {
    $targetPath = storage_path('framework/testing/campaign-kit-layouts/default.webp');
    file_put_contents($targetPath, 'legacy-preview-content');

    $service = new class () extends CampaignLayoutPreviewCaptureService {
        public int $convertCalls = 0;

        public function capture(
            string $url,
            array $viewport,
            string $waitForSelector,
            string $outputPath,
            string $scriptPath,
            bool $ignoreHttpsErrors = false
        ): void {
            throw new RuntimeException('capture failed');
        }

        public function convertPngToWebp(string $pngPath, string $webpPath): void
        {
            $this->convertCalls++;
        }
    };

    app()->instance(CampaignLayoutPreviewCaptureService::class, $service);

    $status = Artisan::call('campaign-kit:generate-layout-previews', [
        '--type'         => [1],
        '--variant'      => 'desktop',
        '--base-url'     => 'https://example.test',
        '--keep-on-fail' => true,
    ]);

    expect($status)->toBe(ConsoleCommand::FAILURE);
    expect($service->convertCalls)->toBe(0);
    expect(file_get_contents($targetPath))->toBe('legacy-preview-content');
});
