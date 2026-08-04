<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // عروض السعر حسب عدد الساعات المحجوزة في نفس اليوم
        // مثال: ساعة واحدة = 10 ريال للساعة | ساعتان فأكثر = 8 ريال لكل ساعة
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('min_hours');   // الحد الأدنى لعدد الساعات لتفعيل هذا السعر
            $table->decimal('price_per_hour', 8, 3);    // السعر لكل ساعة (ريال عماني)
            $table->timestamps();

            $table->unique('min_hours');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
