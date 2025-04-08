<?php

namespace App\Http\Controllers;

use App\Services\RatingService;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    protected $ratingService;
    
    public function __construct(RatingService $ratingService)
    {
        $this->ratingService = $ratingService;
        $this->middleware('auth');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'value' => 'required|integer|min:1|max:10'
        ]);
        
        $this->ratingService->rateMovie(
            auth()->id(),
            $validated['movie_id'],
            $validated['value']
        );
        
        return redirect()->back()->with('success', 'Rating submitted successfully!');
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'value' => 'required|integer|min:1|max:10'
        ]);
        
        $rating = $this->ratingService->getById($id);
        
        // Check if user owns the rating
        if ($rating->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->ratingService->rateMovie(
            auth()->id(),
            $rating->movie_id,
            $validated['value']
        );
        
        return redirect()->back()->with('success', 'Rating updated successfully!');
    }
} 