<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtWorkingHour extends Model
{
    protected $fillable = ['court_id', 'day_of_week', 'opens_at', 'closes_at', 'is_closed'];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}
