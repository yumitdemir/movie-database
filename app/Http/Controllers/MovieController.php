<?php

namespace App\Http\Controllers;

use App\Services\MovieService;
use App\Services\CommentService;
use App\Services\RatingService;
use App\Services\MediaService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class MovieController extends Controller
{
    protected $movieService;
    protected $commentService;
    protected $ratingService;
    protected $mediaService;
    
    public function __construct(
        MovieService $movieService,
        CommentService $commentService,
        RatingService $ratingService,
        MediaService $mediaService
    ) {
        $this->movieService = $movieService;
        $this->commentService = $commentService;
        $this->ratingService = $ratingService;
        $this->mediaService = $mediaService;
    }
    
    public function index()
    {
        $movies = $this->movieService->getAllMovies();
        return view('movies.index', compact('movies'));
    }
    
    public function create()
    {
        return view('movies.create');
    }
    
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
        
        $validated = Validator::make($data, [
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
        ])->validate();
        
        $movie = $this->movieService->createMovie($validated);
        
        return redirect()->route('movies.show', $movie->id)
            ->with('success', 'Movie created successfully!');
    }
    
    public function show($id)
    {
        $movie = $this->movieService->getMovieById($id);
        // Refresh the movie to get the latest rating data
        $movie->refresh();
        
        $comments = $this->commentService->getMovieComments($id);
        
        $userRating = null;
        if (auth()->check()) {
            $userRating = $this->ratingService->getUserRating(auth()->id(), $id);
        }
        
        return view('movies.show', compact('movie', 'comments', 'userRating'));
    }
    
    public function edit($id)
    {
        $movie = $this->movieService->getMovieById($id);
        return view('movies.edit', compact('movie'));
    }
    
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
        
        $validated = Validator::make($data, [
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
        ])->validate();
        
        $movie = $this->movieService->updateMovie($id, $validated);
        
        return redirect()->route('movies.show', $movie->id)
            ->with('success', 'Movie updated successfully!');
    }
    
    public function destroy($id)
    {
        $this->movieService->deleteMovie($id);
        return redirect()->route('movies.index')
            ->with('success', 'Movie deleted successfully!');
    }
    
    public function search(Request $request)
    {
        $term = $request->get('q');
        $movies = $this->movieService->searchMovies($term);
        
        return view('movies.index', compact('movies', 'term'));
    }
    
    public function statistics($id)
    {
        $movie = $this->movieService->getMovieById($id);
        $statistics = $this->movieService->getMovieStatistics($id);
        
        return view('movies.statistics', compact('movie', 'statistics'));
    }
} 