@extends('layouts.app')

@section('title', 'Edit Movie: ' . $movie->title)

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h1>Edit Movie: {{ $movie->title }}</h1>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('movies.update', $movie->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            
                            <div class="mb-3">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $movie->title) }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="5" required>{{ old('description', $movie->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="release_date" class="form-label">Release Date</label>
                                <input type="date" class="form-control @error('release_date') is-invalid @enderror" id="release_date" name="release_date" value="{{ old('release_date', $movie->release_date->format('Y-m-d')) }}" required>
                                @error('release_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="runtime_minutes" class="form-label">Runtime (minutes)</label>
                                <input type="number" class="form-control @error('runtime_minutes') is-invalid @enderror" id="runtime_minutes" name="runtime_minutes" value="{{ old('runtime_minutes', $movie->runtime_minutes) }}">
                                @error('runtime_minutes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="language" class="form-label">Language</label>
                                <input type="text" class="form-control @error('language') is-invalid @enderror" id="language" name="language" value="{{ old('language', $movie->language) }}">
                                @error('language')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="poster" class="form-label">Poster Image</label>
                                @if($movie->poster)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $movie->poster) }}" alt="{{ $movie->title }}" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                @endif
                                <input type="file" class="form-control @error('poster') is-invalid @enderror" id="poster" name="poster">
                                <small class="form-text text-muted">Leave empty to keep current image</small>
                                @error('poster')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="trailer_url" class="form-label">Trailer URL</label>
                                <input type="url" class="form-control @error('trailer_url') is-invalid @enderror" id="trailer_url" name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}">
                                @error('trailer_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="budget" class="form-label">Budget ($)</label>
                                <input type="number" step="0.01" class="form-control @error('budget') is-invalid @enderror" id="budget" name="budget" value="{{ old('budget', $movie->budget) }}">
                                @error('budget')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="revenue" class="form-label">Revenue ($)</label>
                                <input type="number" step="0.01" class="form-control @error('revenue') is-invalid @enderror" id="revenue" name="revenue" value="{{ old('revenue', $movie->revenue) }}">
                                @error('revenue')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="genres" class="form-label">Genres (comma separated)</label>
                                <input type="text" class="form-control @error('genres') is-invalid @enderror" id="genres" name="genres" value="{{ old('genres', $movie->genres ? implode(', ', $movie->genres) : '') }}">
                                @error('genres')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="directors" class="form-label">Directors (comma separated)</label>
                                <input type="text" class="form-control @error('directors') is-invalid @enderror" id="directors" name="directors" value="{{ old('directors', $movie->directors ? implode(', ', $movie->directors) : '') }}">
                                @error('directors')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="artists" class="form-label">Artists (comma separated)</label>
                                <input type="text" class="form-control @error('artists') is-invalid @enderror" id="artists" name="artists" value="{{ old('artists', $movie->artists ? implode(', ', $movie->artists) : '') }}">
                                @error('artists')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('movies.show', $movie->id) }}" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Update Movie</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 