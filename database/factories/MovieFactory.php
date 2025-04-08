<?php

namespace Database\Factories;

use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Movie::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'release_date' => fake()->dateTimeBetween('-30 years', 'now'),
            'runtime_minutes' => fake()->numberBetween(80, 180),
            'language' => fake()->randomElement(['English', 'Spanish', 'French', 'German', 'Mandarin', 'Japanese']),
            'poster' => null,
            'trailer_url' => fake()->optional(0.7)->url(),
            'budget' => fake()->optional(0.8)->randomFloat(2, 1000000, 250000000),
            'revenue' => fake()->optional(0.8)->randomFloat(2, 0, 3000000000),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
} 