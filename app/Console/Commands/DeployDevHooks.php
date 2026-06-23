<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class DeployDevHooks extends Command
{
    protected $signature = 'deploy:dev-hooks';
    protected $description = 'Safely run development hooks if the packages exist.';

    public function handle()
    {
        if (array_key_exists('boost:update', Artisan::all())) {
            $this->info('Attempting to run boost:update...');

            try {
                // Wrap the call in a try/catch block to swallow the validation exception
                $this->call('boost:update', [
                    '--ansi' => true,
                    '--no-interaction' => true,
                ]);
            } catch (\Throwable $e) {
                // If it crashes due to missing inputs or validation, log it and move on safely
                $this->warn('boost:update skipped: Requires interactive input during installation.');
            }
        } else {
            $this->info('boost:update not found (skipping dev dependency hook).');
        }

        return 0;
    }
}
