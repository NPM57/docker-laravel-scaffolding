<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         \App\Models\User::factory()->create([
             'name' => 'Admin',
             'email' => 'admin@admin.com',
             'password' => 'password',
         ]);

         \App\Models\User::factory(10)->create();

        $this->call([
            CompanySeeder::class,
            EmployeeSeeder::class,
        ]);
    }
}
