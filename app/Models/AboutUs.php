<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    // Define the table
    protected $table = 'about_us';

    // Fillable columns to protect against mass-assignment
    protected $fillable = [ 'content', 'language'];

    // Optionally, you can add accessor or mutators for custom data handling
}
