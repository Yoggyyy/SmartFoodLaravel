<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Allergen;

class AllergenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allergens = [
            ['name_allergen' => 'Gluten'],
            ['name_allergen' => 'Lactosa'],
            ['name_allergen' => 'Frutos secos'],
            ['name_allergen' => 'Huevos'],
            ['name_allergen' => 'Pescado'],
            ['name_allergen' => 'Mariscos'],
            ['name_allergen' => 'Soja'],
            ['name_allergen' => 'Sesamo'],
            ['name_allergen' => 'Sulfitos'],
            ['name_allergen' => 'Apio'],
            ['name_allergen' => 'Mostaza'],
            ['name_allergen' => 'Altramuces'],
            ['name_allergen' => 'Moluscos'],
            ['name_allergen' => 'Cacahuetes'],
        ];

        foreach ($allergens as $allergen) {
            Allergen::create($allergen);
        }
    }
}
