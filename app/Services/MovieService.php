<?php

namespace App\Services;

use App\Repositories\MovieRepository;
use Illuminate\Support\Facades\Storage;

class MovieService
{
    protected $movieRepository;
    
    public function __construct(MovieRepository $movieRepository)
    {
        $this->movieRepository = $movieRepository;
    }
    
    public function getAllMovies($perPage = 15)
    {
        return $this->movieRepository->getAllWithRelations($perPage);
    }
    
    public function getMovieById($id)
    {
        return $this->movieRepository->getByIdWithRelations($id);
    }
    
    public function createMovie(array $data)
    {
        $attributes = $this->prepareMovieData($data);
        $relations = $this->prepareRelationsData($data);
        
        return $this->movieRepository->createWithRelations($attributes, $relations);
    }
    
    public function updateMovie($id, array $data)
    {
        $attributes = $this->prepareMovieData($data);
        $relations = $this->prepareRelationsData($data);
        
        return $this->movieRepository->updateWithRelations($id, $attributes, $relations);
    }
    
    public function deleteMovie($id)
    {
        $movie = $this->movieRepository->getById($id);
        
        // Delete poster file if exists
        if ($movie->poster) {
            Storage::delete('public/' . $movie->poster);
        }
        
        // Delete related media files
        foreach ($movie->media as $media) {
            Storage::delete('public/' . $media->file_path);
        }
        
        return $this->movieRepository->delete($id);
    }
    
    public function searchMovies($term, $perPage = 15)
    {
        return $this->movieRepository->searchMovies($term, $perPage);
    }
    
    public function getMovieStatistics($id)
    {
        return $this->movieRepository->getRatingStatistics($id);
    }
    
    protected function prepareMovieData(array $data)
    {
        $attributes = [
            'title' => $data['title'],
            'description' => $data['description'],
            'release_date' => $data['release_date'],
            'runtime_minutes' => $data['runtime_minutes'] ?? null,
            'language' => $data['language'] ?? null,
            'trailer_url' => $data['trailer_url'] ?? null,
            'budget' => $data['budget'] ?? null,
            'revenue' => $data['revenue'] ?? null,
        ];
        
        // Handle poster upload
        if (isset($data['poster']) && $data['poster']->isValid()) {
            $posterPath = $data['poster']->store('posters', 'public');
            $attributes['poster'] = $posterPath;
        }
        
        return $attributes;
    }
    
    protected function prepareRelationsData(array $data)
    {
        $relations = [];
        
        if (isset($data['genres'])) {
            $relations['genres'] = $data['genres'];
        }
        
        if (isset($data['directors'])) {
            $relations['directors'] = $data['directors'];
        }
        
        if (isset($data['artists'])) {
            $relations['artists'] = $data['artists'];
        }
        
        return $relations;
    }
} 