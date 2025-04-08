<?php

namespace Tests\Unit\Services;

use App\Models\Rating;
use App\Models\User;
use App\Models\Movie;
use App\Repositories\RatingRepository;
use App\Services\RatingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class RatingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RatingService $service;
    protected $mockRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(RatingRepository::class);
        $this->service = new RatingService($this->mockRepository);
        
        // Mock App and DB facades to handle transactions properly
        App::shouldReceive('environment')->andReturn('testing');
        DB::shouldReceive('transactionLevel')->andReturn(0);
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_rate_movie_for_first_time()
    {
        // Arrange
        $userId = 1;
        $movieId = 1;
        $value = 8;
        
        // Simulate that no rating exists yet
        $this->mockRepository->shouldReceive('rateMovie')
            ->once()
            ->with($userId, $movieId, $value)
            ->andReturn(new Rating([
                'id' => 1,
                'user_id' => $userId,
                'movie_id' => $movieId,
                'value' => $value
            ]));

        // Act
        $rating = $this->service->rateMovie($userId, $movieId, $value);

        // Assert
        $this->assertEquals($value, $rating->value);
        $this->assertEquals($userId, $rating->user_id);
        $this->assertEquals($movieId, $rating->movie_id);
    }

    /** @test */
    public function it_can_update_existing_rating()
    {
        // Arrange
        $userId = 1;
        $movieId = 1;
        $newValue = 9;
        
        $updatedRating = new Rating([
            'id' => 1,
            'user_id' => $userId,
            'movie_id' => $movieId,
            'value' => $newValue
        ]);
        
        $this->mockRepository->shouldReceive('rateMovie')
            ->once()
            ->with($userId, $movieId, $newValue)
            ->andReturn($updatedRating);

        // Act
        $rating = $this->service->rateMovie($userId, $movieId, $newValue);

        // Assert
        $this->assertEquals($updatedRating, $rating);
        $this->assertEquals($newValue, $rating->value);
    }

    /** @test */
    public function it_can_get_user_rating()
    {
        // Arrange
        $userId = 1;
        $movieId = 1;
        
        $expectedRating = new Rating([
            'id' => 1,
            'user_id' => $userId,
            'movie_id' => $movieId,
            'value' => 8
        ]);
        
        $this->mockRepository->shouldReceive('getUserRating')
            ->once()
            ->with($userId, $movieId)
            ->andReturn($expectedRating);

        // Act
        $rating = $this->service->getUserRating($userId, $movieId);

        // Assert
        $this->assertEquals($expectedRating, $rating);
    }

    /** @test */
    public function it_returns_null_when_user_has_no_rating()
    {
        // Arrange
        $userId = 1;
        $movieId = 1;
        
        $this->mockRepository->shouldReceive('getUserRating')
            ->once()
            ->with($userId, $movieId)
            ->andReturn(null);

        // Act
        $rating = $this->service->getUserRating($userId, $movieId);

        // Assert
        $this->assertNull($rating);
    }

    /** @test */
    public function it_can_get_rating_by_id()
    {
        // Arrange
        $ratingId = 1;
        
        $expectedRating = new Rating([
            'id' => $ratingId,
            'user_id' => 1,
            'movie_id' => 1,
            'value' => 8
        ]);
        
        $this->mockRepository->shouldReceive('getById')
            ->once()
            ->with($ratingId)
            ->andReturn($expectedRating);

        // Act
        $rating = $this->service->getById($ratingId);

        // Assert
        $this->assertEquals($expectedRating, $rating);
    }

    /** @test */
    public function it_can_get_movie_average_rating()
    {
        // Arrange
        $movieId = 1;
        $expectedAverage = 8.5;
        
        $this->mockRepository->shouldReceive('getMovieAverageRating')
            ->once()
            ->with($movieId)
            ->andReturn($expectedAverage);

        // Act
        $average = $this->service->getMovieAverageRating($movieId);

        // Assert
        $this->assertEquals($expectedAverage, $average);
    }

    /** @test */
    public function it_can_get_movie_rating_count()
    {
        // Arrange
        $movieId = 1;
        $expectedCount = 10;
        
        $this->mockRepository->shouldReceive('getMovieRatingCount')
            ->once()
            ->with($movieId)
            ->andReturn($expectedCount);

        // Act
        $count = $this->service->getMovieRatingCount($movieId);

        // Assert
        $this->assertEquals($expectedCount, $count);
    }
} 