<?php

namespace App\Repositories;

use App\Models\Movie;

class MovieRepository extends BaseRepository
{
    public function __construct(Movie $model)
    {
        parent::__construct($model);
    }
    
    public function getAllWithRelations($perPage = 15)
    {
        return $this->model
            ->with(['genres', 'directors', 'artists'])
            ->withCount('comments', 'ratings')
            ->withAvg('ratings as average_rating', 'value')
            ->paginate($perPage);
    }
    
    public function getByIdWithRelations($id)
    {
        return $this->model
            ->with(['genres', 'directors', 'artists', 'comments.user', 'media'])
            ->withCount('comments', 'ratings')
            ->withAvg('ratings as average_rating', 'value')
            ->findOrFail($id);
    }
    
    public function createWithRelations(array $attributes, array $relations = [])
    {
        $movie = $this->create($attributes);
        
        if (isset($relations['genres'])) {
            $genreIds = $this->syncGenresByName($relations['genres']);
            $movie->genres()->sync($genreIds);
        }
        
        if (isset($relations['directors'])) {
            $directorIds = $this->syncDirectorsByName($relations['directors']);
            $movie->directors()->sync($directorIds);
        }
        
        if (isset($relations['artists'])) {
            $artistIds = $this->syncArtistsByName($relations['artists']);
            $movie->artists()->sync($artistIds);
        }
        
        return $movie;
    }
    
    public function updateWithRelations($id, array $attributes, array $relations = [])
    {
        $movie = $this->update($id, $attributes);
        
        if (isset($relations['genres'])) {
            $genreIds = $this->syncGenresByName($relations['genres']);
            $movie->genres()->sync($genreIds);
        }
        
        if (isset($relations['directors'])) {
            $directorIds = $this->syncDirectorsByName($relations['directors']);
            $movie->directors()->sync($directorIds);
        }
        
        if (isset($relations['artists'])) {
            $artistIds = $this->syncArtistsByName($relations['artists']);
            $movie->artists()->sync($artistIds);
        }
        
        return $movie;
    }
    
    protected function syncGenresByName(array $genreNames)
    {
        $genreIds = [];
        
        foreach ($genreNames as $name) {
            $genre = \App\Models\Genre::firstOrCreate(['name' => $name]);
            $genreIds[] = $genre->id;
        }
        
        return $genreIds;
    }
    
    protected function syncDirectorsByName(array $directorNames)
    {
        $directorIds = [];
        
        foreach ($directorNames as $name) {
            $director = \App\Models\Director::firstOrCreate(['name' => $name]);
            $directorIds[] = $director->id;
        }
        
        return $directorIds;
    }
    
    protected function syncArtistsByName(array $artistNames)
    {
        $artistIds = [];
        
        foreach ($artistNames as $name) {
            $artist = \App\Models\Artist::firstOrCreate(['name' => $name]);
            $artistIds[] = $artist->id;
        }
        
        return $artistIds;
    }
    
    public function searchMovies($term, $perPage = 15)
    {
        return $this->model
            ->where('title', 'like', "%{$term}%")
            ->orWhere('description', 'like', "%{$term}%")
            ->with(['genres', 'directors'])
            ->withCount('comments', 'ratings')
            ->withAvg('ratings as average_rating', 'value')
            ->paginate($perPage);
    }
    
    public function getRatingStatistics($movieId)
    {
        $movie = $this->getById($movieId);
        
        return [
            'overall' => [
                'average' => $movie->ratings()->avg('value') ?: 0,
                'count' => $movie->ratings()->count(),
            ],
            'ratings_distribution' => $this->getRatingDistribution($movieId),
            'recent_comments' => $movie->comments()->with('user')->latest()->take(5)->get(),
            'recent_ratings' => $movie->ratings()->with('user')->latest()->take(5)->get(),
            'comments_count' => $movie->comments()->count()
        ];
    }
    
    protected function getRatingDistribution($movieId)
    {
        $distribution = [];
        $ratings = \App\Models\Rating::where('movie_id', $movieId)->get();
        
        // Initialize all values from 1 to 10 with zero count
        for ($i = 1; $i <= 10; $i++) {
            $distribution[$i] = 0;
        }
        
        // Count ratings by value
        foreach ($ratings as $rating) {
            if (isset($distribution[$rating->value])) {
                $distribution[$rating->value]++;
            }
        }
        
        return $distribution;
    }
} 