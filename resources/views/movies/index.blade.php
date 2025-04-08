@extends('layouts.app')

@section('title', 'Movies')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ isset($term) ? 'Search Results for: ' . $term : 'Movies' }}</h1>
        @auth
            <a href="{{ route('movies.create') }}" class="btn btn-primary">Add New Movie</a>
        @endauth
    </div>

    @if($movies->isEmpty())
        <div class="alert alert-info">
            No movies found.
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-3 g-4">
            @foreach($movies as $movie)
                <div class="col">
                    <div class="card h-100">
                        @if($movie->poster)
                            <img src="{{ asset('storage/' . $movie->poster) }}" class="card-img-top" alt="{{ $movie->title }}">
                        @else
                            <div class="bg-secondary text-white d-flex justify-content-center align-items-center" style="height: 200px;">
                                <span class="fs-4">No Image</span>
                            </div>
                        @endif
                        <div class="card-body">
                            <h5 class="card-title">{{ $movie->title }}</h5>
                            <p class="card-text text-muted">{{ $movie->release_date->format('Y') }}</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="small">
                                    @if($movie->average_rating)
                                        <span class="text-warning">★</span> {{ number_format($movie->average_rating, 1) }}/10
                                    @else
                                        <span class="text-muted">No ratings yet</span>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge bg-primary">{{ $movie->comments_count }} {{ Str::plural('comment', $movie->comments_count) }}</span>
                                    <span class="badge bg-info">{{ $movie->ratings_count }} {{ Str::plural('rating', $movie->ratings_count) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="{{ route('movies.show', $movie->id) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $movies->links() }}
        </div>
    @endif
@endsection 