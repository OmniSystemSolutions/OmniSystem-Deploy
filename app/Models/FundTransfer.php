<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'created_by',
        'method_of_transfer_id',
        'from_cash_equivalent_id',
        'to_cash_equivalent_id',
        'amount',
        'description',
        'status',
        'attachments',
        'branch_id',
        'approved_by',
        'approved_datetime',
        'archived_by',
        'archived_dateime'
    ];
    
    protected $casts = [
    'attachments' => 'array',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function methodOfTransfer()
    {
        return $this->belongsTo(Payment::class, 'method_of_transfer_id');
    }

    public function fromCashEquivalent()
    {
        return $this->belongsTo(CashEquivalent::class, 'from_cash_equivalent_id');
    }

    public function toCashEquivalent()
    {
        return $this->belongsTo(CashEquivalent::class, 'to_cash_equivalent_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

     // Add relationships to users
    public function approvedByUser()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function archivedByUser()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }
}
