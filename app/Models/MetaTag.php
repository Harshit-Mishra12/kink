<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MetaTag extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'type_id', 'title', 'description'];

    /**
     * Relationship with meta keywords.
     */
    public function metaKeywords()
    {
        return $this->belongsToMany(MetaKeyword::class, 'meta_tag_meta_keyword');
    }
}
