@extends('layouts.app')
@section('body-class', 'no-bg')
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">

                    <div class="card-body" highlight="{{$highlight}}" v-highlight:[highlight]>
                        <div class="row justify-content-between align-items-baseline">
                            <h1>
                                <a href="https://swgoh.gg{{ $member->url }}" target="_gg">
                                    <span>{{ $member->player }}</span>
                                </a>
                            </h1>

                            @bot
                            <div class="note">
                                Highlighting based on <strong>{{$highlight}}</strong>
                            </div>
                            @else
                            <highlight-widget :starting="'{{$highlight}}'"></highlight-widget>
                            @endbot
                        </div>

                        @foreach($teams as $title => $team)
                            @include('shared.unit_table', [
                                'team' => $title,
                                'characters' => $team
                            ])
                        @endforeach

                        @bot
                        <div class="row justify-content-end footer-logo">
                            <div class="logo">@include('shared.logo')</div>
                        </div>
                        @endbot
                    </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')