<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    protected $fillable = [
        'name',
        'account_number',
        'branch',
        'refer_bank_id',
        'organization_id',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}
