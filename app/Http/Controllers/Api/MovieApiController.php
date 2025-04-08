<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovieService;
use App\Services\CommentService;
use App\Services\RatingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MovieApiController extends Controller
{
    protected $movieService;
    protected $commentService;
    protected $ratingService;
    
    public function __construct(
        MovieService $movieService,
        CommentService $commentService,
        RatingService $ratingService
    ) {
        $this->movieService = $movieService;
        $this->commentService = $commentService;
        $this->ratingService = $ratingService;
    }
    
    /**
     * Get a list of all movies
     */
    public function index()
    {
        $movies = $this->movieService->getAllMovies();
        return response()->json([
            'status' => 'success',
            'data' => $movies
        ]);
    }
    
    /**
     * Get a specific movie by ID
     */
    public function show($id)
    {
        try {
            $movie = $this->movieService->getMovieById($id);
            return response()->json([
                'status' => 'success',
                'data' => $movie
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Movie not found'
            ], 404);
        }
    }
    
    /**
     * Create a new movie
     */
    public function store(Request $request)
    {
        // Convert comma-separated strings to arrays
        $data = $request->all();
        
        // Process comma separated inputs into arrays
        foreach (['genres', 'directors', 'artists'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = array_map('trim', explode(',', $data[$field]));
            }
        }
        
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'release_date' => 'required|date',
            'runtime_minutes' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:50',
            'poster' => 'nullable|image|max:2048',
            'trailer_url' => 'nullable|url|max:255',
            'budget' => 'nullable|numeric|min:0',
            'revenue' => 'nullable|numeric|min:0',
            'genres' => 'nullable|array',
            'directors' => 'nullable|array',
            'artists' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $movie = $this->movieService->createMovie($validator->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Movie created successfully',
                'data' => $movie
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create movie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update an existing movie
     */
    public function update(Request $request, $id)
    {
        // Convert comma-separated strings to arrays
        $data = $request->all();
        
        // Process comma separated inputs into arrays
        foreach (['genres', 'directors', 'artists'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = array_map('trim', explode(',', $data[$field]));
            }
        }
        
        $validator = Validator::make($data, [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'release_date' => 'sometimes|required|date',
            'runtime_minutes' => 'nullable|integer|min:1',
            'language' => 'nullable|string|max:50',
            'poster' => 'nullable|image|max:2048',
            'trailer_url' => 'nullable|url|max:255',
            'budget' => 'nullable|numeric|min:0',
            'revenue' => 'nullable|numeric|min:0',
            'genres' => 'nullable|array',
            'directors' => 'nullable|array',
            'artists' => 'nullable|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $movie = $this->movieService->updateMovie($id, $validator->validated());
            return response()->json([
                'status' => 'success',
                'message' => 'Movie updated successfully',
                'data' => $movie
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update movie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a movie
     */
    public function destroy($id)
    {
        try {
            $this->movieService->deleteMovie($id);
            return response()->json([
                'status' => 'success',
                'message' => 'Movie deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete movie',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Search movies
     */
    public function search(Request $request)
    {
        $term = $request->get('q');
        $movies = $this->movieService->searchMovies($term);
        
        return response()->json([
            'status' => 'success',
            'data' => $movies
        ]);
    }
    
    /**
     * Get movie statistics
     */
    public function statistics($id)
    {
        try {
            $movie = $this->movieService->getMovieById($id);
            $statistics = $this->movieService->getMovieStatistics($id);
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'movie' => $movie,
                    'statistics' => $statistics
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get movie statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get movie comments
     */
    public function comments($id)
    {
        try {
            $comments = $this->commentService->getMovieComments($id);
            
            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get movie comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 