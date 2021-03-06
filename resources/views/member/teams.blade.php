@extends('layouts.app')
@section('body-class', 'no-bg')
@section('title')—{{ $member->player }} Teams @endsection
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card radiant-back">

                    <div class="card-body" highlight="{{$highlight}}" v-highlight:[highlight]>
                        <div class="row justify-content-between align-items-baseline">
                            <h1>
                                <a href="{{ route('member.profile', ['ally' => $member->ally_code]) }}">
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