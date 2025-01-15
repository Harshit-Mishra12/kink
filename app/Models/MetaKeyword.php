<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaKeyword extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relationship with meta tags.
     */
    public function metaTags()
    {
        return $this->belongsToMany(MetaTag::class, 'meta_tag_meta_keyword');
    }
}
