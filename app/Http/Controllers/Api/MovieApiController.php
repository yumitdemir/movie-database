<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MovieService;
use Illuminate\Http\Request;

class MovieApiController extends Controller
{
    protected $movieService;
    
    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
    }
    
    public function index()
    {
        $movies = $this->movieService->getAllMovies();
        return response()->json($movies);
    }
    
    public function show($id)
    {
        $movie = $this->movieService->getMovieById($id);
        return response()->json($movie);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
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
        
        $movie = $this->movieService->createMovie($validated);
        
        return response()->json($movie, 201);
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
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
        
        $movie = $this->movieService->updateMovie($id, $validated);
        
        return response()->json($movie);
    }
    
    public function destroy($id)
    {
        $this->movieService->deleteMovie($id);
        
        return response()->json(null, 204);
    }
    
    public function search(Request $request)
    {
        $term = $request->get('q');
        $movies = $this->movieService->searchMovies($term);
        
        return response()->json($movies);
    }
    
    public function statistics($id)
    {
        $statistics = $this->movieService->getMovieStatistics($id);
        
        return response()->json($statistics);
    }
} 