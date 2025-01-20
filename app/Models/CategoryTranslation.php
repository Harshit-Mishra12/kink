<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryTranslation extends Model
{
    protected $fillable = ['language', 'name', 'title', 'short_description', 'content'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}

