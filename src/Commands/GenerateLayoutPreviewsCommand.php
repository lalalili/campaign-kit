<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Lalalili\CampaignKit\Services\CampaignLayoutPreviewCaptureService;
use Lalalili\CampaignKit\Support\CampaignLayoutPreviewFactory;
use RuntimeException;
use Symfony\Component\Console\Command\Command as ConsoleCommand;

final class GenerateLayoutPreviewsCommand extends Command
{
    protected $signature = 'campaign-kit:generate-layout-previews
        {--type=* : Campaign type value, support multiple values or comma-separated values}
        {--variant=desktop,mobile : Comma-separated variants}
        {--base-url= : Base URL used to capture preview pages}
        {--keep-on-fail : Keep existing webp file when capture fails}';

    protected $description = 'Generate campaign layout preview images from preview routes.';

    public function handle(CampaignLayoutPreviewCaptureService $captureService, CampaignLayoutPreviewFactory $previewFactory): int
    {
        $types = $this->resolveTypes($previewFactory);
        $variants = $this->resolveVariants($previewFactory);
        $baseUrl = $this->resolveBaseUrl();
        $outputDir = rtrim($previewFactory->outputDir(), '/');
        $keepOnFail = (bool) $this->option('keep-on-fail');
        $ignoreHttpsErrors = $previewFactory->ignoreHttpsErrors();

        if ($types === []) {
            $this->error('No valid campaign types to process.');

            return ConsoleCommand::FAILURE;
        }

        if ($variants === []) {
            $this->error('No valid variants to process.');

            return ConsoleCommand::FAILURE;
        }

        File::ensureDirectoryExists($outputDir);

        $failures = [];
        $successCount = 0;

        foreach ($types as $type) {
            foreach ($variants as $variant) {
                $viewport = $previewFactory->viewport($variant);

                if (! is_array($viewport)) {
                    $failures[] = sprintf('type=%s variant=%s: viewport not configured', $type, $variant);
                    continue;
                }

                $targetPath = $outputDir . '/' . $previewFactory->filename($type, $variant);
                $tempPngPath = $outputDir . '/.' . $previewFactory->slug($type) . '-' . $variant . '-' . uniqid('', true) . '.png';
                $previewPathTemplate = config('campaign-kit.routes.preview_path_template', '/campaign/layout-preview/{type}/{variant}');

                if (! is_string($previewPathTemplate) || trim($previewPathTemplate) === '') {
                    $previewPathTemplate = '/campaign/layout-preview/{type}/{variant}';
                }

                $previewPath = str_replace(
                    ['{type}', '{variant}'],
                    [$type, $variant],
                    $previewPathTemplate
                );
                $previewUrl = rtrim($baseUrl, '/') . $previewPath;

                try {
                    $captureService->capture(
                        $previewUrl,
                        $viewport,
                        $previewFactory->waitForSelector(),
                        $tempPngPath,
                        $previewFactory->captureScriptPath(),
                        $ignoreHttpsErrors,
                    );
                    $captureService->convertPngToWebp($tempPngPath, $targetPath);
                    $this->line(sprintf('Generated: type=%s variant=%s -> %s', $type, $variant, $targetPath));
                    $successCount++;
                } catch (\Throwable $exception) {
                    if (! $keepOnFail && file_exists($targetPath)) {
                        @unlink($targetPath);
                    }

                    $failures[] = sprintf('type=%s variant=%s: %s', $type, $variant, $exception->getMessage());
                } finally {
                    if (file_exists($tempPngPath)) {
                        @unlink($tempPngPath);
                    }
                }
            }
        }

        $this->info(sprintf('Campaign preview generation completed: success %d, failure %d', $successCount, count($failures)));

        if ($failures !== []) {
            foreach ($failures as $failure) {
                $this->error($failure);
            }

            return ConsoleCommand::FAILURE;
        }

        return ConsoleCommand::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function resolveTypes(CampaignLayoutPreviewFactory $previewFactory): array
    {
        $rawTypes = $this->resolveTypeOptionValues();
        $values = [];

        foreach ($rawTypes as $rawType) {
            foreach (explode(',', $rawType) as $segment) {
                $segment = trim($segment);

                if ($segment !== '') {
                    $values[] = $segment;
                }
            }
        }

        $availableTypes = $previewFactory->types();

        if ($values === []) {
            return $availableTypes;
        }

        $selectedTypes = [];

        foreach (array_values(array_unique($values)) as $value) {
            if (in_array($value, $availableTypes, true) || in_array((string) (int) $value, $availableTypes, true)) {
                $selectedTypes[] = in_array($value, $availableTypes, true) ? $value : (string) (int) $value;
                continue;
            }

            $this->warn('Ignore unknown type: ' . $value);
        }

        return array_values(array_unique($selectedTypes));
    }

    /**
     * @return list<string>
     */
    private function resolveVariants(CampaignLayoutPreviewFactory $previewFactory): array
    {
        $rawVariantOption = $this->stringOption('variant', 'desktop,mobile');
        $segments = array_values(array_filter(array_map(
            static fn (string $value): string => trim($value),
            explode(',', $rawVariantOption)
        ), static fn (string $value): bool => $value !== ''));

        $supportedVariants = $previewFactory->variants();
        $variants = [];

        foreach (array_values(array_unique($segments)) as $segment) {
            if (in_array($segment, $supportedVariants, true)) {
                $variants[] = $segment;
                continue;
            }

            $this->warn('Ignore unknown variant: ' . $segment);
        }

        return $variants;
    }

    private function resolveBaseUrl(): string
    {
        $baseUrl = trim($this->stringOption('base-url'));

        if ($baseUrl !== '') {
            return $baseUrl;
        }

        $appUrl = config('app.url');
        $configBaseUrl = is_string($appUrl) ? trim($appUrl) : '';

        if ($configBaseUrl === '') {
            throw new RuntimeException('APP_URL is empty, please provide --base-url.');
        }

        return $configBaseUrl;
    }

    /**
     * @return list<string>
     */
    private function resolveTypeOptionValues(): array
    {
        $raw = $this->option('type');

        if (! is_array($raw)) {
            return [];
        }

        $values = [];

        foreach ($raw as $value) {
            if (is_string($value)) {
                $values[] = $value;
                continue;
            }

            if (is_int($value) || is_float($value)) {
                $values[] = (string) $value;
                continue;
            }
        }

        return $values;
    }

    private function stringOption(string $name, string $default = ''): string
    {
        $option = $this->option($name);

        if (is_string($option)) {
            return $option;
        }

        if (is_int($option) || is_float($option)) {
            return (string) $option;
        }

        return $default;
    }
}
