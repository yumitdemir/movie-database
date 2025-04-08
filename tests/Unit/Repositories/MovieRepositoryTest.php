<?php

namespace Tests\Unit\Repositories;

use App\Models\Movie;
use App\Models\Genre;
use App\Models\Artist;
use App\Models\Director;
use App\Models\Rating;
use App\Models\User;
use App\Repositories\MovieRepository;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class MovieRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected MovieRepository $repository;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository = new MovieRepository(new Movie());
    }

    /** @test */
    public function it_can_get_all_movies_with_relations()
    {
        // Arrange
        $movie = Movie::factory()->create();
        $genre = Genre::factory()->create();
        $director = Director::factory()->create();
        $artist = Artist::factory()->create();

        $movie->genres()->attach($genre);
        $movie->directors()->attach($director);
        $movie->artists()->attach($artist, ['role' => 'Lead Actor']);

        // Act
        $movies = $this->repository->getAllWithRelations();

        // Assert
        $this->assertCount(1, $movies);
        $this->assertEquals($movie->id, $movies->first()->id);
        $this->assertCount(1, $movies->first()->genres);
        $this->assertCount(1, $movies->first()->directors);
        $this->assertCount(1, $movies->first()->artists);
    }

    /** @test */
    public function it_can_get_movie_by_id_with_relations()
    {
        // Arrange
        $movie = Movie::factory()->create();
        $genre = Genre::factory()->create();
        $director = Director::factory()->create();
        $artist = Artist::factory()->create();

        $movie->genres()->attach($genre);
        $movie->directors()->attach($director);
        $movie->artists()->attach($artist, ['role' => 'Lead Actor']);

        // Act
        $foundMovie = $this->repository->getByIdWithRelations($movie->id);

        // Assert
        $this->assertEquals($movie->id, $foundMovie->id);
        $this->assertCount(1, $foundMovie->genres);
        $this->assertCount(1, $foundMovie->directors);
        $this->assertCount(1, $foundMovie->artists);
    }

    /** @test */
    public function it_can_create_movie_with_relations()
    {
        // Arrange
        $genreName = 'Action';
        $directorName = 'Christopher Nolan';
        $artistName = 'Leonardo DiCaprio';

        $movieData = [
            'title' => 'Inception',
            'description' => 'A thief who steals corporate secrets through the use of dream-sharing technology.',
            'release_date' => '2010-07-16',
            'runtime_minutes' => 148,
            'language' => 'English',
        ];

        $relations = [
            'genres' => [$genreName],
            'directors' => [$directorName],
            'artists' => [$artistName],
        ];

        // Act
        $movie = $this->repository->createWithRelations($movieData, $relations);

        // Assert
        $this->assertDatabaseHas('movies', ['title' => 'Inception']);
        $this->assertDatabaseHas('genres', ['name' => $genreName]);
        $this->assertDatabaseHas('directors', ['name' => $directorName]);
        $this->assertDatabaseHas('artists', ['name' => $artistName]);
        
        $this->assertCount(1, $movie->genres);
        $this->assertCount(1, $movie->directors);
        $this->assertCount(1, $movie->artists);
        $this->assertEquals($genreName, $movie->genres->first()->name);
    }

    /** @test */
    public function it_can_update_movie_with_relations()
    {
        // Arrange
        $movie = Movie::factory()->create(['title' => 'Old Title']);
        $oldGenre = Genre::factory()->create(['name' => 'Old Genre']);
        $oldDirector = Director::factory()->create(['name' => 'Old Director']);
        
        $movie->genres()->attach($oldGenre);
        $movie->directors()->attach($oldDirector);
        
        $newGenreName = 'New Genre';
        $newDirectorName = 'New Director';
        
        $updateData = [
            'title' => 'New Title',
            'description' => 'Updated description',
        ];
        
        $relations = [
            'genres' => [$newGenreName],
            'directors' => [$newDirectorName],
        ];

        // Act
        $updatedMovie = $this->repository->updateWithRelations($movie->id, $updateData, $relations);

        // Assert
        $this->assertEquals('New Title', $updatedMovie->title);
        $this->assertCount(1, $updatedMovie->genres);
        $this->assertCount(1, $updatedMovie->directors);
        $this->assertEquals($newGenreName, $updatedMovie->genres->first()->name);
        $this->assertEquals($newDirectorName, $updatedMovie->directors->first()->name);
    }

    /** @test */
    public function it_can_search_movies()
    {
        // Arrange
        Movie::factory()->create(['title' => 'The Shawshank Redemption']);
        Movie::factory()->create(['title' => 'The Godfather']);
        Movie::factory()->create(['title' => 'Finding Nemo']);

        // Act
        $results = $this->repository->searchMovies('The');

        // Assert
        $this->assertCount(2, $results);
        $this->assertTrue($results->pluck('title')->contains('The Shawshank Redemption'));
        $this->assertTrue($results->pluck('title')->contains('The Godfather'));
    }

    /** @test */
    public function it_can_get_rating_statistics()
    {
        // Arrange
        $movie = Movie::factory()->create();
        
        // Create users with different demographics
        $maleUser = User::factory()->create(['gender' => 'male', 'age_group' => '25_34', 'continent' => 'Europe', 'country' => 'Germany']);
        $femaleUser = User::factory()->create(['gender' => 'female', 'age_group' => '18_24', 'continent' => 'North America', 'country' => 'United States']);
        $otherUser = User::factory()->create(['gender' => 'other', 'age_group' => '35_44', 'continent' => 'Asia', 'country' => 'Japan']);
        
        // Create ratings
        Rating::factory()->create(['user_id' => $maleUser->id, 'movie_id' => $movie->id, 'value' => 8]);
        Rating::factory()->create(['user_id' => $femaleUser->id, 'movie_id' => $movie->id, 'value' => 6]);
        Rating::factory()->create(['user_id' => $otherUser->id, 'movie_id' => $movie->id, 'value' => 9]);
        
        // Act
        $statistics = $this->repository->getRatingStatistics($movie->id);
        
        // Assert - Adjust the assertions to match the actual structure
        $this->assertEquals(7.67, round($statistics['overall']['average'], 2));
        $this->assertEquals(3, $statistics['overall']['count']);
        
        // Check gender distribution
        $this->assertArrayHasKey('demographics', $statistics);
        $this->assertArrayHasKey('gender', $statistics['demographics']);
        $this->assertEquals(8.0, $statistics['demographics']['gender']['male']['average']);
        $this->assertEquals(6.0, $statistics['demographics']['gender']['female']['average']);
        $this->assertEquals(9.0, $statistics['demographics']['gender']['other']['average']);
        
        // Check age group distribution
        $this->assertArrayHasKey('age', $statistics['demographics']);
        $this->assertEquals(6.0, $statistics['demographics']['age']['18_24']['average']);
        $this->assertEquals(8.0, $statistics['demographics']['age']['25_34']['average']);
        $this->assertEquals(9.0, $statistics['demographics']['age']['35_44']['average']);
        
        // Check continent distribution
        $this->assertArrayHasKey('geography', $statistics['demographics']);
        $this->assertArrayHasKey('continents', $statistics['demographics']['geography']);
        $this->assertEquals(8.0, $statistics['demographics']['geography']['continents']['Europe']['average']);
        $this->assertEquals(6.0, $statistics['demographics']['geography']['continents']['North America']['average']);
        $this->assertEquals(9.0, $statistics['demographics']['geography']['continents']['Asia']['average']);
        
        // Check country distribution
        $this->assertArrayHasKey('countries', $statistics['demographics']['geography']);
        $this->assertEquals(8.0, $statistics['demographics']['geography']['countries']['Germany']['average']);
        $this->assertEquals(6.0, $statistics['demographics']['geography']['countries']['United States']['average']);
        $this->assertEquals(9.0, $statistics['demographics']['geography']['countries']['Japan']['average']);
    }
} 