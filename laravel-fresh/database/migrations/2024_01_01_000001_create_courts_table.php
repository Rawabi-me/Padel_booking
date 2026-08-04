<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courts', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // اسم الملعب (يظهر للإدارة فقط، لا يظهر للعميل)
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true); // تعطيل الملعب بشكل عام
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courts');
    }
};
