<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Component extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'brand_name',        // <-- added
        'cost',
        'price',
        'unit_id',
        'onhand',
        'status',
        'image',
        'for_sale',
        'category_id',
        'subcategory_id',
        'supplier_id',
    ];

    protected $casts = [
        'onhand' => 'string',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function branchStocks()
    {
        return $this->hasMany(BranchComponent::class);
    }

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function branchStockForCurrent()
    {
        return $this->hasOne(BranchComponent::class)
                    ->where('branch_id', current_branch_id());
    }

    public function onhandForCurrentBranch()
    {
        return $this->branchStockForCurrent()->value('onhand') ?? 0;
    }
}