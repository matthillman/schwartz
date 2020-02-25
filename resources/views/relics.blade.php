@extends('layouts.app')
@section('viewport', '830')
@section('body-class', 'no-bg')
@section('title')—
@isset($member)
{{$member->player}} Relic Status
@else
Relic Reccomendations
@endisset
@endsection
@section('content')
<div class="container narrow-list">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-center">
                    <div class="column">
                        @isset($member)
                        <h2>{{ $member->player }}</h2>
                        <div class="note">Relic Status</div>
                        @else
                        <h2>Relic Reccomendations</h2>
                        @endisset
                    </div>
                </div>

                <div class="card-body" highlight="relic">
                    <div class="portrait-list row justify-content-center align-items-center">
                        <div relic="1" priority="1">High</div>
                        <div relic="1" priority="2">Medium</div>
                        <div relic="1" priority="3">Normal</div>
                        <div relic="1" priority="4">Low</div>
                        <div relic="1" priority="5">Situational</div>
                        @if (isset($member))
                        <div relic="0" priority="0">Bad</div>
                        @endif
                    </div>
                </div>

                @foreach($relics as $level => $chars)
                <div class="card-body" highlight="relic">
                    <div class="row justify-content-center align-items-center portrait-list-header-wrapper neutral">
                        <div class="portrait portrait-list-header">
                            <div class="character">{{ $level }}</div>
                            <div class="gear g13" style="--gear-image:url(/images/units/gear/gear-icon-g13.png);"></div>
                        </div>
                    </div>
                    <div class="row justify-content-center portrait-list">
                        @foreach ($chars as $character)
                        <div priority="{{ $character['priority'] }}" relic="{{ intval($character['unit']->relic >= ($level + 2)) }}">
                            @include('shared.char', [
                                'character' => $character['unit'],
                                'noStats' => true,
                                'noMods' => true,
                            ])
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach

                <div class="card-body">
                    <div class="row justify-content-end footer-logo">
                        <div class="logo">@include('shared.logo')</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')