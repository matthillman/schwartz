<div class="column">
    <div class="row">
        <div>
            <div>{{ $guild->name }}</div>
            <div class="small-note">{{ intval(floor($guild->gp / 1000000)) }}M</div>
        </div>

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
    <div class="row guild-team-list">
    @foreach ($squads as $squad)
        <a class="btn" href="{{ route('guild.members', ['guild' => $guild->id, 'team' => $squad['value']]) }}">{{ $squad['label'] }}</a>
    @endforeach
    </div>
</div>