<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;
    protected $table = 'responses';

    // Mass assignable attributes (whitelist)
    protected $fillable = [
        'user_id',
        'question_id',
        'option_id',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A response belongs to a question
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    // A response belongs to an option
    public function option()
    {
        return $this->belongsTo(Option::class);
    }
}
