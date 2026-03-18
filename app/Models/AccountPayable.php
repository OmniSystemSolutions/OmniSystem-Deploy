<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayable extends Model
{
    protected $fillable = [
        'branch_id',
        'reference_number',
        'payor_details',
        'payer_name',
        'payer_company',
        'payer_address',
        'payer_mobile_number',
        'payer_email_address',
        'payer_tin',
        'due_date',
        'status',
        'paid_datetime'
    ];

    protected $casts = [
        'due_date' => 'date', // automatically converts to Carbon instance
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function details()
    {
        return $this->hasMany(AccountPayableDetail::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
