@if (isset($character))
    <character
        :character="{{ $character->toJson() }}"
        @if (isset($member))
        :member="{{ $member->toJson() }}"
        @endif
        {{ isset($noStats) ? " no-stats" : '' }}
        {{ isset($noMods) ? " no-mods" : '' }}
    ></character>
@else
<span missing class="column justify-content-center align-items-center fill-height">
@if (isset($units) && isset($base_id))
    <span>{{ $units->where('base_id', $base_id)->first()->name }}</span>
    <span>Not Unlocked</span>
@else
    <span>None</span>
@endif
</span>
@endif