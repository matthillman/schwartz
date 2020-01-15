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

                @foreach($members as $member)
                    <div class="card-body" highlight="{{$highlight}}" v-highlight:[highlight]>
                        <div class="row justify-content-between align-items-center">
                            <div class="row align-items-center">
                                <form method="GET" action="{{ route('member.teams', ['allyCode' => $member->ally_code, 'team' => $team]) }}">
                                    <button type="submit" class="btn btn-primary btn-icon inverted"><i class="icon ion-ios-link"></i></button>
                                </form>
                                <h1>
                                    <a href="https://swgoh.gg{{ $member->url }}" target="_gg">
                                        <span>{{ $member->player }}</span>
                                    </a>
                                </h1>
                            </div>

                            <highlight-widget :starting="'{{$highlight}}'"></highlight-widget>
                        </div>

                        @foreach($teams as $title => $squad)
                            @include('shared.unit_table', [
                                'team' => $title,
                                'characters' => $squad->pluck('base_id')->all(),
                                'member_characters' => $member->characterSet($squad->pluck('base_id')->all())['characters'],
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