<?php

namespace App\Repositories;

use App\Models\Rating;

class RatingRepository extends BaseRepository
{
    public function __construct(Rating $model)
    {
        parent::__construct($model);
    }
    
    public function getUserRating($userId, $movieId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('movie_id', $movieId)
            ->first();
    }
    
    public function rateMovie($userId, $movieId, $value)
    {
        return $this->model->updateOrCreate(
            ['user_id' => $userId, 'movie_id' => $movieId],
            ['value' => $value]
        );
    }
    
    public function getUserRatings($userId, $perPage = 15)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('movie')
            ->paginate($perPage);
    }
} 