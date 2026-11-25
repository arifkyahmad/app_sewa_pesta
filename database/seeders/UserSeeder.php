<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $akun = [
            ['name' => 'Admin',    'email' => 'admin@sewapesta.test',    'role' => 'admin'],
            ['name' => 'Petugas 1', 'email' => 'petugas1@sewapesta.test', 'role' => 'petugas'],
            ['name' => 'Petugas 2', 'email' => 'petugas2@sewapesta.test', 'role' => 'petugas'],
        ];

        foreach ($akun as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'role' => $data['role'],
                    'password' => Hash::make('password'),
                ]
            );
        }
    }
}