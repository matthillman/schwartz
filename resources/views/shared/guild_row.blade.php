<div class="column">
    <div class="row">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" v-model="guildCompare" value="{{ $guild->guild_id }}" :disabled="guildCompare.length >= 2 && !guildCompare.includes('{{ $guild->guild_id }}')">
        </div>
        <div class="grow">
            <div>{{ $guild->name }}</div>
            <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
        </div>
        <popover class="teams" name="teams-{{ $guild->id }}">
            <div slot="face">
                <button class="btn btn-primary btn-icon with-text"><i class="icon ion-ios-list"></i><span>Teams</span></button>
            </div>
            <div slot="content">
                <ul>
                @foreach ($squads as $squad)
                    <li><a href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $squad['value'], 'mode' => 'guild', 'index' => 0]) }}">{{ $squad['label'] }}</a></li>
                @endforeach
                </ul>
            </div>
        </popover>

        <form method="GET" action="{{ route('guild.modsList', ['guild' => $guild->id]) }}">
            <button type="submit" class="btn btn-primary btn-icon"><span class="mod-set-image speed tier-6"></span></button>
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
</div>