<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtInstallment extends Model
{
    protected $guarded = [];

    public function debt()
    {
        return $this->belongsTo(Debt::class);
    }
}
