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
            $movie->genres()->sync($relations['genres']);
        }
        
        if (isset($relations['directors'])) {
            $movie->directors()->sync($relations['directors']);
        }
        
        if (isset($relations['artists'])) {
            foreach ($relations['artists'] as $artistId => $role) {
                $movie->artists()->attach($artistId, ['role' => $role]);
            }
        }
        
        return $movie;
    }
    
    public function updateWithRelations($id, array $attributes, array $relations = [])
    {
        $movie = $this->update($id, $attributes);
        
        if (isset($relations['genres'])) {
            $movie->genres()->sync($relations['genres']);
        }
        
        if (isset($relations['directors'])) {
            $movie->directors()->sync($relations['directors']);
        }
        
        if (isset($relations['artists'])) {
            $movie->artists()->detach();
            foreach ($relations['artists'] as $artistId => $role) {
                $movie->artists()->attach($artistId, ['role' => $role]);
            }
        }
        
        return $movie;
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