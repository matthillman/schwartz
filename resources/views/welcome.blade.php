@extends('layouts.html')

@section('body-class', 'no-scroll')
@section('viewport', '600')

@push('styles')
    @include('shared.guild_list_css', ['guilds' => $guilds])
    <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
@endpush

@section('body')
<div id="app">
    <welcome-parallax>
        <div slot="sections" id="main">
            <div class="logo hero">@include('shared.logo')</div>
            <div class="description">A family of guilds playing <a href="https://www.ea.com/games/starwars/galaxy-of-heroes" target="_blank">Star Wars Galaxy of Heroes</a></div>
            <div class="nav-links">
                <a href="#guilds">Our Guilds</a>
                <a href="#join">Join Us</a>
            </div>
        </div>

        <div slot="sections" id="guilds">
            <div class="narrow-links">
                <a href="#main">Main</a>
                <a href="#join">Join Us</a>
            </div>
            <div class="content-title">Our Guilds</div>
            <div class="guild-list">
                @foreach ($guilds as $guild)
                <guild
                    :name="'{{ $guild->name }}'"
                    :url="'{{ $guild->url }}'"
                    :icon="'{{ asset("storage/$guild->icon.png") }}'"
                    :gp="'{{ intval(floor($guild->gp / 1000000)) }}M'"
                    :tb="'{{ $guild->stars }}'"
                    :focus="'{{ $guild->focus }}'"
                    :raids="'{{ $guild->raids }}'"
                    ></guild>
                @endforeach
            </div>
        </div>

        <div slot="sections" id="join">
            <div class="narrow-links">
                <a href="#main">Main</a>
                <a href="#guilds">Our Guilds</a>
            </div>
            <div class="content-title">Join Us</div>
            <div>
                <p>Connect to The Scwartzies</p>
                <discord-widget
                    :server="'316691456204996608'"
                    :invite="'KZ5tvRU'"
                >
                    @include('shared.discord')
                </discord-widget>
            </div>
        </div>
    </welcome-parallax>
</div>
@endsection