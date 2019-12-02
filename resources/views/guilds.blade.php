@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
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

            <div class="card">
                <div class="card-header"><h2>Schwartz Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($guilds->where('schwartz', true) as $guild)
                        @include('shared.guild_row', [ 'guild' => $guild, 'squads' => $squads])
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Other Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($guilds->where('schwartz', false) as $guild)
                        @include('shared.guild_row', [ 'guild' => $guild, 'squads' => $squads])
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')