<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourtClosure extends Model
{
    protected $fillable = ['court_id', 'start_date', 'end_date', 'reason'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }
}
