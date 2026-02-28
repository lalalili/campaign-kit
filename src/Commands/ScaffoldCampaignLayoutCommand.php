<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ScaffoldCampaignLayoutCommand extends Command
{
    protected $signature = 'campaign-kit:scaffold-layout
        {type : Campaign type value}
        {--slug= : Slug used for preview image filename}
        {--force : Overwrite generated files}';

    protected $description = 'Scaffold campaign layout views/assets and register type in campaign-kit-layouts.php';

    public function handle(): int
    {
        $type = trim($this->stringArgument('type'));

        if ($type === '') {
            $this->error('Type is required.');

            return self::FAILURE;
        }

        $slugOption = trim($this->stringOption('slug'));
        $slug = $slugOption !== '' ? $slugOption : ('type' . $type);
        $force = (bool) $this->option('force');

        $replacements = [
            '{{TYPE}}'     => $type,
            '{{SLUG}}'     => $slug,
            '{{TYPE_INT}}' => ctype_digit($type) ? (string) (int) $type : $type,
        ];

        $stubRoot = dirname(__DIR__, 2) . '/stubs';
        $targets = [
            $stubRoot . '/views/campaigns/type.stub.blade.php'                 => resource_path('views/campaigns/type' . $type . '.blade.php'),
            $stubRoot . '/views/campaigns/type_mobile.stub.blade.php'          => resource_path('views/campaigns/type' . $type . '_mobile.blade.php'),
            $stubRoot . '/views/campaigns/previews/type.stub.blade.php'        => resource_path('views/campaigns/previews/type' . $type . '.blade.php'),
            $stubRoot . '/views/campaigns/previews/type_mobile.stub.blade.php' => resource_path('views/campaigns/previews/type' . $type . '_mobile.blade.php'),
            $stubRoot . '/css/campaigns/type.stub.css'                         => public_path('css/campaigns/type' . $type . '.css'),
            $stubRoot . '/css/campaigns/type_mobile.stub.css'                  => public_path('css/campaigns/type' . $type . '_mobile.css'),
            $stubRoot . '/js/campaign-type.stub.js'                            => resource_path('js/campaign-type' . $type . '.js'),
        ];

        foreach ($targets as $source => $target) {
            if (! File::exists($source)) {
                $this->error('Missing stub: ' . $source);

                return self::FAILURE;
            }

            if (File::exists($target) && ! $force) {
                $this->warn('Skip existing file: ' . $target);
                continue;
            }

            File::ensureDirectoryExists(dirname($target));
            $content = strtr((string) File::get($source), $replacements);
            File::put($target, $content);
            $this->line('Generated: ' . $target);
        }

        $this->registerType($type, $slug);

        return self::SUCCESS;
    }

    private function registerType(string $type, string $slug): void
    {
        $layoutsFile = base_path('campaign-kit-layouts.php');
        $types = [];

        if (File::exists($layoutsFile)) {
            $loaded = require $layoutsFile;
            if (is_array($loaded)) {
                $types = $loaded;
            }
        }

        $types[$type] = [
            'slug'  => $slug,
            'views' => [
                'desktop' => 'campaigns.previews.type' . $type,
                'mobile'  => 'campaigns.previews.type' . $type . '_mobile',
            ],
            'data' => [
                'campaign_title'   => 'Scaffold Campaign Type ' . $type,
                'primary_title'    => 'Primary Section',
                'secondary_title'  => 'Secondary Section',
                'additional_title' => 'Additional Section',
                'items'            => [
                    [
                        'prod_no' => '978000000001',
                        'title'   => 'Preview Book A',
                        'author'  => 'Author A',
                        'price'   => '399',
                        'summary' => 'Scaffold preview item.',
                    ],
                ],
            ],
        ];

        ksort($types);

        $content = "<?php\n\nreturn " . var_export($types, true) . ";\n";
        File::put($layoutsFile, $content);

        $this->info('Registered type in: ' . $layoutsFile);
    }

    private function stringArgument(string $name): string
    {
        $argument = $this->argument($name);

        if (is_string($argument)) {
            return $argument;
        }

        if (is_int($argument) || is_float($argument)) {
            return (string) $argument;
        }

        return '';
    }

    private function stringOption(string $name): string
    {
        $option = $this->option($name);

        if (is_string($option)) {
            return $option;
        }

        if (is_int($option) || is_float($option)) {
            return (string) $option;
        }

        return '';
    }
}
