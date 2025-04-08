<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    protected $commentService;
    
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
        $this->middleware('auth');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'content' => 'required|string'
        ]);
        
        $userId = auth()->id();
        $movieId = $validated['movie_id'];
        $content = $validated['content'];
        
        $this->commentService->createComment($userId, $movieId, $content);
        
        return redirect()->back()->with('success', 'Comment added successfully!');
    }
    
    public function update(Request $request, $id)
    {
        $comment = $this->commentService->getById($id);
        
        // Check if user owns the comment
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'content' => 'required|string'
        ]);
        
        $this->commentService->updateComment($id, $validated['content']);
        
        return redirect()->back()->with('success', 'Comment updated successfully!');
    }
    
    public function destroy($id)
    {
        $comment = $this->commentService->getById($id);
        
        // Check if user owns the comment
        if ($comment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
        
        $this->commentService->deleteComment($id);
        
        return redirect()->back()->with('success', 'Comment deleted successfully!');
    }
} 