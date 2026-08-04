<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // كل صف = ساعة واحدة في تاريخ محدد على ملعب تم تخصيصه عشوائياً عند التأكيد
        Schema::create('booking_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('court_id')->constrained();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('price', 8, 3);
            $table->timestamps();

            // يمنع حجز نفس الملعب في نفس التاريخ والوقت مرتين (حماية على مستوى قاعدة البيانات)
            $table->unique(['court_id', 'date', 'start_time']);
            $table->index(['date', 'start_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_slots');
    }
};
