@extends('layouts.html')

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
            <div class="content-title">Our Guilds</div>
            <div class="guild-list">
                <guild
                    :name="'Return of the Schwartz'"
                    :url="'https://swgoh.gg/g/3577/return-of-the-schwartz/'"
                    :icon="'mandalorian'"
                    :gp="'148M'"
                    :raid="'Heroic Sith'"
                ></guild>
                <guild
                    :name="'theSCHWARTZ'"
                    :url="'https://swgoh.gg/g/2238/theschwartz/'"
                    :icon="'sabine'"
                    :gp="'135M'"
                    :raid="'Heroic Sith'"
                ></guild>
                <guild
                    :name="'Schwartz Holiday Special'"
                    :url="'https://swgoh.gg/g/29865/schwartz-holiday-special/'"
                    :icon="'senate'"
                    :gp="'125M'"
                    :raid="'Heroic AAT'"
                ></guild>
                <guild
                    :name="'The Phantom Schwartz'"
                    :url="'https://swgoh.gg/g/11339/the-phantom-schwartz/'"
                    :icon="'senate'"
                    :gp="'90M'"
                    :raid="'Heroic AAT'"
                ></guild>
                <guild
                    :name="'The Clone Schwartz'"
                    :url="'https://swgoh.gg/g/30376/the-cione-schwartz/'"
                    :icon="'niteowl'"
                    :gp="'89M'"
                    :raid="'Heroic AAT'"
                ></guild>
                <guild
                    :name="'A New Schwartz'"
                    :url="'https://swgoh.gg/g/8545/a-new-schwartz/'"
                    :icon="'blast'"
                    :gp="'47M'"
                    :raid="'Heroic AAT'"
                ></guild>
            </div>
        </div>
        <div slot="sections" id="join">
            <div class="content-title">Join Us</div>
            <div>This will be a form where you enter your contact information</div>
        </div>
    </welcome-parallax>
</div>
@endsection