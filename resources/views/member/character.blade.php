@extends('layouts.app')
@section('title')—{{ $member->player }}—Characters @endsection
@section('content')
<div class="container member-profile member-character">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card dark-back">
                <div class="card-header row justify-content-start align-items-baseline">
                    @include('shared.back')
                    <div class="column">
                        <h2>{{ $character->display_name }}</h2>
                        <div class="small-note">{{ $member->player }}</div>
                    </div>
                </div>
                <div class="card-body character-profile row">
                    <div class="col-6">
                        <div class="column justify-content-center character-info-wrapper">
                            <div class="row justify-content-between {{ $character->is_ship ? 'align-items-start ship' : 'align-items-center char' }} portrait-wrapper {{ $character->alignment }}">
                                @if ($character->is_ship)
                                <div class="column">
                                    @foreach ($character->crew_display_list->slice(0, 2) as $crew)
                                        <div class="row no-margin justify-content-between align-items-center pilot-wrapper left glass-back">
                                            <div class="ability-wrapper {{ $crew['character']->alignment }}">
                                                <tooltip>
                                                    <img class="ability" src="/images/units/skills/{{ $crew['id'] }}.png">
                                                    @isset($crew['image'])
                                                        <img class="ability-flair" src="/images/units/abilities/{{ $crew['image'] }}.png">
                                                    @elseif($crew['tier'] >= 0)
                                                        <div class="ability-flair"><div class="value">{{ $crew['tier'] + 1 }}</div></div>
                                                    @endisset
                                                    <template #tooltip>
                                                        <h4 class="whitespace-nowrap">{{ $crew['name'] }}</h4>
                                                        <p class="ability-description">{!! $crew['description'] !!}</p>
                                                    </template>
                                                </tooltip>
                                            </div>
                                            @include('shared.char', [
                                                'character' => $crew['character'],
                                                'noMods' => true,
                                                'noStats' => true,
                                                'size' => 'medium',
                                            ])
                                        </div>
                                    @endforeach
                                </div>
                                @endif
                                @include('shared.char', [
                                    'character' => $character,
                                    'noMods' => true,
                                    'noStats' => true,
                                    'size' => 'giant',
                                ])
                                @if ($character->is_char)
                                    <div class="relic-portrait medium {{ $character->alignment }}{{ $character->relic <= 1 ? ' locked' : '' }}">
                                        <div class="backdrop">
                                            <img src="/images/gear/{{ $character->unit->relic_image }}.png" alt="">
                                            @if($character->relic > 2)<div class="tier">{{ $character->relic - 2 }}</div>@endif
                                        </div>
                                    </div>
                                @else
                                <div class="column">
                                    @if($character->crew_display_list->count() > 2)
                                    <div class="row no-margin justify-content-between align-items-center pilot-wrapper right glass-back">
                                        @foreach ($character->crew_display_list->slice(2) as $crew)
                                            @include('shared.char', [
                                                'character' => $crew['character'],
                                                'noMods' => true,
                                                'noStats' => true,
                                                'size' => 'medium',
                                            ])
                                            <div class="ability-wrapper {{ $crew['character']->alignment }}">
                                                <tooltip>
                                                    <img class="ability" src="/images/units/skills/{{ $crew['id'] }}.png">
                                                    @isset($crew['image'])
                                                        <img class="ability-flair" src="/images/units/abilities/{{ $crew['image'] }}.png">
                                                    @elseif($crew['tier'] >= 0)
                                                        <div class="ability-flair"><div class="value">{{ $crew['tier'] + 1 }}</div></div>
                                                    @endisset
                                                    <template #tooltip>
                                                        <h4 class="whitespace-nowrap">{{ $crew['name'] }}</h4>
                                                        <p class="ability-description">{!! $crew['description'] !!}</p>
                                                    </template>
                                                </tooltip>
                                            </div>
                                        @endforeach
                                    </div>
                                    @endif
                                </div>
                                @endif
                            </div>
                            <div class="row no-margin justify-content-center align-items-center ability-row">
                                @foreach ($character->skill_display_list as $skill)
                                    <div class="ability-wrapper {{ $character->alignment }}{{ array_get($skill, 'ultimate', false) ? ' ultimate' : '' }}">
                                        <tooltip>
                                            <div class="ability{{ $skill['tier'] == -1 ? ' locked' : '' }}">
                                                <img class="ability" src="/images/units/skills/{{ $skill['id'] }}.png">
                                            </div>
                                            @isset($skill['image'])
                                                <img class="ability-flair" src="/images/units/abilities/{{ $skill['image'] }}.png">
                                            @elseif(($skill['tier'] ?: -1) >= 0)
                                                <div class="ability-flair"><div class="value">{{ $skill['tier'] + 1 }}</div></div>
                                            @endisset
                                            <template #tooltip>
                                                <h4 class="whitespace-nowrap">{{ $skill['name'] }}</h4>
                                                <p class="ability-description">{!! $skill['description'] !!}</p>
                                            </template>
                                        </tooltip>
                                    </div>
                                @endforeach
                            </div>
                            @if ($character->is_char)
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
                            @endif
                            <div class="items-needed stat-list column">
                                <div class="row justify-content-center align-items-baseline stat-header"><div>Materials Needed to max Abilities</div></div>
                                @forelse ($character->ability_materials_needed as $icon => $amount)
                                    <div class="row justify-content-between">
                                        <img src="/images/units/abilities/{{ $icon }}.png">
                                        <span>{{ number_format($amount) }}</span>
                                    </div>
                                @empty
                                <div class="row justify-content-between">
                                    <div>Abilities Maxed!</div>
                                </div>
                                @endforelse
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
                                    <div class="row justify-content-center align-items-baseline stat-header"><div>{{ $label }}</div></div>
                                    @foreach ($stat as $l => $s)
                                    <div class="row justify-content-between">
                                        <div>{{ $l }}</div><div>{{ format_stat($character->$s, $s) }}@unless(is_null($character->modBonus($s)))<span class="bonus">({{ format_stat($character->modBonus($s), $s) }})</span>@endunless</div>
                                    </div>
                                    @endforeach
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
                <div class="card-footer">
                    @bot
                    <div class="row justify-content-end footer-logo">
                        <div class="logo">@include('shared.logo')</div>
                    </div>
                    @endbot
                </div>
            </div>

        </div>
    </div>
</div>
@endsection