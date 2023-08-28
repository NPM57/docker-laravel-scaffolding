<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use \App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $limit = 30;

        for ($i = 0; $i < $limit; $i++) {
            Company::factory()
                ->create([
                    'logo' => fake()->image('public/storage', 100, 100, null, false)
                ]);
        }
    }
}
