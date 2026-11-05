<?php

namespace Database\Seeders;

use App\Constants\Constants;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => config('services.app.email'),
                'password' => 'admin123',
                'role' => Constants::SUPERADMIN,
            ],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@gmail.com',
                'password' => 'admin123',
                'role' => Constants::ADMIN,
            ],
            [
                'first_name' => 'Champs',
                'last_name' => 'Manager',
                'email' => 'champs.management@gmail.com',
                'password' => 'admin123',
                'role' => Constants::CHAMPS_MANAGEMENT,
            ],
            [
                'first_name' => 'Security',
                'last_name' => 'Operative',
                'email' => 'security@gmail.com',
                'password' => 'admin123',
                'role' => Constants::SECURITY_OPERATIVE,
            ],
            [
                'first_name' => 'Field',
                'last_name' => 'Worker',
                'email' => 'field.worker@gmail.com',
                'password' => 'admin123',
                'role' => Constants::FIELD_WORKER,
            ],
            [
                'first_name' => 'Client',
                'last_name' => 'Management',
                'email' => 'client.management@gmail.com',
                'password' => 'admin123',
                'role' => Constants::CLIENT_MANAGEMENT,
            ],
            [
                'first_name' => 'Client',
                'last_name' => 'Director',
                'email' => 'client.director@gmail.com',
                'password' => 'admin123',
                'role' => Constants::CLIENT_DIRECTORS,
            ],
            [
                'first_name' => 'Normal',
                'last_name' => 'User',
                'email' => 'user@gmail.com',
                'password' => 'test123',
                'role' => Constants::USER,
            ],
        ];

        foreach ($users as $u) {

            $user = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'first_name' => $u['first_name'],
                    'last_name' => $u['last_name'],
                    'phone_number' => '+1 (363) 654-8569',
                    'password' => Hash::make($u['password']),
                    'email_verified_at' => now(),
                ]
            );

            $user->assignRole($u['role']);

            $user->file()->create([
                'name' => 'avatar.png',
                'path' => 'users/avatar.png',
                'type' => 'profile'
            ]);
        }
    }
}
