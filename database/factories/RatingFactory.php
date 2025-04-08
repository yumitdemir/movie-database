<?php

namespace Database\Factories;

use App\Models\Rating;
use App\Models\User;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rating>
 */
class RatingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Rating::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'movie_id' => Movie::factory(),
            'value' => fake()->numberBetween(1, 10),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return Carbon::parse($attributes['created_at'])->addMinutes(rand(0, 60));
            }
        ];
    }
    
    /**
     * Configure the model factory to associate with an existing movie.
     *
     * @param int $movieId
     * @return $this
     */
    public function forMovie(int $movieId)
    {
        return $this->state(function (array $attributes) use ($movieId) {
            return [
                'movie_id' => $movieId,
            ];
        });
    }
    
    /**
     * Configure the model factory to associate with an existing user.
     *
     * @param int $userId
     * @return $this
     */
    public function byUser(int $userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'user_id' => $userId,
            ];
        });
    }
    
    /**
     * Configure the model factory to create a high rating (8-10)
     *
     * @return $this
     */
    public function highRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => fake()->numberBetween(8, 10),
            ];
        });
    }
    
    /**
     * Configure the model factory to create a medium rating (5-7)
     *
     * @return $this
     */
    public function mediumRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => fake()->numberBetween(5, 7),
            ];
        });
    }
    
    /**
     * Configure the model factory to create a low rating (1-4)
     *
     * @return $this
     */
    public function lowRating()
    {
        return $this->state(function (array $attributes) {
            return [
                'value' => fake()->numberBetween(1, 4),
            ];
        });
    }
} 