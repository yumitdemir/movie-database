<?php

namespace App\Services;

use App\Repositories\RatingRepository;

class RatingService
{
    protected $ratingRepository;
    
    public function __construct(RatingRepository $ratingRepository)
    {
        $this->ratingRepository = $ratingRepository;
    }
    
    public function getUserRating($userId, $movieId)
    {
        return $this->ratingRepository->getUserRating($userId, $movieId);
    }
    
    public function getById($id)
    {
        return $this->ratingRepository->getById($id);
    }
    
    public function rateMovie($userId, $movieId, $value)
    {
        // Validate rating is between 1-10
        $value = max(1, min(10, $value));
        
        return $this->ratingRepository->rateMovie($userId, $movieId, $value);
    }
    
    public function getUserRatings($userId, $perPage = 15)
    {
        return $this->ratingRepository->getUserRatings($userId, $perPage);
    }
} 