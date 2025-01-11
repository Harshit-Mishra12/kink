<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $table = 'contact_us'; // Specify the table name if it doesn't follow naming conventions

    // Define the fillable fields to protect against mass assignment
    protected $fillable = [
        'content',
        'language',
    ];
}
