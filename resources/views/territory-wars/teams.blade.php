@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">

            <div class="card">
                <div class="card-header row justify-content-between align-items-center">
                    <h2>Territory Wars Teams/Counters</h2>
                @user('edit_tw')
                    <div>
                        <form method="GET" action="{{ route('tw-teams.create') }}" >
                            @csrf
                            <div class="row add-row">
                                <button type="submit" class="btn btn-primary">{{ __('Add Team') }}</button>
                            </div>
                        </form>
                    </div>
                @enduser
                </div>
                <div class="card-body">
                    @if (session('twStatus'))
                        <div class="alert alert-success">
                            {{ session('twStatus') }}
                        </div>
                    @endif


                    <div class="guild-list">
                        <div class="row top border-bottom">
                            <div class="col-md-3">
                                <div>Team</div>
                                <div class="small-note">Aliases</div>
                            </div>
                            <div class="grow">
                                <ul>
                                    <li>
                                        <div>Coutner Team</div>
                                        <div class="small-note">Notes/strategy</div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @foreach($teams as $team)
                        <div class="row top">
                            <div class="col-md-3">
                                <h4>{{ $team->name }}</h4>
                                <div class="small-note">{{ $team->aliases }}</div>
                            </div>
                            <div class="grow">
                                <ul>
                            @foreach ($team->counters as $counter)
                                <li>
                                    <div>{{ $counter->name }}</div>
                                    <div class="small-note">{{ $counter->description }}</div>
                                </li>
                            @endforeach
                                </ul>
                            </div>
                        @user('edit_tw')
                            <div class="col-md-1">
                                <form method="GET" action="{{ route('tw-teams.edit', ['team' => $team->id]) }}" >
                                    @csrf
                                    <button type="submit" class="btn btn-primary">{{ __('Edit') }}</button>
                                </form>
                            </div>
                        @enduser
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
