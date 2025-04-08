<?php

namespace App\Repositories;

use App\Models\Comment;

class CommentRepository extends BaseRepository
{
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }
    
    public function getCommentsByMovie($movieId, $perPage = 15)
    {
        return $this->model
            ->where('movie_id', $movieId)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }
    
    public function getCommentsByUser($userId, $perPage = 15)
    {
        return $this->model
            ->where('user_id', $userId)
            ->with('movie')
            ->latest()
            ->paginate($perPage);
    }
} 