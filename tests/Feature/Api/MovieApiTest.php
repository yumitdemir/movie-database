<?php

namespace Tests\Feature\Api;

use App\Models\Movie;
use App\Models\User;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MovieApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_get_list_of_movies()
    {
        // Arrange
        $movies = Movie::factory()->count(3)->create();

        // Act
        $response = $this->getJson('/api/movies');

        // Assert
        $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => ['id', 'title', 'description', 'release_date']
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_single_movie()
    {
        // Arrange
        $movie = Movie::factory()->create();

        // Act
        $response = $this->getJson("/api/movies/{$movie->id}");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'id' => $movie->id,
                        'title' => $movie->title
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_non_existent_movie()
    {
        // Act
        $response = $this->getJson('/api/movies/9999');

        // Assert
        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Movie not found'
                ]);
    }

    /** @test */
    public function it_can_search_movies()
    {
        // Arrange
        Movie::factory()->create(['title' => 'The Matrix']);
        Movie::factory()->create(['title' => 'The Lord of the Rings']);
        Movie::factory()->create(['title' => 'Star Wars']);

        // Act
        $response = $this->getJson('/api/movies/search?q=The');

        // Assert
        $response->assertStatus(200)
                ->assertJsonCount(2, 'data')
                ->assertJsonPath('data.0.title', 'The Matrix')
                ->assertJsonPath('data.1.title', 'The Lord of the Rings');
    }

    /** @test */
    public function it_requires_authentication_to_create_movie()
    {
        // Arrange
        $movieData = [
            'title' => 'Test Movie',
            'description' => 'Test Description',
            'release_date' => '2023-01-01'
        ];

        // Act
        $response = $this->postJson('/api/movies', $movieData);

        // Assert
        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_create_movie_with_authentication()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $movieData = [
            'title' => 'New API Movie',
            'description' => 'Created via API',
            'release_date' => '2023-01-01',
            'genres' => 'Action, Thriller',
            'directors' => 'API Director'
        ];

        // Act
        $response = $this->postJson('/api/movies', $movieData);

        // Assert
        $response->assertStatus(201)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Movie created successfully',
                    'data' => [
                        'title' => 'New API Movie',
                        'description' => 'Created via API'
                    ]
                ]);
                
        $this->assertDatabaseHas('movies', [
            'title' => 'New API Movie',
            'description' => 'Created via API'
        ]);
    }

    /** @test */
    public function it_validates_movie_creation_data()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $invalidData = [
            'title' => '', // Empty title (required)
            'description' => 'Test description',
            'release_date' => 'not-a-date' // Invalid date format
        ];

        // Act
        $response = $this->postJson('/api/movies', $invalidData);

        // Assert
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['title', 'release_date']);
    }

    /** @test */
    public function it_can_update_movie_with_authentication()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $movie = Movie::factory()->create();
        
        $updateData = [
            'title' => 'Updated API Movie',
            'description' => 'Updated via API'
        ];

        // Act
        $response = $this->putJson("/api/movies/{$movie->id}", $updateData);

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Movie updated successfully',
                    'data' => [
                        'title' => 'Updated API Movie',
                        'description' => 'Updated via API'
                    ]
                ]);
                
        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => 'Updated API Movie',
            'description' => 'Updated via API'
        ]);
    }

    /** @test */
    public function it_can_delete_movie_with_authentication()
    {
        // Arrange
        Sanctum::actingAs($this->user);
        
        $movie = Movie::factory()->create();

        // Act
        $response = $this->deleteJson("/api/movies/{$movie->id}");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Movie deleted successfully'
                ]);
                
        $this->assertDatabaseMissing('movies', [
            'id' => $movie->id
        ]);
    }

    /** @test */
    public function it_can_get_movie_statistics()
    {
        // Arrange
        $movie = Movie::factory()->create();
        
        // Create users with demographic data
        $user1 = User::factory()->create([
            'gender' => 'male',
            'age_group' => '25_34',
            'continent' => 'Europe',
            'country' => 'Germany'
        ]);
        
        $user2 = User::factory()->create([
            'gender' => 'female',
            'age_group' => '18_24',
            'continent' => 'North America',
            'country' => 'United States'
        ]);
        
        // Create ratings
        Rating::factory()->create(['user_id' => $user1->id, 'movie_id' => $movie->id, 'value' => 8]);
        Rating::factory()->create(['user_id' => $user2->id, 'movie_id' => $movie->id, 'value' => 9]);

        // Act
        $response = $this->getJson("/api/movies/{$movie->id}/statistics");

        // Assert
        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'movie' => ['id' => $movie->id],
                        'statistics' => [
                            'average_rating' => 8.5,
                            'total_ratings' => 2
                        ]
                    ]
                ])
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'movie',
                        'statistics' => [
                            'average_rating',
                            'total_ratings',
                            'by_gender',
                            'by_age_group',
                            'by_continent',
                            'by_country'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_get_movie_comments()
    {
        // Arrange
        $movie = Movie::factory()->create();
        
        // Create some comments for the movie
        $comments = [];
        for ($i = 0; $i < 3; $i++) {
            $comments[] = Comment::factory()->create([
                'movie_id' => $movie->id,
                'user_id' => $this->user->id,
                'content' => "Test comment {$i}"
            ]);
        }

        // Act
        $response = $this->getJson("/api/movies/{$movie->id}/comments");

        // Assert
        $response->assertStatus(200)
                ->assertJsonCount(3, 'data')
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => ['id', 'content', 'user_id', 'movie_id', 'created_at']
                    ]
                ]);
    }
} 