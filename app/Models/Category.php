<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'categories';

    // Specify which attributes should be mass assignable
    protected $fillable = [
        'name',
        'title',
        'image',
        'short_description',
        'content',
    ];

    // Optionally, you can define casts for attributes if needed
    protected $casts = [
        'content' => 'string',  // Ensures 'content' is cast to a string (you could use 'html' as an option if needed)
    ];
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'question_categories');
    }

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }
}
