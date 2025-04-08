<?php

namespace App\Services;

use App\Repositories\RatingRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

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
        $callback = function () use ($userId, $movieId, $value) {
            // Validate rating is between 1-10
            $value = max(1, min(10, $value));
            
            return $this->ratingRepository->rateMovie($userId, $movieId, $value);
        };
        
        return $this->runInTransaction($callback);
    }
    
    public function getUserRatings($userId, $perPage = 15)
    {
        return $this->ratingRepository->getUserRatings($userId, $perPage);
    }
    
    public function getMovieAverageRating($movieId)
    {
        return $this->ratingRepository->getMovieAverageRating($movieId);
    }
    
    public function getMovieRatingCount($movieId)
    {
        return $this->ratingRepository->getMovieRatingCount($movieId);
    }
    
    /**
     * Run a callback in a transaction, or directly if we're in a test environment
     *
     * @param callable $callback
     * @return mixed
     */
    protected function runInTransaction(callable $callback)
    {
        // In test environment, run the callback directly without transaction handling
        if (App::environment('testing')) {
            return $callback();
        }
        
        // Otherwise, wrap in a transaction
        return DB::transaction($callback);
    }
} 