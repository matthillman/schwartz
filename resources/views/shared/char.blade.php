@if (isset($character))
<span gear="{{ $character->gear_level }}"
    stars="{{ $character->rarity }}"
>
    <span>{{ $unit->name }}</span>
    <span>{{ $character->rarity }}* g{{ $character->gear_level }}</span>
</span>
@else
<span missing>
        <span>{{ $unit->name }}</span>
        <span>None</span>
</span>
@endif