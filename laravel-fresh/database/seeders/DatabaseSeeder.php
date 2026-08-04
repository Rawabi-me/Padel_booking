<?php

namespace Database\Seeders;

use App\Models\Court;
use App\Models\CourtWorkingHour;
use App\Models\PricingTier;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ============ حساب الإدارة الافتراضي ============
        User::firstOrCreate(
            ['email' => 'admin@padel.local'],
            ['name' => 'Admin', 'password' => bcrypt('Admin@1234')]
        );

        // ============ عروض الأسعار الافتراضية ============
        PricingTier::firstOrCreate(['min_hours' => 1], ['price_per_hour' => 10.000]);
        PricingTier::firstOrCreate(['min_hours' => 2], ['price_per_hour' => 8.000]);

        // ============ ملاعب تجريبية ============
        foreach (['ملعب A', 'ملعب B', 'ملعب C'] as $name) {
            $court = Court::firstOrCreate(['name' => $name], ['is_active' => true]);

            if ($court->workingHours()->count() === 0) {
                foreach (range(0, 6) as $day) {
                    CourtWorkingHour::create([
                        'court_id' => $court->id,
                        'day_of_week' => $day,
                        'opens_at' => '09:00',
                        'closes_at' => '23:00',
                        'is_closed' => false,
                    ]);
                }
            }
        }
    }
}
