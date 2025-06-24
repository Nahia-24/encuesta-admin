<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TicketFeatures;

class TicketFeaturesSeeder extends Seeder
{
    public function run()
    {
        $features = [
            [
                'name' => 'Refrigerio',
                'consumable' => true,
                'featured' => false,
            ],
            [
                'name' => 'Silla numerada',
                'consumable' => false,
                'featured' => false,
            ],
            [
                'name' => 'Acceso a Meet & Greet',
                'consumable' => false,
                'featured' => true,
            ],
            [
                'name' => 'Bebida gratis',
                'consumable' => true,
                'featured' => true,
            ],
        ];

        foreach ($features as $feature) {
            TicketFeatures::create($feature);
        }
    }
}
