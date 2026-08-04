<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique(); // رقم مرجعي يظهر للعميل
            $table->string('customer_phone');               // إجباري
            $table->string('customer_name')->nullable();    // اختياري
            $table->string('customer_email')->nullable();   // اختياري

            $table->enum('payment_method', ['pay_on_arrival', 'thawani']);
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->string('thawani_session_id')->nullable();

            $table->decimal('total_amount', 8, 3);
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed');

            $table->timestamps();

            $table->index('customer_phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
