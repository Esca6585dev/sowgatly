<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create specific users with static tokens
        $this->createSpecificUsersWithTokens();

        // Create additional random users
        User::factory()->count(10)->create();
    }

    private function createSpecificUsersWithTokens()
    {
        $users = [
            [
                'phone_number' => '65656585',
                'email' => 'esca656585@gmail.com',
                'name' => 'Esen Meredow',
                'image' => 'user/user-seeder/logo.png',
                'token' => '1|MADVetcOYwHT7yYmWWQB9PLK6T1lQyvoBYI8Pqc559492981',
            ],
            [
                'phone_number' => '65406778',
                'email' => 'rahym@gmail.com',
                'name' => 'Rahymberdi Ahmedow',
                'image' => 'user/user-seeder/logo.png',
                'token' => '2|O7Gsib5I3OiOtWATDQATMtAasyMSqwDPE7B84ZwV88693cff',
            ],
            [
                'phone_number' => '71406778',
                'email' => 'rahman@gmail.com',
                'name' => 'Rahmanberdi Ahmedow',
                'image' => 'user/user-seeder/sowgatly-logo.png',
                'token' => '3|NXDWftdPZwIS8zZmXXRC0QMK7T2mRzvpCZJ9Rrd660603092',
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                [
                    'phone_number' => $userData['phone_number'],
                    'email' => $userData['email'],
                ],
                [
                    'name' => $userData['name'],
                    'image' => $userData['image'],
                    'password' => Hash::make('password'),
                    'status' => true,
                ]
            );

            // Delete existing tokens for this user
            $user->tokens()->delete();

            // Create a new token with the static value
            $token = explode('|', $userData['token']);
            PersonalAccessToken::create([
                'tokenable_id' => $user->id,
                'tokenable_type' => User::class,
                'name' => 'api-token',
                'token' => hash('sha256', $token[1]),
                'abilities' => ['*'],
            ]);

            $this->command->info("User created/updated: {$user->name} with token: {$userData['token']}");
        }
    }
}