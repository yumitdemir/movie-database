<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RatingApiController extends Controller
{
    protected $ratingService;
    
    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
        $this->middleware('auth:sanctum');
    }
    
    /**
     * Store a new rating
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'value' => 'required|integer|min:1|max:10'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $rating = $this->ratingService->rateMovie(
                auth()->id(),
                $request->input('movie_id'),
                $request->input('value')
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Rating submitted successfully',
                'data' => $rating
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update an existing rating
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'value' => 'required|integer|min:1|max:10'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $rating = $this->ratingService->getById($id);
            
            // Check if user owns the rating
            if ($rating->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized action'
                ], 403);
            }
            
            $updatedRating = $this->ratingService->rateMovie(
                auth()->id(),
                $rating->movie_id,
                $request->value
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Rating updated successfully',
                'data' => $updatedRating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user's rating for a movie
     */
    public function getUserRating($movieId)
    {
        try {
            $rating = $this->ratingService->getUserRating(auth()->id(), $movieId);
            
            if (!$rating) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'No rating found',
                    'data' => null
                ]);
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $rating
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get user rating',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 