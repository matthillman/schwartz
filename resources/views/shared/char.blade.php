@if (isset($character))
<span gear="{{ $character->gear_level }}"
    stars="{{ $character->rarity }}"
    class="character"
>
    <div class="portrait">
        <img class="character round" src="/images/units/{{ $character->unit_name }}.png">
        <img class="gear" src="/images/units/gear/gear-icon-g{{ $character->gear_level }}.png">
        <div class="stars">
        @for ($i = 1; $i <=7; $i++)
            @if ($character->rarity >= $i)
            <img class="full" src="/images/units/stars/active.png">
            @else
            <img class="empty" src="/images/units/stars/inactive.png">
            @endif
        @endfor
        </div>

        @if ($character->zetas->count() > 0)
        <div class="zetas">
            <img src="/images/units/abilities/zeta.png">
            <span class="value">{{ $character->zetas->count() }}</span>
        </div>
        @endif

        <div class="level">
            <span class="value">{{ $character->level }}</span>
        </div>

    </div>
    <span>
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