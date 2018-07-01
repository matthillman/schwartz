@if (isset($character))
<span gear="{{ $character->gear_level }}"
    stars="{{ $character->rarity }}"
    class="character"
>
    <span>
        <span>{{ $character->rarity }}* g{{ $character->gear_level }}</span>
        @foreach($character->zetas as $zeta)
            <span class="zeta">{{ $zeta->class[0] }}</span>
        @endforeach
    </span>
</span>
@else
<span missing>
        <span>None</span>
</span>
@endif