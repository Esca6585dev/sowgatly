<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitPushCommand extends Command
{
    protected $signature = 'git:push {message? : The commit message}';
    protected $description = 'Push changes to the Git repository';

    public function handle()
    {
        $message = $this->argument('message') ?? 'Update from Artisan command';
        $fullMessage = sprintf('"%s - inside data updated"', $message);

        $this->info('git reset --hard');
        exec('git reset --hard');

        $this->info('Adding changes...');
        exec('git add .');

        $this->info('Committing changes...');
        exec(sprintf('git commit -m %s', escapeshellarg($fullMessage)));

        $this->info('Pushing to remote...');
        exec('git push --force');

        $this->info('Changes pushed successfully!');

        $this->info('Generating Swagger documentation...');
        $this->call('swagger:generate');
    }
}