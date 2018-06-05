@if (isset($character))
<span gear="{{ $character->gear_level }}"
    stars="{{ $character->rarity }}"
>
    <span>{{ $character->rarity }}* g{{ $character->gear_level }}</span>
</span>
@else
<span missing>
        <span>None</span>
</span>
@endif