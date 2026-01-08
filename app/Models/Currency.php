<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'symbol',
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
