<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'ITCloud Admin',
            'email' => 'admin@itcloud.uz',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
        User::factory()->create([
            'name' => 'Operator 1',
            'email' => 'operator1@itcloud.uz',
            'password' => bcrypt('password'),
            'role' => 'operator',
        ]);
        User::factory()->create([
            'name' => 'Cashier 1',
            'email' => 'cashier1@itcloud.uz',
            'password' => bcrypt('password'),
            'role' => 'cashier',
        ]);
    }
}
