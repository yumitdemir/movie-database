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
            'comments_count' => $movie->comments()->count(),
            'demographics' => [
                'gender' => $this->getRatingsByGender($movieId),
                'age' => $this->getRatingsByAgeGroup($movieId),
                'geography' => [
                    'continents' => $this->getRatingsByContinent($movieId),
                    'countries' => $this->getRatingsByCountry($movieId)
                ]
            ]
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
    
    /**
     * Get ratings distribution by gender
     */
    protected function getRatingsByGender($movieId)
    {
        $stats = [];
        $genders = ['male', 'female', 'other', 'unspecified'];
        
        foreach ($genders as $gender) {
            $query = \App\Models\Rating::where('movie_id', $movieId);
            
            if ($gender === 'unspecified') {
                $query = $query->whereHas('user', function ($q) {
                    $q->whereNull('gender');
                });
            } else {
                $query = $query->whereHas('user', function ($q) use ($gender) {
                    $q->where('gender', $gender);
                });
            }
            
            $stats[$gender] = [
                'count' => $query->count(),
                'average' => $query->avg('value') ?: 0,
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get ratings distribution by age groups
     */
    protected function getRatingsByAgeGroup($movieId)
    {
        $stats = [];
        $ageGroups = [
            'under_18' => [0, 17],
            '18_24' => [18, 24],
            '25_34' => [25, 34],
            '35_44' => [35, 44],
            '45_54' => [45, 54],
            '55_plus' => [55, 200],
            'unknown' => null
        ];
        
        $currentDate = date('Y-m-d');
        $currentYear = (int)date('Y');
        
        foreach ($ageGroups as $group => $range) {
            if ($group === 'unknown') {
                // Handle users without birth_date and age_group
                $query = \App\Models\Rating::where('movie_id', $movieId)
                    ->whereHas('user', function ($q) {
                        $q->whereNull('birth_date')
                          ->whereNull('age_group');
                    });
            } else {
                // Combine ratings from both approaches:
                // 1. Users who provided an explicit age_group
                // 2. Users whose birth_date puts them in this age group
                $ageGroupQuery = \App\Models\Rating::where('movie_id', $movieId)
                    ->whereHas('user', function ($q) use ($group) {
                        $q->where('age_group', $group);
                    });
                
                // Calculate age from birth_date
                $minYear = $currentYear - $range[1] - 1;
                $maxYear = $currentYear - $range[0];
                
                $birthDateQuery = \App\Models\Rating::where('movie_id', $movieId)
                    ->whereHas('user', function ($q) use ($minYear, $maxYear, $currentDate, $group) {
                        $q->whereNull('age_group') // Only count users who haven't specified an age_group
                          ->whereNotNull('birth_date')
                          ->where(function ($query) use ($minYear, $maxYear, $currentDate) {
                              // Users born between minYear and maxYear
                              $query->whereRaw("strftime('%Y', birth_date) > ?", [$minYear])
                                    ->whereRaw("strftime('%Y', birth_date) <= ?", [$maxYear])
                                    // For the upper bound, ensure we're only counting people who have had their birthday this year
                                    ->orWhere(function ($q) use ($maxYear, $currentDate) {
                                        $q->whereRaw("strftime('%Y', birth_date) = ?", [$maxYear])
                                          ->whereRaw("strftime('%m-%d', birth_date) <= strftime('%m-%d', ?)", [$currentDate]);
                                    });
                          });
                    });
                
                // Get counts and averages from both queries
                $ageGroupCount = $ageGroupQuery->count();
                $ageGroupSum = $ageGroupQuery->sum('value');
                
                $birthDateCount = $birthDateQuery->count();
                $birthDateSum = $birthDateQuery->sum('value');
                
                // Combine the results
                $totalCount = $ageGroupCount + $birthDateCount;
                $totalSum = $ageGroupSum + $birthDateSum;
                
                $stats[$group] = [
                    'count' => $totalCount,
                    'average' => $totalCount > 0 ? $totalSum / $totalCount : 0,
                ];
                
                continue; // Skip the regular processing
            }
            
            $stats[$group] = [
                'count' => $query->count(),
                'average' => $query->avg('value') ?: 0,
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get ratings distribution by continent
     */
    protected function getRatingsByContinent($movieId)
    {
        $stats = [];
        $continents = [
            'Africa', 'Asia', 'Europe', 'North America', 'South America', 
            'Australia/Oceania', 'Antarctica', 'unspecified'
        ];
        
        foreach ($continents as $continent) {
            $query = \App\Models\Rating::where('movie_id', $movieId);
            
            if ($continent === 'unspecified') {
                $query = $query->whereHas('user', function ($q) {
                    $q->whereNull('continent');
                });
            } else {
                $query = $query->whereHas('user', function ($q) use ($continent) {
                    $q->where('continent', $continent);
                });
            }
            
            $stats[$continent] = [
                'count' => $query->count(),
                'average' => $query->avg('value') ?: 0,
            ];
        }
        
        return $stats;
    }
    
    /**
     * Get ratings distribution by top countries
     */
    protected function getRatingsByCountry($movieId)
    {
        // Get countries with at least one rating
        $countries = \App\Models\Rating::where('movie_id', $movieId)
            ->whereHas('user', function ($q) {
                $q->whereNotNull('country');
            })
            ->with('user')
            ->get()
            ->pluck('user.country')
            ->unique()
            ->values()
            ->toArray();
        
        // Add 'unspecified' for users without country
        $countries[] = 'unspecified';
        
        $stats = [];
        foreach ($countries as $country) {
            $query = \App\Models\Rating::where('movie_id', $movieId);
            
            if ($country === 'unspecified') {
                $query = $query->whereHas('user', function ($q) {
                    $q->whereNull('country');
                });
            } else {
                $query = $query->whereHas('user', function ($q) use ($country) {
                    $q->where('country', $country);
                });
            }
            
            $stats[$country] = [
                'count' => $query->count(),
                'average' => $query->avg('value') ?: 0,
            ];
        }
        
        // Sort by count descending and take top 10
        uasort($stats, function ($a, $b) {
            return $b['count'] <=> $a['count']; 
        });
        
        return array_slice($stats, 0, 10);
    }
} 