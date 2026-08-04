<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'booking_reference', 'customer_phone', 'customer_name', 'customer_email',
        'payment_method', 'payment_status', 'thawani_session_id',
        'total_amount', 'status',
    ];

    public function slots(): HasMany
    {
        return $this->hasMany(BookingSlot::class);
    }
}
