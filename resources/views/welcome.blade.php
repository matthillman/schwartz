@extends('layouts.html')

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
                @if (session('inquireStatus'))
                <div class="form-group row justify-content-center">
                    <div class="alert alert-success col-md-3">
                        {{ session('inquireStatus') }}
                    </div>
                </div>
                @else
                <form method="POST" action="{{ route('join.inquiry') }}">
                    @csrf
                    <div class="form-group row justify-content-center">
                        <label for="discord" class="col-md-3 col-form-label text-md-right">{{ __('Discord ID') }}</label>

                        <div class="col-md-4">
                            <input id="discord" type="text"
                                class="form-control{{ $errors->has('discord') ? ' is-invalid' : '' }}"
                                name="discord" value="{{ old('discord') }}"
                                placeholder="Name#0000"
                                required>

                            @if ($errors->has('discord'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('discord') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row justify-content-center">
                        <label for="url" class="col-md-3 col-form-label text-md-right">{{ __('URL of your swgoh.gg profile') }}</label>

                        <div class="col-md-4">
                            <input id="url" type="text"
                                class="form-control{{ $errors->has('url') ? ' is-invalid' : '' }}"
                                name="url" value="{{ old('url') }}"
                                required>

                            @if ($errors->has('url'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('url') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row justify-content-center">
                        <label for="referral" class="col-md-3 col-form-label text-md-right">{{ __('How did you hear about us?') }}</label>

                        <div class="col-md-4">
                            <input id="referral" type="text"
                                class="form-control{{ $errors->has('referral') ? ' is-invalid' : '' }}"
                                name="referral" value="{{ old('referral') }}"
                                >

                            @if ($errors->has('referral'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('referral') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row justify-content-center">
                        <label for="pitch" class="col-md-3 col-form-label text-md-right">{{ __('Why The Schwartzies?') }}</label>

                        <div class="col-md-4">
                            <input id="referral" type="text"
                                class="form-control{{ $errors->has('pitch') ? ' is-invalid' : '' }}"
                                name="pitch" value="{{ old('pitch') }}"
                                >

                            @if ($errors->has('pitch'))
                                <span class="invalid-feedback">
                                    <strong>{{ $errors->first('pitch') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group justify-content-center  row mb-0">
                        <button type="submit" class="btn btn-primary">
                            {{ __('Submit') }}
                        </button>
                    </div>
                </form>
                @endif
            </div>
        </div>
    </welcome-parallax>
</div>
@endsection