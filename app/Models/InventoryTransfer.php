<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransfer extends Model
{
    use HasFactory;

    protected $table = 'inventory_transfers';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'requested_datetime',
        'requested_by',
        'reference_no',
        'transfer_type',
        'source_id',
        'destination_id',
        'attached_file',
        'status',
        'approved_by',
        'approved_datetime',
        'disapproved_by',
        'disapproved_datetime',
        'in_transit_by',
        'in_transit_datetime',
        'completed_by',
        'completed_datetime',
        'archived_by',
        'archived_datetime',
    ];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [
        'requested_datetime' => 'datetime',
    ];

    protected $dates = [
    'in_transit_datetime',
    'completed_datetime',
    'disapproved_datetime',
    'archived_datetime',
];

    /**
     * Relationship: Source Branch
     */
    public function sourceBranch()
    {
        return $this->belongsTo(Branch::class, 'source_id');
    }

    /**
     * Relationship: Destination Branch
     */
    public function destinationBranch()
    {
        return $this->belongsTo(Branch::class, 'destination_id');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by transfer type
     */
    public function scopeTransferType($query, $type)
    {
        return $query->where('transfer_type', $type);
    }
    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function items()
    {
        return $this->hasMany(InventoryTransferItem::class, 'inventory_transfer_id');
    }

    /**
     * Check if the transfer can be approved for a given branch
     */
    public function canApproveForBranch(int $branchId): bool
    {
        $ref = $this->reference_no;

        // TSO → cannot approve if same as SOURCE
        if (str_starts_with($ref, 'TSO-') && $this->source_id == $branchId) {
            return false;
        }

        // TR → cannot approve if same as DESTINATION
        if (str_starts_with($ref, 'TR-') && $this->destination_id == $branchId) {
            return false;
        }

        return true; // allowed
    }

    public function approvedBy() {
    return $this->belongsTo(User::class, 'approved_by');
}

public function inTransitBy() {
    return $this->belongsTo(User::class, 'in_transit_by');
}

public function completedBy() {
    return $this->belongsTo(User::class, 'completed_by');
}

public function disapprovedBy() {
    return $this->belongsTo(User::class, 'disapproved_by');
}

public function archivedBy() {
    return $this->belongsTo(User::class, 'archived_by');
}

public function sendOuts()
{
    return $this->hasMany(InventoryTransferSendOut::class, 'inventory_transfer_id');
}

}
