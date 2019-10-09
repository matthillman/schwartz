@extends('layouts.app')
@section('body-class', 'no-bg')
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card stripes">
                <div class="card-header row justify-content-between align-items-center">
                    <h2>Guild Teams by Member</h2>

                    <div>
                        <form method="GET" action="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team]) }}" >
                            <div class="row add-row">
                                <button type="submit" class="btn btn-primary">{{ __('Group by Team') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="note">
                    Highlighting based on <strong>{{$highlight}}</strong>
                </div>

                @foreach($members as $member)
                    <div class="card-body" highlight="{{$highlight}}">
                        <h1>
                            <a href="https://swgoh.gg{{ $member->url }}" target="_gg">
                                {{ $member->player }}
                            </a>
                        </h1>

                        @foreach($teams as $title => $team)
                            @include('shared.unit_table', [
                                'team' => $title,
                                'characters' => $team
                            ])
                        @endforeach
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')