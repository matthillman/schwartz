@extends('layouts.app')
@section('title')—{{ $guild->name }}@endsection
@section('body-class', 'no-bg')
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-center">
                    <div class="column">
                        <h2>{{ $title }}</h2>
                        <div class="note">{{ $guild->name }}</div>
                    </div>

                    <div>
                        <form method="GET" action="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team, 'mode' => 'members']) }}" >
                            <div class="row add-row">
                                <button type="submit" class="btn btn-primary">{{ __('Group by Member') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if (count($teamKeys) > 1)
                <div class="squad-switcher row justify-content-between align-items-center">
                    <a class="btn{{ $selected > count($teamKeys) ? ' selected' : '' }}" href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team ]) }}">All</a>
                    @foreach ($teamKeys as $index => $key)
                    <a class="btn{{ $selected === $index ? ' selected' : '' }}" href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team, 'mode' => 'guild', 'index' => $index]) }}">{{ $key }}</a>
                    @endforeach
                </div>
                @endif

                @foreach($teams as $title => $team)
                <div class="card-body" highlight="{{ $highlight }}" v-highlight:[highlight]>
                    <team-sort
                        v-bind:units="{{ $team->values()->toJson() }}"
                        v-bind:members="{{ $members->map(function($m) use ($team) { return $m->characterSet($team->pluck('base_id')->all()); })->toJson() }}"
                    >
                        <div class="row justify-content-between align-items-center">
                            <h1>{{ $title }}</h1>

                            <highlight-widget :starting="'{{$highlight}}'"></highlight-widget>
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