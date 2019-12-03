@if (isset($character))
    <character :character="{{ $character->toJson() }}"{{ isset($noStats) ? " no-stats" : '' }}{{ isset($noMods) ? " no-mods" : '' }}></character>
@else
<span missing>
    <span>None</span>
</span>
@endif