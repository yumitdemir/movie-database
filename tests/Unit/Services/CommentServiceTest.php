<?php

namespace Tests\Unit\Services;

use App\Models\Comment;
use App\Models\User;
use App\Models\Movie;
use App\Repositories\CommentRepository;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommentService $service;
    protected $mockRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->mockRepository = Mockery::mock(CommentRepository::class);
        $this->service = new CommentService($this->mockRepository);
        
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
    public function it_can_get_movie_comments()
    {
        // Arrange
        $movieId = 1;
        $perPage = 15;
        $expectedComments = collect([
            new Comment(['id' => 1, 'movie_id' => $movieId, 'content' => 'Great movie!']),
            new Comment(['id' => 2, 'movie_id' => $movieId, 'content' => 'I loved it!'])
        ]);
        
        $this->mockRepository->shouldReceive('getCommentsByMovie')
            ->once()
            ->with($movieId, $perPage)
            ->andReturn($expectedComments);

        // Act
        $comments = $this->service->getMovieComments($movieId);

        // Assert
        $this->assertEquals($expectedComments, $comments);
    }

    /** @test */
    public function it_can_create_comment()
    {
        // Arrange
        $userId = 1;
        $movieId = 1;
        $content = 'This is a test comment';
        
        $expectedComment = new Comment([
            'id' => 1,
            'user_id' => $userId,
            'movie_id' => $movieId,
            'content' => $content
        ]);
        
        $this->mockRepository->shouldReceive('create')
            ->once()
            ->with([
                'user_id' => $userId,
                'movie_id' => $movieId,
                'content' => $content
            ])
            ->andReturn($expectedComment);

        // Act
        $comment = $this->service->createComment($userId, $movieId, $content);

        // Assert
        $this->assertEquals($expectedComment, $comment);
    }

    /** @test */
    public function it_can_update_comment()
    {
        // Arrange
        $commentId = 1;
        $newContent = 'Updated comment content';
        
        $updatedComment = new Comment([
            'id' => $commentId,
            'user_id' => 1,
            'movie_id' => 1,
            'content' => $newContent
        ]);
            
        $this->mockRepository->shouldReceive('update')
            ->once()
            ->with($commentId, ['content' => $newContent])
            ->andReturn($updatedComment);

        // Act
        $comment = $this->service->updateComment($commentId, $newContent);

        // Assert
        $this->assertEquals($updatedComment, $comment);
        $this->assertEquals($newContent, $comment->content);
    }

    /** @test */
    public function it_can_delete_comment()
    {
        // Arrange
        $commentId = 1;
        
        $this->mockRepository->shouldReceive('delete')
            ->once()
            ->with($commentId)
            ->andReturn(true);

        // Act
        $result = $this->service->deleteComment($commentId);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_comment_by_id()
    {
        // Arrange
        $commentId = 1;
        $expectedComment = new Comment([
            'id' => $commentId,
            'user_id' => 1,
            'movie_id' => 1,
            'content' => 'Test comment'
        ]);
        
        $this->mockRepository->shouldReceive('getById')
            ->once()
            ->with($commentId, ['*'], [])
            ->andReturn($expectedComment);

        // Act
        $comment = $this->service->getById($commentId);

        // Assert
        $this->assertEquals($expectedComment, $comment);
    }

    /** @test */
    public function it_can_get_comment_with_relations()
    {
        // Arrange
        $commentId = 1;
        $relations = ['user', 'movie'];
        
        $user = new User(['id' => 1, 'name' => 'Test User']);
        $movie = new Movie(['id' => 1, 'title' => 'Test Movie']);
        
        $expectedComment = new Comment([
            'id' => $commentId,
            'user_id' => 1,
            'movie_id' => 1,
            'content' => 'Test comment'
        ]);
        $expectedComment->setRelation('user', $user);
        $expectedComment->setRelation('movie', $movie);
        
        $this->mockRepository->shouldReceive('getById')
            ->once()
            ->with($commentId, ['*'], $relations)
            ->andReturn($expectedComment);

        // Act
        $comment = $this->service->getById($commentId, ['*'], $relations);

        // Assert
        $this->assertEquals($expectedComment, $comment);
        $this->assertTrue($comment->relationLoaded('user'));
        $this->assertTrue($comment->relationLoaded('movie'));
        $this->assertEquals('Test User', $comment->user->name);
        $this->assertEquals('Test Movie', $comment->movie->title);
    }
} 