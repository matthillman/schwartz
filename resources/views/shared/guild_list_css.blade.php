<style type="text/css">
    @for ($i = 1; $i <= $guilds->count(); $i++)
        .guild-list .guild:nth-child({{$i}}), .guild-list.test.test .guild.guild:nth-child({{$i}}) {
            transform: translate3d(0, calc({{ ceil($i / 2) }} * -1 * (75px + 16px)), 0);
            z-index: calc({{ $guilds->count() }} - {{ $i }} + 1);
            background-color: rgba(255, 255, 255, 1);
            opacity: 0;
        }
    @endfor
</style>