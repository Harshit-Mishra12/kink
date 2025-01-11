<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;

    // Define the table name (optional if it's the same as the plural form of the model)
    protected $table = 'options';

    // Specify the fillable columns for mass assignment
    protected $fillable = [
        'option_label',   // The label for the option (e.g., "Strongly Agree")
        'percentage',     // The percentage value associated with the option (e.g., 0, 25, 50, 75, 100)
    ];

    // Optionally, you can specify the hidden columns for API responses
    protected $hidden = [
        'created_at',     // You can hide created_at if not needed
        'updated_at',     // You can hide updated_at if not needed
    ];

    // If you are using timestamps, you don't need to define this (Laravel handles it automatically)
    public $timestamps = true;


    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function responses()
{
    return $this->hasMany(Response::class, 'option_id');
}
}
