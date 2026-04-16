<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TableLayout extends Model
{
    protected $fillable = ['branch_id', 'name', 'data'];

    protected $casts = [
        'data' => 'array', // automatically converts JSON
    ];
}
