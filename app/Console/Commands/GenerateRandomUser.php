<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Str;

class GenerateRandomUser extends Command
{
    protected $signature = 'generate:random-user';
    protected $description = 'Generate a random user';


    public function handle()
    {
        $user = User::create([
            'name' => 'User ' . Str::random(5),
            'email' => $this->generateRandomEmail(),
            'phone_number' => $this->generateRandomPhoneNumber(),
            'password' => bcrypt('password'),
        ]);

        $this->info('Random user created: ' . $user->name);
    }

    private function generateRandomEmail()
    {
        return Str::random(8) . '@' . Str::random(5) . '.com';
    }

    private function generateRandomPhoneNumber()
    {
        return '+' . rand(1, 99) . rand(1000000000, 9999999999);
    }
}