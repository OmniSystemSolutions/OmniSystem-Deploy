<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Testing\Fluent\Concerns\Has;

class Subcategory extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'category_id', 'description'];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function products() {
        return $this->hasMany(Product::class);
    }

    public function components() {
        return $this->hasMany(Component::class);
    }
}
