<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentApiController extends Controller
{
    protected $commentService;
    
    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
        $this->middleware('auth:sanctum');
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
    
    /**
     * Store a new comment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'content' => 'required|string|max:1000'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $userId = auth()->id();
            $movieId = $request->input('movie_id');
            $content = $request->input('content');
            
            $comment = $this->commentService->createComment(
                $userId,
                $movieId,
                $content
            );
            
            return response()->json([
                'status' => 'success',
                'message' => 'Comment submitted successfully',
                'data' => $comment
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to submit comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function show($id)
    {
        $comment = $this->commentService->getById($id, ['*'], ['user', 'movie']);
        
        return response()->json($comment);
    }
    
    /**
     * Update an existing comment
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $comment = $this->commentService->getById($id);
            
            // Check if user owns the comment
            if ($comment->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized action'
                ], 403);
            }
            
            $content = $request->input('content');
            $updatedComment = $this->commentService->updateComment($id, $content);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Comment updated successfully',
                'data' => $updatedComment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a comment
     */
    public function destroy($id)
    {
        try {
            $comment = $this->commentService->getById($id);
            
            // Check if user owns the comment
            if ($comment->user_id !== auth()->id()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized action'
                ], 403);
            }
            
            $this->commentService->deleteComment($id);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Comment deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete comment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get comments for a movie
     */
    public function getMovieComments($movieId)
    {
        try {
            $comments = $this->commentService->getMovieComments($movieId);
            
            return response()->json([
                'status' => 'success',
                'data' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get comments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 