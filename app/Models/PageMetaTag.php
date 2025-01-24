<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageMetaTag extends Model
{
    protected $fillable = ['page_id', 'language', 'title', 'description', 'meta_keywords'];

    protected $casts = [
        'meta_keywords' => 'array', // Automatically casts JSON to/from array
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }
}
