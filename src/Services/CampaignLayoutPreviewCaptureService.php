<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class CampaignLayoutPreviewCaptureService
{
    /**
     * @param array{width?: int, height?: int} $viewport
     */
    public function capture(
        string $url,
        array $viewport,
        string $waitForSelector,
        string $outputPath,
        string $scriptPath,
        bool $ignoreHttpsErrors = false,
    ): void {
        if (! file_exists($scriptPath)) {
            throw new RuntimeException('Preview capture script not found: ' . $scriptPath);
        }

        $process = new Process([
            'node',
            $scriptPath,
            '--url=' . $url,
            '--width=' . (int) ($viewport['width'] ?? 1366),
            '--height=' . (int) ($viewport['height'] ?? 1024),
            '--wait-for-selector=' . $waitForSelector,
            '--output=' . $outputPath,
            '--ignore-https-errors=' . ($ignoreHttpsErrors ? '1' : '0'),
        ], base_path(), null, null, 180);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                trim($process->getErrorOutput() ?: $process->getOutput() ?: 'Failed to capture preview image.')
            );
        }
    }

    public function convertPngToWebp(string $pngPath, string $webpPath): void
    {
        if (! extension_loaded('imagick')) {
            throw new RuntimeException('Imagick extension is required to convert preview images to webp.');
        }

        $image = new \Imagick($pngPath);
        $image->setImageFormat('webp');
        $image->setImageCompressionQuality(85);
        $image->writeImage($webpPath);
        $image->clear();
        $image->destroy();
    }
}
