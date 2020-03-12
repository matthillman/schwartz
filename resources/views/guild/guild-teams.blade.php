@extends('layouts.app')
@section('title')—{{ $guild->name }}@endsection
@section('body-class', 'no-bg')
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-12">
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

                @if ($teamKeys->count() > 1)
                <tab-list
                    :all-route="`{{ route('guild.members', ['guild' => $guild->id, 'team' => $team ]) }}`"
                    :tabs="{{ $teamKeys->toJson() }}"
                    :selected="{{ $selected }}"
                    @@changed="tab => go(`/guild/{{$guild->id}}/{{$team}}/guild/${tab.index}`)"
                ></tab-list>
                @endif

                @foreach($teams as $title => $team)
                <div class="card-body" highlight="{{ $highlight }}" v-highlight:[highlight]>
                    <team-sort
                        v-bind:units="{{ $team->values()->toJson() }}"
                        v-bind:members="{{ $members->map(function($m) use ($team) { return $m->characterSet($team->pluck('base_id')->all()); })->toJson() }}"
                    >
                        <div class="row no-margin justify-content-between align-items-center">
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