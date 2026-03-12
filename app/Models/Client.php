<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $guarded = [];

    public function contracts()
    {
        return $this->hasMany(Contract::class);
    }

    public function debts()
    {
        return $this->hasMany(Debt::class);
    }
}
