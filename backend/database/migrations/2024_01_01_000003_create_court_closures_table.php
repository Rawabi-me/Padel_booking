<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إغلاق ملعب واحد أو عدة ملاعب أو جميع الملاعب في تاريخ/فترة محددة
        Schema::create('court_closures', function (Blueprint $table) {
            $table->id();
            // court_id = null  =>  الإغلاق يشمل جميع الملاعب
            $table->foreignId('court_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_closures');
    }
};
