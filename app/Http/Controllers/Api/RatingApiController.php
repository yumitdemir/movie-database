<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingApiController extends Controller
{
    protected $ratingService;
    
    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'user_id' => 'required|exists:users,id',
            'value' => 'required|integer|min:1|max:10'
        ]);
        
        $rating = $this->ratingService->rateMovie(
            $validated['user_id'],
            $validated['movie_id'],
            $validated['value']
        );
        
        return response()->json($rating, 201);
    }
    
    public function show(Request $request)
    {
        $userId = $request->get('user_id');
        $movieId = $request->get('movie_id');
        
        if ($userId && $movieId) {
            $rating = $this->ratingService->getUserRating($userId, $movieId);
            return response()->json($rating);
        }
        
        return response()->json(['error' => 'Both user_id and movie_id parameters are required'], 400);
    }
} 