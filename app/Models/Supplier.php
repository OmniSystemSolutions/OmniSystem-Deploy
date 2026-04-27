<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_name',
        'contact_person',
        'mobile_no',
        'landline_no',
        'email',
        'supplier_since',
        'tin',
        'supplier_type',
        'address',
        'status',
    ];
}