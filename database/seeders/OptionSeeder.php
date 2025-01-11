<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Option;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create the options with respective percentage values
        Option::create([
            'option_label' => 'Strongly Disagree',
            'percentage' => 0,
        ]);

        Option::create([
            'option_label' => 'Disagree',
            'percentage' => 25,
        ]);

        Option::create([
            'option_label' => 'Neutral',
            'percentage' => 50,
        ]);

        Option::create([
            'option_label' => 'Agree',
            'percentage' => 75,
        ]);

        Option::create([
            'option_label' => 'Strongly Agree',
            'percentage' => 100,
        ]);
    }
}
