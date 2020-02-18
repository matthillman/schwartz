@extends('layouts.app')
@section('title', '—Guilds')
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
                    <p>Enter either the ID of the guild from its swgoh.gg URL (e.g. 3577) or an ally code of someone in the guild</p>
                    <form method="POST" action="{{ route('guild.add') }}" >
                        @csrf
                        <div class="row add-row">
                            <input type="text" name="guild">
                            <button type="submit" class="btn btn-primary">{{ __('Add Guild') }}</button>
                        </div>
                    </form>
                </div>

                <div class="card-header"><h2>Compare Guilds</h2></div>

                <div class="card-body">
                    <p>Check the boxes below or enter either the ID of each guild from its swgoh.gg URL (e.g. 3577) or an ally code of someone in the guild</p>
                    <form method="POST" action="{{ route('guild.post.compare') }}" >
                        @csrf
                        <div class="row add-row">
                            <input type="text" name="guild1" v-model="guildCompare[0]">
                            <span class="align-self-center">vs</span>
                            <input type="text" name="guild2" v-model="guildCompare[1]">
                            <button type="submit" class="btn btn-primary">{{ __('Compare') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Schwartz Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($schwartz as $guild)
                        @include('shared.guild_row', [ 'guild' => $guild, 'squads' => $squads])
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h2>Other Guilds</h2></div>
                <div class="card-body guild-list">
                <search :url="'{{ route('search.guilds') }}'" :help-note="`Searches guild name and guild ID`" v-slot="result">
                    <div class="row">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" v-model="guildCompare" :value="result.item.guild_id" :disabled="guildCompare.length >= 2 && !guildCompare.includes(result.item.guild_id)">
                        </div>
                        <div class="grow">
                            <div>@{{ result.item.name }}</div>
                            <div class="small-note">@{{ Math.floor(result.item.gp / 1000000) }}M</div>
                        </div>
                        <popover class="teams" :name="`teams-${ result.item.id }`">
                            <div slot="face">
                                <button class="btn btn-primary btn-icon with-text"><ion-icon name="list" size="medium"></ion-icon><span>Teams</span></button>
                            </div>
                            <div slot="content">
                                <ul>
                                @foreach ($squads as $squad)
                                    <li><a :href="`/guild/${result.item.id}/{{ $squad['value'] }}/0`">{{ $squad['label'] }}</a></li>
                                @endforeach
                                </ul>
                            </div>
                        </popover>

                        <form method="GET" :action="`/guild/${result.item.id}/mods`">
                            <button type="submit" class="btn btn-primary btn-icon"><span class="mod-set-image speed tier-6"></span></button>
                        </form>
                        <form method="GET" :action="`/guild/${result.item.id}`">
                            <button type="submit" class="btn btn-primary btn-icon"><ion-icon name="people" size="medium"></ion-icon></button>
                        </form>
                        <a :href="result.item.url" target="_gg" class="gg-link">
                            @include('shared.bb8')
                        </a>
                        <form method="POST" :action="`/guild/${result.item.id}/refresh`">
                            @method('PUT')
                            @csrf
                            <button type="submit" class="btn btn-primary btn-icon"><ion-icon name="refresh" size="medium"></ion-icon></button>
                        </form>
                    </div>
                </search>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')