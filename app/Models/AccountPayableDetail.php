<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountPayableDetail extends Model
{
    protected $fillable = [
        'chart_account_id',
        'account_payable_id',
        'accounting_category_id',
        'payment_id',
        'cash_equivalent_id',
        'description',
        'quantity',
        'tax_id',
        'amount_per_unit',
        'total_amount',
        'amount_to_pay', // add here
    ];

    public function payable()
    {
        return $this->belongsTo(AccountPayable::class, 'account_payable_id');
    }

    public function category()
    {
        return $this->belongsTo(AccountingCategory::class, 'accounting_category_id');
    }

    public function chartAccount()
    {
        return $this->belongsTo(\App\Models\ChartAccount::class);
    }
}
