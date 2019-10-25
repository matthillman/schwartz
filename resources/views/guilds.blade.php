@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">

@auth('admin')
            <div class="card">
                <div class="card-header"><h2>Add a guild</h2></div>

                <div class="card-body">
                @if (session('guildStatus'))
                    <div class="alert alert-success">
                        {{ session('guildStatus') }}
                    </div>
                @endif
                    <p>Enter the ID of the guild from its swgoh.gg URL (e.g. 3577)</p>
                    <form method="POST" action="{{ route('guild.add') }}" >
                        @csrf
                        <div class="row add-row">
                            <input type="text" name="guild">
                            <button type="submit" class="btn btn-primary">{{ __('Add Guild') }}</button>
                        </div>
                    </form>
                </div>
            </div>
@endauth

            <div class="card">
                <div class="card-header"><h2>Schwartz Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($guilds->where('schwartz', true) as $guild)
                    <div class="column">
                        <div class="row">
                            <div>
                                <div>{{ $guild->name }}</div>
                                <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
                            </div>

                            <form method="GET" action="{{ route('guild.modsList', ['guild' => $guild->id]) }}">
                                <button type="submit" class="btn btn-primary btn-icon"><span class="mod-set-image speed tier-1"></span></button>
                            </form>
                            <form method="GET" action="{{ route('guild.guild', ['guild' => $guild->id]) }}">
                                <button type="submit" class="btn btn-primary btn-icon"><i class="icon ion-ios-people"></i></button>
                            </form>
                            <a href="{{ $guild->url }}" target="_gg" class="gg-link">
                                @include('shared.bb8')
                            </a>
                            <form method="POST" action="{{ route('guild.refresh', ['guild' => $guild->id]) }}">
                                @method('PUT')
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon"><i class="icon ion-ios-refresh-circle"></i></button>
                            </form>
                        </div>
                        <div class="row guild-team-list">
                        @foreach ($squads as $squad)
                            <a class="btn" href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $squad['value']]) }}">{{ $squad['label'] }}</a>
                        @endforeach
                        </div>
                    </div>
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Other Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($guilds->where('schwartz', false) as $guild)
                        <div class="row">
                            <div>
                                <div>{{ $guild->name }}</div>
                                <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
                            </div>

                            <form method="GET" action="{{ route('guild.modsList', ['guild' => $guild->id]) }}">
                                <button type="submit" class="btn btn-primary btn-icon"><span class="mod-set-image speed tier-1"></span></button>
                            </form>
                            <form method="GET" action="{{ route('guild.guild', ['guild' => $guild->id]) }}">
                                <button type="submit" class="btn btn-primary btn-icon"><i class="icon ion-ios-people"></i></button>
                            </form>
                            <a href="{{ $guild->url }}" target="_gg" class="gg-link">
                                @include('shared.bb8')
                            </a>
                            <form method="POST" action="{{ route('guild.refresh', ['guild' => $guild->id]) }}">
                                @method('PUT')
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon"><i class="icon ion-ios-refresh-circle"></i></button>
                            </form>
                        </div>
                        <div class="row guild-team-list">
                        @foreach ($squads as $squad)
                            <a class="btn" href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $squad['value']]) }}">{{ $squad['label'] }}</a>
                        @endforeach
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')