<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    // Table associated with the model
    protected $table = 'questions';

    // Define the columns that are mass assignable
    protected $fillable = [
        'question_text',
        'status',
    ];

    // Optionally, you can add timestamps if they are present in the table
    public $timestamps = true;

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'question_categories');
    }
}
