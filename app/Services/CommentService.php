<?php

namespace App\Services;

use App\Repositories\CommentRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class CommentService
{
    protected $commentRepository;
    
    public function __construct(CommentRepository $commentRepository)
    {
        $this->commentRepository = $commentRepository;
    }
    
    public function getMovieComments($movieId, $perPage = 15)
    {
        return $this->commentRepository->getCommentsByMovie($movieId, $perPage);
    }
    
    public function getUserComments($userId, $perPage = 15)
    {
        return $this->commentRepository->getCommentsByUser($userId, $perPage);
    }
    
    public function getById($id, $columns = ['*'], $relations = [])
    {
        return $this->commentRepository->getById($id, $columns, $relations);
    }
    
    public function getPaginated($perPage = 15, $columns = ['*'], $relations = [])
    {
        return $this->commentRepository->getPaginated($perPage, $columns, $relations);
    }
    
    public function createComment($userId, $movieId, $content)
    {
        $callback = function () use ($userId, $movieId, $content) {
            return $this->commentRepository->create([
                'movie_id' => $movieId,
                'user_id' => $userId,
                'content' => $content
            ]);
        };
        
        return $this->runInTransaction($callback);
    }
    
    public function updateComment($id, $content)
    {
        $callback = function () use ($id, $content) {
            return $this->commentRepository->update($id, [
                'content' => $content
            ]);
        };
        
        return $this->runInTransaction($callback);
    }
    
    public function deleteComment($id)
    {
        $callback = function () use ($id) {
            return $this->commentRepository->delete($id);
        };
        
        return $this->runInTransaction($callback);
    }
    
    /**
     * Run a callback in a transaction, or directly if we're in a test environment
     *
     * @param callable $callback
     * @return mixed
     */
    protected function runInTransaction(callable $callback)
    {
        // In test environment, run the callback directly without transaction handling
        if (App::environment('testing')) {
            return $callback();
        }
        
        // Otherwise, wrap in a transaction
        return DB::transaction($callback);
    }
} 