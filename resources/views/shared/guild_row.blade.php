<div class="column">
    <div class="row cut-corner">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" v-model="guildCompare" value="{{ $guild->guild_id }}" :disabled="guildCompare.length >= 2 && !guildCompare.includes('{{ $guild->guild_id }}')">
        </div>
        <div class="grow">
            <div>{{ $guild->name }}</div>
            <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
        </div>

        <span class="status-indicator" v-if="guildJobStatusByGuildId[{{ $guild->guild_id }}]">
            <svg v-if="guildJobStatusByGuildId[{{ $guild->guild_id }}] == 'completed'" class="fill-success" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
            </svg>

            <svg v-if="guildJobStatusByGuildId[{{ $guild->guild_id }}] == 'reserved' || guildJobStatusByGuildId[{{ $guild->guild_id }}] == 'pending'" class="fill-warning" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
            </svg>

            <svg v-if="guildJobStatusByGuildId[{{ $guild->guild_id }}] == 'failed'" class="fill-danger" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
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
                        <a href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $squad['value'], 'mode' => 'guild', 'index' => 0]) }}">{{ $squad['label'] }}</a>
                        @endif
                    </li>
                @endforeach
                </ul>
            </template>
        </popup>

        <button @@click="go(`{{ route('guild.modsList', ['guild' => $guild->id]) }}`)" class="btn btn-primary btn-icon striped"><span class="mod-set-image speed tier-6"></span></button>
        <button @@click="go(`{{ route('guild.guild', ['guild' => $guild->id]) }}`)" class="btn btn-primary btn-icon striped"><ion-icon name="people" size="medium"></ion-icon></button>
        <a href="{{ $guild->url }}" target="_gg" class="gg-link striped round">
            @include('shared.bb8')
        </a>
        <form method="POST" action="{{ route('guild.refresh', ['guild' => $guild->id]) }}">
            @method('PUT')
            @csrf
            <button type="submit" class="btn btn-primary btn-icon striped"><ion-icon name="refresh" size="medium"></ion-icon></button>
        </form>
    </div>
</div>