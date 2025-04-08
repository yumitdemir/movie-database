<?php

namespace App\Http\Controllers;

use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    protected $mediaService;
    
    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
        $this->middleware('auth');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'media' => 'required|file|mimes:jpeg,png,jpg,gif|max:2048',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        $this->mediaService->addMedia(
            $validated['movie_id'],
            $request->file('media'),
            [
                'title' => $validated['title'] ?? null,
                'description' => $validated['description'] ?? null
            ]
        );
        
        return redirect()->back()->with('success', 'Media added successfully!');
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string'
        ]);
        
        $this->mediaService->updateMedia($id, $validated);
        
        return redirect()->back()->with('success', 'Media information updated successfully!');
    }
    
    public function destroy($id)
    {
        $this->mediaService->deleteMedia($id);
        
        return redirect()->back()->with('success', 'Media deleted successfully!');
    }
} 