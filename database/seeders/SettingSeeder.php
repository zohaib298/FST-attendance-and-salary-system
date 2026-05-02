<?php

namespace Database\Seeders;

use App\Models\setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         Setting::updateOrCreate(
            ['key' => 'late_rule'],
            ['value' => 3]
        );

        setting::updateOrCreate(
            ['key' => 'overtime_rate'],
            ['value' => 500]
        );

        Setting::updateOrCreate(
            ['key' => 'night_bonus'],
            ['value' => 1000]
        );

        Setting::updateOrCreate(
            ['key' => 'absent_penalty'],
            ['value' => 1000]
        );
    }
}
