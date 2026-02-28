<?php

declare(strict_types=1);

namespace Lalalili\CampaignKit\Commands;

use Illuminate\Console\Command;

final class InstallCampaignKitCommand extends Command
{
    protected $signature = 'campaign-kit:install {--force : Overwrite published files}';

    protected $description = 'Install Campaign Kit resources (config, views, assets, and preview capture script).';

    public function handle(): int
    {
        $params = ['--provider' => 'Lalalili\\CampaignKit\\CampaignKitServiceProvider'];

        if ((bool) $this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', array_merge($params, ['--tag' => 'campaign-kit-config']));
        $this->call('vendor:publish', array_merge($params, ['--tag' => 'campaign-kit-views']));
        $this->call('vendor:publish', array_merge($params, ['--tag' => 'campaign-kit-assets']));
        $this->call('vendor:publish', array_merge($params, ['--tag' => 'campaign-kit-bin']));

        $this->info('Campaign Kit installation completed.');

        return self::SUCCESS;
    }
}
