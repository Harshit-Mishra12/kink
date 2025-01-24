<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['type', 'type_id'];

    public function metaTags()
    {
        return $this->hasMany(PageMetaTag::class);
    }
}
