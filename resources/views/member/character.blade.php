@extends('layouts.app')
@section('title')—{{ $member->player }}—Characters @endsection
@section('content')
<div class="container member-profile member-character">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card dark-back">
                <div class="card-header glass-back row justify-content-start align-items-baseline">
                    <button type="button" @@click="back" class="btn btn-dark btn-icon back-button">
                        <ion-icon name="chevron-back-circle" size="medium"></ion-icon>
                    </button>
                    <h2>{{ $member->player }}'s {{ $character->display_name }}</h2>
                </div>
                <div class="card-body character-profile row">
                    <div class="col-6">
                        <div class="column justify-content-center character-info-wrapper">
                            <div class="row justify-content-between align-items-center portrait-wrapper {{ $character->alignment }}">
                                @include('shared.char', [
                                    'character' => $character,
                                    'noMods' => true,
                                    'noStats' => true,
                                    'size' => 'giant',
                                ])
                                <div class="relic-portrait medium {{ $character->alignment }}{{ $character->relic <= 1 ? ' locked' : '' }}">
                                    <div class="backdrop">
                                        <img src="/images/gear/{{ $character->unit->relic_image }}.png" alt="">
                                        @if($character->relic > 2)<div class="tier">{{ $character->relic - 2 }}</div>@endif
                                    </div>
                                </div>
                            </div>
                            <div class="mod-details {{ $character->alignment }}">
                                @if ($character->mods->count())
                                @foreach (['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross'] as $shape)
                                    <div>
                                        @if ($character->mods->where('slot', '=', $shape)->count())
                                            <mod :mod="{{ $character->mods->where('slot', '=', $shape)->first()->toJson() }}"></mod>
                                        @else
                                            <div class="mod missing">No {{ $shape }} equipped</div>
                                        @endif
                                    </div>
                                @endforeach
                                @else
                                    <div>No mods found</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-6">

                        <div class="stat-list column">
                        <div class="row justify-content-between align-items-baseline stat-header with-stat">
                            <div>Power</div><div>{{ format_stat($character->power, 'power') }}</div>
                        </div>
                        </div>
                        <div class="row no-margin justify-content-start align-items-start align-content-start">
                        @foreach (['stats_left', 'stats_right'] as $side)
                            <div class="stat-list column">
                            @foreach ($$side as $label => $stat)
                                @if (is_array($stat))
                                <div>
                                    <div class="row justify-content-center align-items-baseline stat-header"><div>{{ $label }}</div></div>
                                    @foreach ($stat as $l => $s)
                                    <div class="row justify-content-between">
                                        <div>{{ $l }}</div><div>{{ format_stat($character->$s, $s) }}@unless(is_null($character->modBonus($s)))<span class="bonus">({{ format_stat($character->modBonus($s), $s) }})</span>@endunless</div>
                                    </div>
                                    @endforeach
                                </div>
                                @else
                                    <div class="row justify-content-between align-items-baseline stat-header with-stat">
                                        <div>{{ $label }}</div><div>{{ format_stat($character->$stat, $stat) }}</div>
                                    </div>
                                @endif
                            @endforeach
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection