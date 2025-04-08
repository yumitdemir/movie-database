<?php

namespace App\Services;

use App\Repositories\CommentRepository;

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
    
    public function createComment(array $data)
    {
        return $this->commentRepository->create([
            'movie_id' => $data['movie_id'],
            'user_id' => $data['user_id'],
            'content' => $data['content']
        ]);
    }
    
    public function updateComment($id, array $data)
    {
        return $this->commentRepository->update($id, [
            'content' => $data['content']
        ]);
    }
    
    public function deleteComment($id)
    {
        return $this->commentRepository->delete($id);
    }
} 