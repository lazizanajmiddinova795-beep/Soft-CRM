<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function pauses()
    {
        return $this->hasMany(ShiftPause::class);
    }

    public function currentPause()
    {
        return $this->pauses()->whereNull('resumed_at')->first();
    }
}
