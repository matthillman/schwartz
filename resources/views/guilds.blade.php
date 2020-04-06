@extends('layouts.app')
@section('title', '—Guilds')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('shared.status');
            <div class="card radiant-back">
                <div class="card-header"><h2>Compare Guilds</h2></div>

                <div class="card-body">
                    <p>Check the boxes below or enter either the ID of each guild from its swgoh.gg URL (e.g. 3577) or an ally code of someone in the guild</p>
                    <form method="POST" action="{{ route('guild.post.compare') }}" >
                        @csrf
                        <div class="row add-row input-group">
                            <input class="form-control" type="text" name="guild1" v-model="guildCompare[0]">
                            <span class="align-self-center">vs</span>
                            <input class="form-control" type="text" name="guild2" v-model="guildCompare[1]">
                            <button type="submit" class="btn btn-primary striped"><span>{{ __('Compare') }}</span></button>
                        </div>
                    </form>
                </div>
            </div>

            @user('accounts')
                <div class="card radiant-back">
                    <div class="card-header"><h2>Your Guilds</h2></div>

                    <div class="card-body">
                        <div class="guild-list">
                        @forelse(auth()->user()->accounts->pluck('guild') as $guild)
                            @include('shared.guild_row', [ 'guild' => $guild, 'squads' => $squads])
                        @empty
                            <div>No guilds found for the current user 😞</div>
                        @endforelse
                        </div>
                    </div>
                </div>
            @enduser

            <div class="card radiant-back">
                <div class="card-header"><h2>Schwartz Guilds</h2></div>

                <div class="card-body">
                    <div class="guild-list">
                    @foreach($schwartz as $guild)
                        @include('shared.guild_row', [ 'guild' => $guild, 'squads' => $squads])
                    @endforeach
                    </div>
                </div>
            </div>

            <div class="card radiant-back">
                <div class="card-header"><h2>Other Guilds</h2></div>
                <div class="card-body guild-list">
                <search :url="'{{ route('search.guilds') }}'" :help-note="`Searches guild name and guild ID`" v-slot="result">
                    <div class="row cut-corner">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" v-model="guildCompare" :value="result.item.guild_id" :disabled="guildCompare.length >= 2 && !guildCompare.includes(result.item.guild_id)">
                        </div>
                        <div class="grow">
                            <div>@{{ result.item.name }}</div>
                            <div class="small-note">@{{ Math.floor(result.item.gp / 1000000) }}M</div>
                        </div>

                        <span class="status-indicator" v-if="guildJobStatusByGuildId[result.item.guild_id]">
                            <svg v-if="guildJobStatusByGuildId[result.item.guild_id] == 'completed'" class="fill-success" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                            </svg>

                            <svg v-if="guildJobStatusByGuildId[result.item.guild_id] == 'reserved' || guildJobStatusByGuildId[result.item.guild_id] == 'pending'" class="fill-warning" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                            </svg>

                            <svg v-if="guildJobStatusByGuildId[result.item.guild_id] == 'failed'" class="fill-danger" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                            </svg>
                        </span>

                        <popup>
                            <button class="btn btn-primary btn-icon with-text striped"><ion-icon name="list" size="medium"></ion-icon><span>Teams</span></button>

                            <template #menu>
                                <ul>
                                @foreach ($squads as $squad)
                                    <li>
                                        @if (array_get($squad, 'separator', false))
                                        <strong>{{ $squad['label'] }}</strong>
                                        @else
                                        <a :href="`/guild/${result.item.id}/{{ $squad['value'] }}/0`">{{ $squad['label'] }}</a>
                                        @endif
                                    </li>
                                @endforeach
                                </ul>
                            </template>
                        </popup>

                        <button @@click="go(`/guild/${result.item.id}/mods`)" class="btn btn-primary btn-icon striped"><span class="mod-set-image speed tier-6"></span></button>
                        <button @@click="go(`/guild/${result.item.id}`)" class="btn btn-primary btn-icon striped"><ion-icon name="people" size="medium"></ion-icon></button>
                        <a :href="result.item.url" target="_gg" class="gg-link striped round">
                            @include('shared.bb8')
                        </a>
                        <form method="POST" :action="`/guild/${result.item.id}/refresh`">
                            @method('PUT')
                            @csrf
                            <button type="submit" class="btn btn-primary btn-icon striped"><ion-icon name="refresh" size="medium"></ion-icon></button>
                        </form>
                    </div>
                </search>
                </div>
                <div class="card-header"><h2>Add a guild</h2></div>

                <div class="card-body">
                    <p>Enter either the ID of the guild from its swgoh.gg URL (e.g. 3577) or an ally code of someone in the guild</p>
                    <form method="POST" action="{{ route('guild.add') }}" >
                        @csrf
                        <div class="row add-row input-group">
                            <input class="form-control" type="text" name="guild">
                            <button type="submit" class="btn btn-primary">{{ __('Add Guild') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')