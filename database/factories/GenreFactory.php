<?php

namespace Database\Factories;

use App\Models\Genre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Genre>
 */
class GenreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Genre::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $genres = [
            'Action', 'Adventure', 'Animation', 'Biography', 'Comedy', 
            'Crime', 'Documentary', 'Drama', 'Family', 'Fantasy', 
            'Film Noir', 'History', 'Horror', 'Music', 'Musical', 
            'Mystery', 'Romance', 'Sci-Fi', 'Sport', 'Superhero', 
            'Thriller', 'War', 'Western'
        ];

        return [
            'name' => fake()->unique()->randomElement($genres),
        ];
    }
} 