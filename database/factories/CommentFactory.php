<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\User;
use App\Models\Movie;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Comment::class;

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
            'content' => fake()->paragraph(),
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
} 