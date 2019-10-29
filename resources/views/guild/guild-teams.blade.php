@extends('layouts.app')
@section('body-class', 'no-bg')
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-center">
                    <h2>Guild Teams</h2>

                    <div>
                        <form method="GET" action="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team, 'mode' => 'members']) }}" >
                            <div class="row add-row">
                                <button type="submit" class="btn btn-primary">{{ __('Group by Member') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                @foreach($teams as $title => $team)
                <div class="card-body" highlight="{{$highlight}}">
                    <team-sort
                        units="{{ $team->values()->toJson() }}"
                        members="{{ $members->map(function($m) use ($team) { return $m->characterSet($team->pluck('base_id')->all()); })->toJson() }}"
                    >
                        <div class="row justify-content-between align-items-center">
                            <h1>{{ $title }}</h1>
                            <div class="note">
                                Highlighting based on <strong>{{$highlight}}</strong>
                            </div>
                        </div>
                    </team-sort>
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')