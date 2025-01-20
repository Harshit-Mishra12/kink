<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionTranslation extends Model
{
    use HasFactory;

    // The table associated with the model
    protected $table = 'question_translations';

    // Specify the fields that can be mass-assigned
    protected $fillable = [
        'question_id',
        'language',
        'text',
        'hint',
    ];

    // The relationship between QuestionTranslation and Question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
}
