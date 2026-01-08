<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function organizations()
    {
        return $this->hasMany(Organization::class);
    }
}
