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

        <!-- Demographic Statistics Section -->
        <h2 class="mt-4 mb-3">Demographic Analysis</h2>
        
        <div class="row">
            <!-- Gender Statistics -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ratings by Gender</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($statistics['demographics']['gender']))
                            <div class="row">
                                @foreach($statistics['demographics']['gender'] as $gender => $data)
                                    @if($data['count'] > 0)
                                        <div class="col-md-6 mb-3">
                                            <div class="card">
                                                <div class="card-body text-center">
                                                    <h6 class="card-title text-capitalize">{{ $gender == 'unspecified' ? 'Not Specified' : $gender }}</h6>
                                                    <div class="display-6 text-warning">★ {{ number_format($data['average'], 1) }}</div>
                                                    <p class="text-muted">{{ $data['count'] }} {{ Str::plural('rating', $data['count']) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <p class="text-center">No gender data available</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Age Group Statistics -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ratings by Age Group</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($statistics['demographics']['age']))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Age Group</th>
                                            <th>Average Rating</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statistics['demographics']['age'] as $ageGroup => $data)
                                            @if($data['count'] > 0)
                                                <tr>
                                                    <td>
                                                        @switch($ageGroup)
                                                            @case('under_18')
                                                                Under 18
                                                                @break
                                                            @case('18_24')
                                                                18-24
                                                                @break
                                                            @case('25_34')
                                                                25-34
                                                                @break
                                                            @case('35_44')
                                                                35-44
                                                                @break
                                                            @case('45_54')
                                                                45-54
                                                                @break
                                                            @case('55_plus')
                                                                55+
                                                                @break
                                                            @case('unknown')
                                                                Not Specified
                                                                @break
                                                        @endswitch
                                                    </td>
                                                    <td><span class="text-warning">★</span> {{ number_format($data['average'], 1) }}</td>
                                                    <td>{{ $data['count'] }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center">No age data available</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Geographic Statistics -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ratings by Continent</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($statistics['demographics']['geography']['continents']))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Continent</th>
                                            <th>Average Rating</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statistics['demographics']['geography']['continents'] as $continent => $data)
                                            @if($data['count'] > 0)
                                                <tr>
                                                    <td>{{ $continent == 'unspecified' ? 'Not Specified' : $continent }}</td>
                                                    <td><span class="text-warning">★</span> {{ number_format($data['average'], 1) }}</td>
                                                    <td>{{ $data['count'] }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center">No continent data available</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Country Statistics -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Ratings by Country (Top Countries)</h5>
                    </div>
                    <div class="card-body">
                        @if(isset($statistics['demographics']['geography']['countries']))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Country</th>
                                            <th>Average Rating</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($statistics['demographics']['geography']['countries'] as $country => $data)
                                            @if($data['count'] > 0)
                                                <tr>
                                                    <td>{{ $country == 'unspecified' ? 'Not Specified' : $country }}</td>
                                                    <td><span class="text-warning">★</span> {{ number_format($data['average'], 1) }}</td>
                                                    <td>{{ $data['count'] }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-center">No country data available</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 