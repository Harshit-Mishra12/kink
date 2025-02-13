<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomePage extends Model
{
    use HasFactory;

    protected $table = 'home_pages'; // Table name

    protected $fillable = [
        'content', // JSON-stored heading and content
        'language',
    ];

    // Accessor to automatically decode JSON when retrieving content
    public function getContentAttribute($value)
    {
        return json_decode($value, true);
    }

    // Mutator to automatically encode JSON when saving content
    public function setContentAttribute($value)
    {
        $this->attributes['content'] = json_encode($value);
    }
}
