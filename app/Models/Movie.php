<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'title', 'description', 'release_date', 'runtime_minutes',
        'language', 'poster', 'trailer_url', 'budget', 'revenue'
    ];
    
    protected $casts = [
        'release_date' => 'date',
        'budget' => 'decimal:2',
        'revenue' => 'decimal:2',
    ];
    
    public function genres()
    {
        return $this->belongsToMany(Genre::class);
    }
    
    public function artists()
    {
        return $this->belongsToMany(Artist::class)
            ->withPivot('role')
            ->withTimestamps();
    }
    
    public function directors()
    {
        return $this->belongsToMany(Director::class);
    }
    
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
    
    public function ratings()
    {
        return $this->hasMany(Rating::class);
    }
    
    public function media()
    {
        return $this->hasMany(Media::class);
    }
    
    public function getAverageRatingAttribute()
    {
        return $this->ratings()->avg('value') ?: 0;
    }
    
    public function getRatingCountAttribute()
    {
        return $this->ratings()->count();
    }
} 