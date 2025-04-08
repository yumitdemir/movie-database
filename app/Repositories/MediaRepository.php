<?php

namespace App\Repositories;

use App\Models\Media;

class MediaRepository extends BaseRepository
{
    public function __construct(Media $model)
    {
        parent::__construct($model);
    }
    
    public function getMediaByMovie($movieId)
    {
        return $this->model
            ->where('movie_id', $movieId)
            ->get();
    }
} 