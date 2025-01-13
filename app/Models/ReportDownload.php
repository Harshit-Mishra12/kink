<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportDownload extends Model
{
    use HasFactory;

    // The table associated with the model.
    protected $table = 'reports_downloads';

    // The attributes that are mass assignable.
    protected $fillable = [
        'user_id',
        'file',
    ];

    // The attributes that should be cast to native types.
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Define the relationship with the User model (assuming the User model exists).
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
