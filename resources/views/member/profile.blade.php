@extends('layouts.app')
@section('title')—{{ $member->player }}@endsection
@section('content')
<div class="container member-profile">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card profile-card dark-back">
                    <div class="card-body" highlight="none">
                        <div class="row justify-content-between align-items-start">
                            <div class="column align-items-start grow">
                                <div class="row no-margin align-items-center">
                                    @include('shared.back')
                                    <h1>
                                        <a href="https://swgoh.gg{{ $member->url }}" target="_gg">
                                            <span>{{ $member->player }}</span>
                                        </a>
                                    </h1>
                                </div>
                                <div class="note"><strong>Ally Code:</strong> <span>{{ $member->ally_code}}</span></div>

                                <div class="row no-margin guild-info glass-back" @@click="go('/guild/{{ $member->guild->id }}')">
                                    <span class="small-note space-right">Guild:</span>
                                    <div class="column">
                                        <h4>{{ $member->guild->name }}</h4>
                                        <div class="small-note">{{ number_format($member->guild->gp) }}M</div>
                                    </div>

                                </div>
                            </div>

                            @person
                            <div class="column align-items-stretch">
                                <button class="btn btn-primary btn-icon with-text striped" @@click="go(`{{ route('member.characters', ['ally' => $member->ally_code]) }}`)">
                                    <ion-icon name="people-circle-outline" size="medium"></ion-icon>
                                    <span>{{ __('Characters') }}</span>
                                </button>
                                <button class="btn btn-primary btn-icon with-text striped" @@click="go(`{{ route('member.ships', ['ally' => $member->ally_code]) }}`)">
                                    <ion-icon name="planet" size="medium"></ion-icon>
                                    <span>{{ __('Ships') }}</span>
                                </button>
                                <button class="btn btn-primary btn-icon with-text striped" @@click="go(`{{ route('member.ships', ['ally' => $member->ally_code]) }}`)">
                                    <ion-icon name="planet" size="medium"></ion-icon>
                                    <span>{{ __('Ships') }}</span>
                                </button>
                            </div>
                            @endperson

                            <div class="column player-stats glass-back">
                                <div class="row justify-content-between align-items-baseline">
                                    <span><strong>GP:</strong> <span>{{ number_format($member->gp) }}</span></span>
                                    <span><strong>Character:</strong> {{ number_format($member->character_gp) }}</span></span>
                                    <span><strong>Ship:</strong> <span>{{ number_format($member->ship_gp) }}</span></span>
                                    <span><strong>Zetas:</strong> <span>{{ $member->zetas->count() }}</span></span>
                                </div>
                                <div class="row justify-content-between align-items-baseline">
                                    <span class="row justify-content-center align-items-center">
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">7</span></div>
                                        </div>
                                        <span>:&nbsp;</span>
                                        <span>{{ $member->relic_7 }}</span>
                                    </span>
                                    <span class="row justify-content-center align-items-center">
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">6</span></div>
                                        </div>
                                        <span>:&nbsp;</span>
                                        <span>{{ $member->relic_6 }}</span>
                                    </span>
                                    <span class="row justify-content-center align-items-center">
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">5</span></div>
                                        </div>
                                        <span>:&nbsp;</span>
                                        <span>{{ $member->relic_5 }}</span>
                                    </span>
                                    <span class="row justify-content-center align-items-center">
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">3</span></div>
                                        </div>
                                        <span>:&nbsp;</span>
                                        <span>{{ $member->relic_3 }}</span>
                                    </span>

                                    <span class="row justify-content-center align-items-center">
                                        <div class="portrait mini centered">
                                            <div class="gear g13" style="--gear-image:url('/images/units/gear/gear-icon-g13.png');"></div>
                                            <span class="value">13</span>
                                        </div>
                                        <span>:&nbsp;</span>
                                        <span>{{ $member->gear_13 }}</span>
                                    </span>
                                    <span class="row justify-content-center align-items-center">
                                        <div class="portrait mini centered">
                                            <div class="gear g12" style="--gear-image:url('/images/units/gear/gear-icon-g12.png');"></div>
                                            <span class="value">12</span>
                                        </div>
                                        <span>:&nbsp;</span>
                                        <span>{{ $member->gear_12 }}</span>
                                    </span>
                                </div>
                                <div class="row justify-content-between align-items-baseline">
                                    <span><strong>6•:</strong> <span>{{ $member->six_dot }}</span></span>
                                    <span><div class="row justify-content-center align-items-center"><strong>25</strong> <span class="mod-set-image speed tier-5 mini"></span><span>:&nbsp;</span><span>{{ $member->speed_25 }}</span></div></span>
                                    <span><div class="row justify-content-center align-items-center"><strong>20</strong> <span class="mod-set-image speed tier-5 mini"></span><span>:&nbsp;</span><span>{{ $member->speed_20 }}</span></div></span>
                                    <span><div class="row justify-content-center align-items-center"><strong>15</strong> <span class="mod-set-image speed tier-5 mini"></span><span>:&nbsp;</span><span>{{ $member->speed_15 }}</span></div></span>
                                    <span><div class="row justify-content-center align-items-center"><strong>10</strong> <span class="mod-set-image speed tier-5 mini"></span><span>:&nbsp;</span><span>{{ $member->speed_10 }}</span></div></span>
                                    <span><div class="row justify-content-center align-items-center"><strong>100</strong> <span class="mod-set-image offense tier-5 mini"></span><span>:&nbsp;</span><span>{{ $member->offense_100 }}</span></div></span>
                                </div>
                            </div>
                        </div>


                        <h2>Arena</h2>
                        @include('shared.arena_table', [
                            'arena' => 'Squad',
                            'rank' => array_get($member->arena, 'char', head($member->arena))['rank'],
                            'team' => collect(array_get($member->arena, 'char.squad', array_get(head($member->arena), 'squad.cellList')))->map(function($u) {
                                if (!isset($u['defId'])) {
                                    list($baseId, ) = explode(':', $u['unitDefId']);
                                    return ['defId' => $baseId];
                                }
                                return ['defId' => $u['defId']];
                            }),
                        ])
                        @include('shared.arena_table', [
                            'arena' => 'Fleet',
                            'rank' => array_get($member->arena, 'ship', last($member->arena))['rank'],
                            'team' => collect(array_get($member->arena, 'ship.squad', array_get(last($member->arena), 'squad.cellList')))->map(function($u) {
                                if (!isset($u['defId'])) {
                                    list($baseId, ) = explode(':', $u['unitDefId']);
                                    return ['defId' => $baseId];
                                }
                                return ['defId' => $u['defId']];
                            }),
                        ])

                        <h2>Key Characters</h2>

                        @include('shared.unit_table', [
                            'characters' => ['GRANDMASTERLUKE', 'SITHPALPATINE', 'GLREY', 'SUPREMELEADERKYLOREN']
                        ])
                        @include('shared.unit_table', [
                            'characters' => ['JEDIKNIGHTLUKE', 'GENERALSKYWALKER', 'PADMEAMIDALA', 'C3POLEGENDARY', 'JEDIKNIGHTREVAN', 'ANAKINKNIGHT']
                        ])
                        @include('shared.unit_table', [
                            'characters' => ['DARTHREVAN', 'DARTHMALAK', 'WATTAMBOR', 'GRIEVOUS', 'GEONOSIANBROODALPHA', 'DARTHTRAYA']
                        ])
                        @include('shared.unit_table', [
                            'characters' => ['CAPITALNEGOTIATOR', 'CAPITALMALEVOLENCE', 'CAPITALFINALIZER', 'CAPITALRADDUS', 'MILLENNIUMFALCON']
                        ])

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

@include('shared.guild_listener')