<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Media>
 */
class MediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['image', 'video', 'document'];
        $type = fake()->randomElement($types);
        
        $paths = [
            'image' => 'media/images/movie_' . fake()->numberBetween(1, 100) . '_' . fake()->word() . '.jpg',
            'video' => 'media/videos/movie_' . fake()->numberBetween(1, 100) . '_' . fake()->word() . '.mp4',
            'document' => 'media/documents/movie_' . fake()->numberBetween(1, 100) . '_' . fake()->word() . '.pdf',
        ];

        return [
            'movie_id' => Movie::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional(0.7)->sentence(),
            'type' => $type,
            'path' => $paths[$type],
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => function (array $attributes) {
                return Carbon::parse($attributes['created_at'])->addMinutes(rand(0, 60));
            }
        ];
    }
    
    /**
     * Configure the factory to create an image media.
     *
     * @return $this
     */
    public function image()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'image',
                'path' => 'media/images/movie_' . fake()->numberBetween(1, 100) . '_' . fake()->word() . '.jpg',
            ];
        });
    }
    
    /**
     * Configure the factory to create a video media.
     *
     * @return $this
     */
    public function video()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'video',
                'path' => 'media/videos/movie_' . fake()->numberBetween(1, 100) . '_' . fake()->word() . '.mp4',
            ];
        });
    }
    
    /**
     * Configure the factory to create a document media.
     *
     * @return $this
     */
    public function document()
    {
        return $this->state(function (array $attributes) {
            return [
                'type' => 'document',
                'path' => 'media/documents/movie_' . fake()->numberBetween(1, 100) . '_' . fake()->word() . '.pdf',
            ];
        });
    }
} 