<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GitPullCommand extends Command
{
    protected $signature = 'git:pull';
    protected $description = 'Reset local changes and pull from the Git repository';

    public function handle()
    {
        $this->info('Resetting local changes...');
        exec('git reset --hard', $resetOutput, $resetReturnCode);

        if ($resetReturnCode !== 0) {
            $this->error('Failed to reset local changes. Error output:');
            foreach ($resetOutput as $line) {
                $this->error($line);
            }
            return;
        }

        $this->info('Local changes reset successfully.');

        $this->info('Pulling changes from remote...');
        exec('git pull', $pullOutput, $pullReturnCode);

        if ($pullReturnCode === 0) {
            $this->info('Changes pulled successfully!');
            foreach ($pullOutput as $line) {
                $this->line($line);
            }
        } else {
            $this->error('Failed to pull changes. Error output:');
            foreach ($pullOutput as $line) {
                $this->error($line);
            }
        }

        $this->info('Generating Swagger documentation...');
        $this->call('swagger:generate');
    }
}