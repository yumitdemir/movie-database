<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\Request;

class CommentApiController extends Controller
{
    protected $commentService;
    
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }
    
    public function index(Request $request)
    {
        $movieId = $request->get('movie_id');
        
        if ($movieId) {
            $comments = $this->commentService->getMovieComments($movieId);
        } else {
            $comments = $this->commentService->getPaginated();
        }
        
        return response()->json($comments);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'movie_id' => 'required|exists:movies,id',
            'content' => 'required|string',
            'user_id' => 'required|exists:users,id'
        ]);
        
        $comment = $this->commentService->createComment($validated);
        
        return response()->json($comment, 201);
    }
    
    public function show($id)
    {
        $comment = $this->commentService->getById($id, ['*'], ['user', 'movie']);
        
        return response()->json($comment);
    }
    
    public function update(Request $request, $id)
    {
        $comment = $this->commentService->getById($id);
        
        $validated = $request->validate([
            'content' => 'required|string'
        ]);
        
        $updated = $this->commentService->updateComment($id, $validated);
        
        return response()->json($updated);
    }
    
    public function destroy($id)
    {
        $this->commentService->deleteComment($id);
        
        return response()->json(null, 204);
    }
} 