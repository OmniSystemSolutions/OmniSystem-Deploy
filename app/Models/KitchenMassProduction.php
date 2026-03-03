<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KitchenMassProduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'product_id',
        'quantity',
        'status',
        'remarks',
        'created_by',
        'approved_by',
        'approved_datetime',
        'completed_by',
        'completed_datetime',
        'disapproved_by',
        'disapproved_datetime',
        'archived_by',
        'archived_datetime',
        'branch_id',
        'additional_items'
    ];

    protected $casts = [
        'approved_datetime' => 'datetime',
        'completed_datetime' => 'datetime',
        'disapproved_datetime' => 'datetime',
        'archived_datetime' => 'datetime',
        'additional_items' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    // Product reference
    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    // Who created this mass production
    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // Approved by user
    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    // Completed by user
    public function completedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'completed_by');
    }

    // Disapproved by user
    public function disapprovedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'disapproved_by');
    }

    // Archived by user
    public function archivedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'archived_by');
    }

    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }
}
