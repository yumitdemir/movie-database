@extends('layouts.app')

@section('title', 'Statistics: ' . $movie->title)

@section('content')
    <div class="container">
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Statistics: {{ $movie->title }}</h1>
                <div>
                    <a href="{{ route('movies.show', $movie->id) }}" class="btn btn-outline-secondary">Back to Movie</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Rating Summary</h5>
                    </div>
                    <div class="card-body">
                        @if($movie->average_rating)
                            <div class="display-4 text-center">
                                <span class="text-warning">★</span> {{ number_format($movie->average_rating, 1) }}/10
                            </div>
                            <p class="text-center text-muted">{{ $movie->ratings_count }} {{ Str::plural('rating', $movie->ratings_count) }}</p>
                            
                            <div class="mt-4">
                                <h6>Rating Distribution</h6>
                                <div class="rating-bars">
                                    @for($i = 10; $i >= 1; $i--)
                                        @php
                                            $count = $statistics['ratings_distribution'][$i] ?? 0;
                                            $percentage = $movie->ratings_count > 0 ? ($count / $movie->ratings_count) * 100 : 0;
                                        @endphp
                                        <div class="d-flex align-items-center mb-1">
                                            <div style="width: 30px;">{{ $i }}</div>
                                            <div class="progress flex-grow-1" style="height: 20px;">
                                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%"
                                                     aria-valuenow="{{ $percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                            <div style="width: 30px; text-align: right;">{{ $count }}</div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        @else
                            <p class="text-center">No ratings yet</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Activity</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h6>Comments</h6>
                                <div class="text-center">
                                    <span class="display-4">{{ $statistics['comments_count'] }}</span>
                                    <p class="text-muted">Total Comments</p>
                                </div>
                                
                                @if(!empty($statistics['recent_comments']))
                                    <h6 class="mt-3">Recent Activity</h6>
                                    <ul class="list-group">
                                        @foreach($statistics['recent_comments'] as $comment)
                                            <li class="list-group-item">
                                                <strong>{{ $comment->user->name }}</strong> commented
                                                <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <h6>Ratings</h6>
                                <div class="text-center">
                                    <span class="display-4">{{ $movie->ratings_count }}</span>
                                    <p class="text-muted">Total Ratings</p>
                                </div>
                                
                                @if(!empty($statistics['recent_ratings']))
                                    <h6 class="mt-3">Recent Activity</h6>
                                    <ul class="list-group">
                                        @foreach($statistics['recent_ratings'] as $rating)
                                            <li class="list-group-item">
                                                <strong>{{ $rating->user->name }}</strong> rated <strong>{{ $rating->value }}/10</strong>
                                                <small class="text-muted">{{ $rating->created_at->diffForHumans() }}</small>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 