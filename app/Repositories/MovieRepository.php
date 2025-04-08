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
        
        $byGender = $movie->ratings()
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->selectRaw('users.gender, AVG(ratings.value) as average, COUNT(*) as count')
            ->groupBy('users.gender')
            ->get();
            
        $byAgeGroup = $movie->ratings()
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->selectRaw('
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE()) < 18 THEN "Under 18"
                    WHEN TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE()) BETWEEN 18 AND 24 THEN "18-24"
                    WHEN TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE()) BETWEEN 25 AND 34 THEN "25-34"
                    WHEN TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE()) BETWEEN 35 AND 44 THEN "35-44"
                    WHEN TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE()) BETWEEN 45 AND 54 THEN "45-54"
                    WHEN TIMESTAMPDIFF(YEAR, users.birth_date, CURDATE()) BETWEEN 55 AND 64 THEN "55-64"
                    ELSE "65+" 
                END AS age_group,
                AVG(ratings.value) as average,
                COUNT(*) as count
            ')
            ->groupBy('age_group')
            ->get();
            
        $byContinent = $movie->ratings()
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->selectRaw('users.continent, AVG(ratings.value) as average, COUNT(*) as count')
            ->groupBy('users.continent')
            ->get();
            
        $byCountry = $movie->ratings()
            ->join('users', 'ratings.user_id', '=', 'users.id')
            ->selectRaw('users.country, AVG(ratings.value) as average, COUNT(*) as count')
            ->groupBy('users.country')
            ->get();
            
        return [
            'overall' => [
                'average' => $movie->ratings()->avg('value') ?: 0,
                'count' => $movie->ratings()->count(),
            ],
            'by_gender' => $byGender,
            'by_age_group' => $byAgeGroup,
            'by_continent' => $byContinent,
            'by_country' => $byCountry,
        ];
    }
} 