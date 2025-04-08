<?php

namespace Tests\Feature;

use App\Models\Movie;
use App\Models\User;
use App\Models\Comment;
use App\Models\Rating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MovieControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication tests
        $this->user = User::factory()->create();
        
        // Setup fake storage
        Storage::fake('public');
    }

    /** @test */
    public function guests_can_view_movie_list()
    {
        // Arrange
        $movies = Movie::factory()->count(3)->create();

        // Act
        $response = $this->get(route('movies.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('movies');
        foreach ($movies as $movie) {
            $response->assertSee($movie->title);
        }
    }

    /** @test */
    public function guests_can_view_movie_details()
    {
        // Arrange
        $movie = Movie::factory()->create();

        // Act
        $response = $this->get(route('movies.show', $movie->id));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('movie');
        $response->assertSee($movie->title);
        $response->assertSee($movie->description);
    }

    /** @test */
    public function guests_can_search_movies()
    {
        // Arrange
        Movie::factory()->create(['title' => 'The Matrix']);
        Movie::factory()->create(['title' => 'The Lord of the Rings']);
        Movie::factory()->create(['title' => 'Star Wars']);

        // Act
        $response = $this->get(route('movies.search', ['q' => 'The']));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('movies');
        $response->assertSee('The Matrix');
        $response->assertSee('The Lord of the Rings');
        $response->assertDontSee('Star Wars');
    }

    /** @test */
    public function guests_cannot_access_create_movie_page()
    {
        // Act
        $response = $this->get(route('movies.create'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_users_can_access_create_movie_page()
    {
        // Act
        $response = $this->actingAs($this->user)
                         ->get(route('movies.create'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('movies.create');
    }

    /** @test */
    public function authenticated_users_can_create_movies()
    {
        // Arrange
        $movieData = [
            'title' => 'Test Movie',
            'description' => 'This is a test movie description',
            'release_date' => '2023-01-01',
            'runtime_minutes' => 120,
            'language' => 'English',
            'poster' => UploadedFile::fake()->image('movie-poster.jpg'),
            'genres' => 'Action, Adventure',
            'directors' => 'Test Director',
            'artists' => 'Test Actor 1, Test Actor 2'
        ];

        // Act
        $response = $this->actingAs($this->user)
                         ->post(route('movies.store'), $movieData);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('movies', [
            'title' => 'Test Movie',
            'description' => 'This is a test movie description',
        ]);
        $this->assertDatabaseHas('genres', [
            'name' => 'Action'
        ]);
        $this->assertDatabaseHas('genres', [
            'name' => 'Adventure'
        ]);
        $this->assertDatabaseHas('directors', [
            'name' => 'Test Director'
        ]);
        $this->assertDatabaseHas('artists', [
            'name' => 'Test Actor 1'
        ]);
    }

    /** @test */
    public function authenticated_users_can_update_movies()
    {
        // Arrange
        $movie = Movie::factory()->create([
            'title' => 'Original Title',
            'description' => 'Original Description'
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'release_date' => '2023-01-01',
            'runtime_minutes' => 120,
            'language' => 'English',
            'genres' => 'Drama, Comedy',
            'directors' => 'Updated Director',
            'artists' => 'Updated Actor'
        ];

        // Act
        $response = $this->actingAs($this->user)
                         ->put(route('movies.update', $movie->id), $updateData);

        // Assert
        $response->assertRedirect(route('movies.show', $movie->id));
        $this->assertDatabaseHas('movies', [
            'id' => $movie->id,
            'title' => 'Updated Title',
            'description' => 'Updated Description'
        ]);
    }

    /** @test */
    public function authenticated_users_can_delete_movies()
    {
        // Arrange
        $movie = Movie::factory()->create();

        // Act
        $response = $this->actingAs($this->user)
                         ->delete(route('movies.destroy', $movie->id));

        // Assert
        $response->assertRedirect(route('movies.index'));
        $this->assertDatabaseMissing('movies', [
            'id' => $movie->id
        ]);
    }

    /** @test */
    public function users_can_view_movie_statistics()
    {
        // Arrange
        $movie = Movie::factory()->create();
        
        // Create some ratings with demographic data
        $user1 = User::factory()->create([
            'gender' => 'male', 
            'age_group' => '25_34', 
            'continent' => 'Europe', 
            'country' => 'France'
        ]);
        
        $user2 = User::factory()->create([
            'gender' => 'female', 
            'age_group' => '18_24', 
            'continent' => 'North America', 
            'country' => 'USA'
        ]);
        
        Rating::factory()->create(['user_id' => $user1->id, 'movie_id' => $movie->id, 'value' => 8]);
        Rating::factory()->create(['user_id' => $user2->id, 'movie_id' => $movie->id, 'value' => 9]);

        // Act
        $response = $this->get(route('movies.statistics', $movie->id));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('movie');
        $response->assertViewHas('statistics');
        $response->assertSee($movie->title);
    }

    /** @test */
    public function validation_fails_with_invalid_movie_data()
    {
        // Arrange
        $invalidData = [
            'title' => '', // Title is required
            'description' => 'Description',
            'release_date' => 'invalid-date', // Invalid date format
            'runtime_minutes' => 'not-a-number', // Should be a number
        ];

        // Act
        $response = $this->actingAs($this->user)
                         ->post(route('movies.store'), $invalidData);

        // Assert
        $response->assertSessionHasErrors(['title', 'release_date', 'runtime_minutes']);
    }
} 