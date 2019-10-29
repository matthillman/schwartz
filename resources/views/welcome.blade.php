@extends('layouts.html')

@section('body-class', 'no-scroll')
@section('viewport', '600')

@push('styles')
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
                    :icon="'{{ $guild->icon_name }}'"
                    :gp="'{{ intval(floor($guild->gp / 1000000)) }}M'"
                    :raid="'{{ $guild->raid_tag }}'"
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
                <p>Connect to The Scwartzies recruiting</p>
                <iframe src="https://discordapp.com/widget?id=576216200158248969&theme=dark&username=Frax#4201&chetmanly#3351" width="350" height="250" allowtransparency="true" frameborder="0"></iframe>
            </div>
        </div>
    </welcome-parallax>
</div>
@endsection