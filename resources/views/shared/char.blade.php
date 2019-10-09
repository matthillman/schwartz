@if (isset($character))
<span gear="{{ $character->gear_level }}"
    stars="{{ $character->rarity }}"
    power="{{ $character->highlight_power }}"
    class="character {{ $character->alignment }}"
>
    <div class="portrait{{ $character->is_ship ? ' ship' : '' }}{{ $character->is_capital_ship ? ' capital' : ''}}">
        @if ($character->is_ship)
            <div class="ship-wrapper">
                <img class="character" src="/images/units/{{ $character->unit_name }}.png">
            </div>
            <div class="gear g{{ $character->gear_level }}" style="--gear-image: url('/images/units/gear/{{ $character->is_capital_ship ? 'capital-' : ''}}ship-frame.svg')"></div>
        @else
            <img class="character round" src="/images/units/{{ $character->unit_name }}.png">
            <div class="gear g{{ $character->gear_level }}" style="--gear-image: url('/images/units/gear/gear-icon-g{{ $character->gear_level }}.png')"></div>
        @endif
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

        @if ($character->relic > 1)
        <div class="relic">
            <span class="value">{{ $character->relic - 2 }}</span>
        </div>
        @endif

        <div class="level">
            <span class="value">{{ $character->level }}</span>
        </div>

    </div>
    <span class="zeta-list">
    @foreach($character->zetas as $zeta)
        <span class="zeta">{{ $zeta->class[0] }}</span>
    @endforeach
    @if ($character->speed > 0)
    @endif
    </span>
    <div class="stat-container">
        <div class="stat-wrapper"><span class="stat">{{ $character->speed }}</span><span class="mod-set-image speed tier-5 mini"></span></div>
        @foreach($character->key_stats as $key => $stat)
            <div class="stat-wrapper"><span class="stat">{{ $stat }}</span><span class="mod-set-image {{ $key }} tier-5 mini"></span></div>
        @endforeach
    </div>
</span>
@else
<span missing>
    <span>None</span>
</span>
@endif