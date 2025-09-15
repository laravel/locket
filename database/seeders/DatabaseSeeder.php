<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DemoSeeder::class,
        ]);

        // Create a test user for easy access
        User::factory()->create([
            'name' => 'test_user',
            'email' => 'test@example.com',
        ]);
    }
}
