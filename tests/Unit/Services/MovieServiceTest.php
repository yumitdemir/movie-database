<?php

namespace Tests\Unit\Services;

use App\Models\Movie;
use App\Repositories\MovieRepository;
use App\Services\MovieService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Mockery;
use Tests\TestCase;

class MovieServiceTest extends TestCase
{
    use RefreshDatabase;

    protected MovieService $service;
    protected $mockRepository;

    public function setUp(): void
    {
        parent::setUp();
        
        // Create a mock of the MovieRepository
        $this->mockRepository = Mockery::mock(MovieRepository::class);
        
        // Inject the mock into the service
        $this->service = new MovieService($this->mockRepository);
        
        // Setup storage for file uploads
        Storage::fake('public');
        
        // Mock App and DB facades
        App::shouldReceive('environment')->andReturn('testing');
        DB::shouldReceive('transactionLevel')->andReturn(1);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_all_movies()
    {
        // Arrange
        $expectedMovies = collect([new Movie(['id' => 1, 'title' => 'Test Movie'])]);
        $this->mockRepository->shouldReceive('getAllWithRelations')
            ->once()
            ->andReturn($expectedMovies);

        // Act
        $movies = $this->service->getAllMovies();

        // Assert
        $this->assertEquals($expectedMovies, $movies);
    }

    /** @test */
    public function it_can_get_movie_by_id()
    {
        // Arrange
        $movieId = 1;
        $expectedMovie = new Movie(['id' => $movieId, 'title' => 'Test Movie']);
        $this->mockRepository->shouldReceive('getByIdWithRelations')
            ->once()
            ->with($movieId)
            ->andReturn($expectedMovie);

        // Act
        $movie = $this->service->getMovieById($movieId);

        // Assert
        $this->assertEquals($expectedMovie, $movie);
    }

    /** @test */
    public function it_can_create_movie()
    {
        // Arrange
        $movieData = [
            'title' => 'New Movie',
            'description' => 'Description',
            'release_date' => '2023-01-01',
            'poster' => UploadedFile::fake()->image('poster.jpg'),
            'genres' => ['Action', 'Drama'],
            'directors' => ['Christopher Nolan'],
            'artists' => ['Leonardo DiCaprio']
        ];
        
        $expectedMovie = new Movie([
            'id' => 1,
            'title' => 'New Movie',
            'description' => 'Description',
            'release_date' => '2023-01-01',
            'poster' => 'movies/some-path.jpg'
        ]);
        
        // The repository should be called with the processed data
        $this->mockRepository->shouldReceive('createWithRelations')
            ->once()
            ->withArgs(function ($data, $relations) use ($movieData) {
                // Verify that the poster path was processed correctly
                $this->assertArrayHasKey('poster', $data);
                $this->assertStringStartsWith('movies/', $data['poster']);
                
                // Verify relations
                $this->assertEquals($movieData['genres'], $relations['genres']);
                $this->assertEquals($movieData['directors'], $relations['directors']);
                $this->assertEquals($movieData['artists'], $relations['artists']);
                
                return true;
            })
            ->andReturn($expectedMovie);

        // Act
        $movie = $this->service->createMovie($movieData);

        // Assert
        $this->assertEquals($expectedMovie, $movie);
    }

    /** @test */
    public function it_can_update_movie()
    {
        // Arrange
        $movieId = 1;
        $existingMovie = new Movie([
            'id' => $movieId,
            'title' => 'Old Title',
            'description' => 'Old Description',
            'poster' => 'movies/old-poster.jpg'
        ]);
        
        $this->mockRepository->shouldReceive('getByIdWithRelations')
            ->once()
            ->with($movieId)
            ->andReturn($existingMovie);
        
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'poster' => UploadedFile::fake()->image('new-poster.jpg'),
            'genres' => ['Sci-Fi'],
            'directors' => ['James Cameron']
        ];
        
        $updatedMovie = new Movie([
            'id' => $movieId,
            'title' => 'Updated Title',
            'description' => 'Updated Description',
            'poster' => 'movies/new-path.jpg'
        ]);
        
        $this->mockRepository->shouldReceive('updateWithRelations')
            ->once()
            ->withArgs(function ($id, $data, $relations) use ($movieId, $updateData) {
                $this->assertEquals($movieId, $id);
                $this->assertEquals($updateData['title'], $data['title']);
                $this->assertStringStartsWith('movies/', $data['poster']);
                $this->assertEquals($updateData['genres'], $relations['genres']);
                $this->assertEquals($updateData['directors'], $relations['directors']);
                
                return true;
            })
            ->andReturn($updatedMovie);

        // Act
        $movie = $this->service->updateMovie($movieId, $updateData);

        // Assert
        $this->assertEquals($updatedMovie, $movie);
    }

    /** @test */
    public function it_can_delete_movie()
    {
        // Arrange
        $movieId = 1;
        $movie = new Movie([
            'id' => $movieId,
            'title' => 'Movie to Delete',
            'poster' => 'movies/poster-to-delete.jpg',
            'media' => collect([])
        ]);
        
        $this->mockRepository->shouldReceive('getByIdWithRelations')
            ->once()
            ->with($movieId)
            ->andReturn($movie);
        
        $this->mockRepository->shouldReceive('delete')
            ->once()
            ->with($movieId)
            ->andReturn(true);

        // Act
        $result = $this->service->deleteMovie($movieId);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_search_movies()
    {
        // Arrange
        $searchTerm = 'test';
        $expectedResults = collect([
            new Movie(['id' => 1, 'title' => 'Test Movie 1']),
            new Movie(['id' => 2, 'title' => 'Test Movie 2'])
        ]);
        
        $this->mockRepository->shouldReceive('searchMovies')
            ->once()
            ->with($searchTerm)
            ->andReturn($expectedResults);

        // Act
        $results = $this->service->searchMovies($searchTerm);

        // Assert
        $this->assertEquals($expectedResults, $results);
    }

    /** @test */
    public function it_can_get_movie_statistics()
    {
        // Arrange
        $movieId = 1;
        $expectedStats = [
            'average_rating' => 8.5,
            'total_ratings' => 10,
            'by_gender' => [
                'male' => ['count' => 6, 'average' => 8.3],
                'female' => ['count' => 4, 'average' => 8.8]
            ],
            'by_age_group' => [
                '18_24' => ['count' => 3, 'average' => 9.0],
                '25_34' => ['count' => 7, 'average' => 8.2]
            ]
        ];
        
        $this->mockRepository->shouldReceive('getRatingStatistics')
            ->once()
            ->with($movieId)
            ->andReturn($expectedStats);

        // Act
        $statistics = $this->service->getMovieStatistics($movieId);

        // Assert
        $this->assertEquals($expectedStats, $statistics);
    }
} 