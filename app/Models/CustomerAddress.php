<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'country',
        'city',
        'address_line',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
