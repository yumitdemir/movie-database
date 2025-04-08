@extends('layouts.app')

@section('title', $movie->title)

@section('content')
    <div class="container">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1>{{ $movie->title }}</h1>
                <div>
                    <a href="{{ route('movies.index') }}" class="btn btn-outline-secondary">Back to Movies</a>
                    @auth
                        <a href="{{ route('movies.statistics', $movie->id) }}" class="btn btn-outline-info">View Statistics</a>
                        <a href="{{ route('movies.edit', $movie->id) }}" class="btn btn-outline-primary">Edit</a>
                        <form action="{{ route('movies.destroy', $movie->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this movie?')">Delete</button>
                        </form>
                    @endauth
                </div>
            </div>
            <div class="text-muted">
                Released: {{ $movie->release_date->format('F j, Y') }}
                @if($movie->runtime_minutes)
                    | {{ $movie->runtime_minutes }} minutes
                @endif
                @if($movie->language)
                    | {{ $movie->language }}
                @endif
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                @if($movie->poster)
                    <img src="{{ asset('storage/' . $movie->poster) }}" class="img-fluid rounded" alt="{{ $movie->title }}">
                @else
                    <div class="bg-secondary text-white d-flex justify-content-center align-items-center rounded" style="height: 400px;">
                        <span class="fs-4">No Image</span>
                    </div>
                @endif

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Rating</h5>
                    </div>
                    <div class="card-body">
                        @if($movie->average_rating)
                            <div class="display-4 text-center">
                                <span class="text-warning">★</span> {{ number_format($movie->average_rating, 1) }}/10
                            </div>
                            <p class="text-center text-muted">{{ $movie->ratings_count }} {{ Str::plural('rating', $movie->ratings_count) }}</p>
                        @else
                            <p class="text-center">No ratings yet</p>
                        @endif

                        @auth
                            <form action="{{ $userRating ? route('ratings.update', $userRating->id) : route('ratings.store') }}" method="POST" class="mt-3">
                                @csrf
                                @if($userRating)
                                    @method('PUT')
                                @endif
                                <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                <div class="mb-3">
                                    <label for="rating" class="form-label">Your Rating</label>
                                    <select class="form-select" id="rating" name="rating" required>
                                        <option value="" disabled {{ !$userRating ? 'selected' : '' }}>Select your rating</option>
                                        @for($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ $userRating && $userRating->rating == $i ? 'selected' : '' }}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">{{ $userRating ? 'Update' : 'Submit' }} Rating</button>
                            </form>
                        @else
                            <div class="alert alert-info text-center">
                                <a href="{{ route('login') }}">Login</a> to rate this movie
                            </div>
                        @endauth
                    </div>
                </div>

                @if($movie->genres || $movie->directors || $movie->artists)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Details</h5>
                        </div>
                        <div class="card-body">
                            @if($movie->genres)
                                <div class="mb-3">
                                    <h6>Genres</h6>
                                    <div>
                                        @foreach($movie->genres as $genre)
                                            <span class="badge bg-primary">{{ $genre->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($movie->directors)
                                <div class="mb-3">
                                    <h6>Directors</h6>
                                    <div>
                                        @foreach($movie->directors as $director)
                                            <span class="badge bg-secondary">{{ $director->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($movie->artists)
                                <div class="mb-3">
                                    <h6>Artists</h6>
                                    <div>
                                        @foreach($movie->artists as $artist)
                                            <span class="badge bg-info">{{ $artist->name }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($movie->budget)
                                <div class="mb-3">
                                    <h6>Budget</h6>
                                    <p>${{ number_format($movie->budget, 0) }}</p>
                                </div>
                            @endif

                            @if($movie->revenue)
                                <div class="mb-3">
                                    <h6>Revenue</h6>
                                    <p>${{ number_format($movie->revenue, 0) }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Description</h5>
                    </div>
                    <div class="card-body">
                        <p>{{ $movie->description }}</p>
                    </div>
                </div>

                @if($movie->trailer_url)
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Trailer</h5>
                        </div>
                        <div class="card-body">
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ $movie->trailer_url }}" allowfullscreen></iframe>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Comments ({{ $comments->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @auth
                            <form action="{{ route('comments.store') }}" method="POST" class="mb-4">
                                @csrf
                                <input type="hidden" name="movie_id" value="{{ $movie->id }}">
                                <div class="mb-3">
                                    <label for="content" class="form-label">Add a comment</label>
                                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
                        @else
                            <div class="alert alert-info mb-4">
                                <a href="{{ route('login') }}">Login</a> to leave a comment
                            </div>
                        @endauth

                        @if($comments->isEmpty())
                            <p class="text-muted">No comments yet. Be the first to comment!</p>
                        @else
                            <div class="comments">
                                @foreach($comments as $comment)
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <strong>{{ $comment->user->name }}</strong>
                                                    <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                                </div>
                                                @auth
                                                    @if(auth()->id() === $comment->user_id)
                                                        <div>
                                                            <button class="btn btn-sm btn-outline-primary edit-comment-btn" data-id="{{ $comment->id }}" data-content="{{ $comment->content }}">Edit</button>
                                                            <form action="{{ route('comments.destroy', $comment->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this comment?')">Delete</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                @endauth
                                            </div>
                                            <p class="comment-content-{{ $comment->id }}">{{ $comment->content }}</p>
                                            <div class="edit-form-{{ $comment->id }}" style="display: none;">
                                                <form action="{{ route('comments.update', $comment->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="mb-2">
                                                        <textarea class="form-control" name="content" rows="3" required>{{ $comment->content }}</textarea>
                                                    </div>
                                                    <div>
                                                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                                        <button type="button" class="btn btn-sm btn-secondary cancel-edit-btn" data-id="{{ $comment->id }}">Cancel</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Edit comment functionality
        document.querySelectorAll('.edit-comment-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-id');
                document.querySelector(`.comment-content-${commentId}`).style.display = 'none';
                document.querySelector(`.edit-form-${commentId}`).style.display = 'block';
            });
        });

        // Cancel edit functionality
        document.querySelectorAll('.cancel-edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-id');
                document.querySelector(`.comment-content-${commentId}`).style.display = 'block';
                document.querySelector(`.edit-form-${commentId}`).style.display = 'none';
            });
        });
    });
</script>
@endsection 