<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;
    
    protected $fillable = ['movie_id', 'file_path', 'file_type', 'title', 'description'];
    
    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
} 