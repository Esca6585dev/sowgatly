<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use L5Swagger\Generator;
use Illuminate\Support\Facades\File;

class GenerateSwaggerJson extends Command
{
    protected $signature = 'swagger:generate {--force : Skip database operations and only generate Swagger docs}';
    protected $description = 'Manually generate Swagger JSON';

    public function handle(Generator $generator)
    {
        try {
            if (!$this->option('force')) {
                if ($this->confirm('This will refresh your database. Do you wish to continue?')) {
                    $this->performDatabaseOperations();
                } else {
                    $this->info('Database operations skipped.');
                }
            }

            $this->generateSwaggerDocs($generator);
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
        }
    }

    private function performDatabaseOperations()
    {
        $this->info('Migrate Fresh');
        $this->call('migrate:fresh');
        
        $this->info('Database Seed');
        $this->call('db:seed');
        
        $this->info('Clear cache');
        $this->call('optimize:clear');
    }

    private function generateSwaggerDocs(Generator $generator)
    {
        $this->info('Generating Swagger JSON...');
        $generator->generateDocs();
        $this->info('Swagger JSON generated successfully.');

        $this->info('Starting Swagger documentation generation...');
        $this->call('l5-swagger:generate');
    
        $source = storage_path('api-docs/api-docs.json');
        $destination = public_path('docs/api-docs.json');
        
        if (File::exists($source)) {
            File::copy($source, $destination);
            $this->info("File copied to: $destination");
        } else {
            $this->error("Source file not found: $source");
        }
    }
}