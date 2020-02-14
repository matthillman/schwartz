@extends('layouts.app')
@section('body-class', 'no-bg')
@section('title', '—Member Compare')
@push('styles')
<style type="text/css">
:root {
    --compare-count: {{ $data->count() }};
    --header-column: 0px;
    --column-width: 400px;
}
</style>
@endpush
@section('content')
<div class="container comparison-container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body row">
                @foreach ($data as $id => $member)
                    <div class="col-md-5">
                        <h4>{{ $member['player'] }}</h4>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                @foreach ($data as $id => $member)
                    <div class="col-md-5 column justify-content-center align-items-center">
                        <table class="gear-table">
                            <tbody>
                                <tr>
                                    <th>Ally Code</td>
                                    <td colspan="2">{{ preg_replace('/^(\d{3})(\d{3})(\d{3})$/', '${1}-${2}-${3}', $member['ally_code']) }}</td>
                                </tr>
                            </tbody>

                            <tbody>
                                <tr>
                                    <th>Guild</td>
                                    <td colspan="2">{{ $member['guild_name'] }}</td>
                                </tr>
                                <tr>
                                    <th {{ $winner['guild_gp']->contains($id) ? 'winner' : '' }}>Guild GP</td>
                                    <td colspan="2" {{ $winner['guild_gp']->contains($id) ? 'winner' : '' }}>{{ number_format($member['guild_gp']) }}</td>
                                </tr>
                            </tbody>

                            <tbody>
                                <tr>
                                    <th rowspan="2" {{ $winner['gp']->contains($id) ? 'winner' : '' }}>GP</th>
                                    <th {{ $winner['character_gp']->contains($id) ? 'winner' : '' }}>Char GP</th>
                                    <th {{ $winner['ship_gp']->contains($id) ? 'winner' : '' }}>Ship GP</th>
                                </tr>
                                <tr>
                                    <td {{ $winner['character_gp']->contains($id) ? 'winner' : '' }}>{{ number_format($member['character_gp']) }}</td>
                                    <td {{ $winner['ship_gp']->contains($id) ? 'winner' : '' }}>{{ number_format($member['ship_gp']) }}</td>
                                </tr>
                                <tr>
                                    <td rowspan="2" {{ $winner['gp']->contains($id) ? 'winner' : '' }}>{{ number_format($member['gp']) }}</th>
                                    <th {{ $winner['top_eighty']->contains($id) ? 'winner' : '' }}>Top 80</th>
                                    <th {{ $winner['top_sixty_five']->contains($id) ? 'winner' : '' }}>Top 65</th>
                                </tr>
                                <tr>
                                    <td {{ $winner['top_eighty']->contains($id) ? 'winner' : '' }}>{{ number_format($member['top_eighty']) }}</td>
                                    <td {{ $winner['top_sixty_five']->contains($id) ? 'winner' : '' }}>{{ number_format($member['top_sixty_five']) }}</td>
                                </tr>
                            </tbody>

                            <tbody>
                                <tr>
                                    <th {{ $winner['zetas']->contains($id) ? 'winner' : '' }}>
                                        <img src="/images/units/abilities/zeta.png" class="zeta-image">
                                    </th>
                                    <td colspan="2" {{ $winner['zetas']->contains($id) ? 'winner' : '' }}>{{ number_format($member['zetas']) }}</td>
                                </tr>
                            </tbody>

                            <tbody>
                                <tr>
                                    <th rowspan="2">Arena</th>
                                    <th {{ $winner['squad_rank']->contains($id) ? 'winner' : '' }}>Squad Rank</th>
                                    <th {{ $winner['fleet_rank']->contains($id) ? 'winner' : '' }}>Fleet Rank</th>
                                </tr>
                                <tr>
                                    <td {{ $winner['squad_rank']->contains($id) ? 'winner' : '' }}>{{ number_format($member['squad_rank']) }}</td>
                                    <td {{ $winner['fleet_rank']->contains($id) ? 'winner' : '' }}>{{ number_format($member['fleet_rank']) }}</td>
                                </tr>
                            </tbody>

                        </table>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                    <div class="col-md-12 row justify-content-center align-items-center">
                        <span class="gear-icon tier12 micro">
                            <span class="gear-icon-inner">
                                <img class="gear-icon-img" src="/images/gear/tex.equip_powercellinjector.png" alt="Power Cell Injector (Plasma)">
                            </span>
                        </span>
                        <h2 class="section-head">Gear</h2>
                    </div>
                @foreach ($data as $id => $member)
                    <div class="col-md-5 column justify-content-center align-items-center">
                        <table class="gear-table">
                            <thead>
                                <tr>
                                    <th {{ $winner['relic_seven']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">7</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['relic_six']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">6</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['relic_five']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">5</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['r_total']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">5+</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['r_three_plus']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">3+</span></div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['relic_seven']->contains($id) ? 'winner' : '' }}>{{ number_format($member['relic_seven']) }}</td>
                                    <td {{ $winner['relic_six']->contains($id) ? 'winner' : '' }}>{{ number_format($member['relic_six']) }}</td>
                                    <td {{ $winner['relic_five']->contains($id) ? 'winner' : '' }}>{{ number_format($member['relic_five']) }}</td>
                                    <td {{ $winner['r_total']->contains($id) ? 'winner' : '' }}>{{ number_format($member['relic_seven'] + $member['relic_six'] + $member['relic_five']) }}</td>
                                    <td {{ $winner['r_three_plus']->contains($id) ? 'winner' : '' }}>{{ number_format($member['relic_seven'] + $member['relic_six'] + $member['relic_five'] + $member['relic_four'] + $member['relic_three']) }}</td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th {{ $winner['gear_thirteen']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g13" style="--gear-image:url('/images/units/gear/gear-icon-g13.png');"></div>
                                            <span class="value">13</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['gear_twelve']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g12" style="--gear-image:url('/images/units/gear/gear-icon-g12.png');"></div>
                                            <span class="value">12</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['gear_eleven']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g11" style="--gear-image:url('/images/units/gear/gear-icon-g11.png');"></div>
                                            <span class="value">11</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['g_total']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g11" style="--gear-image:url('/images/units/gear/gear-icon-g11.png');"></div>
                                            <span class="value">11+</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['r_all']->contains($id) ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">1+</span></div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['gear_thirteen']->contains($id) ? 'winner' : '' }}>{{ number_format($member['gear_thirteen']) }}</td>
                                    <td {{ $winner['gear_twelve']->contains($id) ? 'winner' : '' }}>{{ number_format($member['gear_twelve']) }}</td>
                                    <td {{ $winner['gear_eleven']->contains($id) ? 'winner' : '' }}>{{ number_format($member['gear_eleven']) }}</td>
                                    <td {{ $winner['g_total']->contains($id) ? 'winner' : '' }}>{{ number_format($member['gear_thirteen'] + $member['gear_twelve'] + $member['gear_eleven']) }}</td>
                                    <td {{ $winner['r_all']->contains($id) ? 'winner' : '' }}>{{ number_format($member['relic_seven'] + $member['relic_six'] + $member['relic_five'] + $member['relic_four'] + $member['relic_three'] + $member['relic_two'] + $member['relic_one']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                    <div class="col-md-12 row justify-content-center align-items-center">
                        <div class="image"><div class="icon mod-image diamond speed tier-6 gold micro"></div></div>
                        <h2 class="section-head">Mods</h2>
                    </div>
                @foreach ($data as $id => $member)
                    <div class="col-md-5 column justify-content-center align-items-center">
                        <table class="gear-table">
                            <thead>
                                <tr>
                                    <th {{ $winner['six_dot']->contains($id) ? 'winner' : '' }}>6•</th>
                                    <th {{ $winner['one_fifty_offense']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>150</span> <span class="mod-set-image offense tier-5 mini"></span></div></th>
                                    <th {{ $winner['one_hundred_offense']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>100</span> <span class="mod-set-image offense tier-5 mini"></span></div></th>
                                    <th {{ $winner['four_percent_offense']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>4%</span> <span class="mod-set-image offense tier-5 mini"></span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['six_dot']->contains($id) ? 'winner' : '' }}>{{ number_format($member['six_dot']) }}</td>
                                    <td {{ $winner['one_fifty_offense']->contains($id) ? 'winner' : '' }}>{{ number_format($member['one_fifty_offense']) }}</td>
                                    <td {{ $winner['one_hundred_offense']->contains($id) ? 'winner' : '' }}>{{ number_format($member['one_hundred_offense']) }}</td>
                                    <td {{ $winner['four_percent_offense']->contains($id) ? 'winner' : '' }}>{{ number_format($member['four_percent_offense']) }}</td>
                                </tr>
                            </tbody>

                            <thead>
                                <tr>
                                    <th {{ $winner['twenty_five_plus']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>25</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                    <th {{ $winner['twenty_plus']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>20</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                    <th {{ $winner['fifteen_plus']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>15</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                    <th {{ $winner['ten_plus']->contains($id) ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>10</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['twenty_five_plus']->contains($id) ? 'winner' : '' }}>{{ number_format($member['twenty_five_plus']) }}</td>
                                    <td {{ $winner['twenty_plus']->contains($id) ? 'winner' : '' }}>{{ number_format($member['twenty_plus']) }}</td>
                                    <td {{ $winner['fifteen_plus']->contains($id) ? 'winner' : '' }}>{{ number_format($member['fifteen_plus']) }}</td>
                                    <td {{ $winner['ten_plus']->contains($id) ? 'winner' : '' }}>{{ number_format($member['ten_plus']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
                </div>

                @person
                @if (count($data) > 2)
                <div class="card-body row">
                @foreach ($data as $id => $member)
                    <div class="col-md-5">
                        <h4>{{ $member['player'] }}</h4>
                    </div>
                @endforeach
                </div>
                @endif
                @endperson

                <div class="card-body row">
                    <div class="col-md-12 row justify-content-center align-items-center">
                        <div class="image">
                            <div class="char-image-square small light">
                                <img src="/images/units/GENERALKENOBI.png">
                            </div>
                        </div>
                        <h2 class="section-head">Key Characters</h2>
                    </div>
                @foreach ($character_list as $id => $character)
                    @foreach ($data as $member_id => $member)
                        <div class="col-md-1">
                            <div class="char-image-wrapper">
                            @include('shared.char', [
                                'character' => $member->get(strtolower($id)),
                                'noStats' => true,
                                'noMods' => true,
                            ])
                            </div>
                            <div class="char-name">{{ $character['name'] }}</div>
                        </div>
                        <div class="col-md-4 {{ $character['alignment'] }} column justify-content-center align-items-center">
                            <table class="gear-table">
                                <thead>
                                    <th colspan="3" {{ $winner[strtolower($id.'_'.$stat_list->first()['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>
                                        <div class="mod-header-wrapper">
                                            <span class="mod-set-image tier-5 mini {{ $stat_list->first()['key'] }}" overlay="{{ $stat_list->first()['display'] }}"></span>
                                        </div>
                                    </th>
                                    <td colspan="3" {{ $winner[strtolower($id.'_'.$stat_list->first()['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>
                                        <span>{{ $member->get(strtolower($id.'_'.$stat_list->first()['stat']->getKey())) }}</span>
                                        <span>(+{{ format_stat($member->get(strtolower($id))->modBonus($stat_list->first()['stat']), $stat_list->first()['stat']) }})</span>
                                    </td>
                                </thead>
                                <thead>
                                    @foreach ($stat_list->skip(1) as $stat)
                                    <th {{ $winner[strtolower($id.'_'.$stat['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>
                                        <div class="mod-header-wrapper">
                                            @isset($stat['key'])
                                            <span class="mod-set-image tier-5 mini {{ $stat['key'] }}{{ !empty($stat['display']) ? ' overlaid' : '' }}"></span>
                                            @endisset
                                            @isset($stat['icon'])
                                            <ion-icon name="{{ $stat['icon'] }}" size="micro" class="gold"></ion-icon>
                                            @endisset
                                            @if (!empty($stat['display']))
                                            <span class="overlay">{{ $stat['display'] }}</span>
                                            @endif
                                        </div>
                                    </th>
                                    @endforeach
                                </thead>
                                <tbody>
                                    @foreach ($stat_list->skip(1) as $stat)
                                    <td {{ $winner[strtolower($id.'_'.$stat['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>{{ format_stat($member->get(strtolower($id.'_'.$stat['stat']->getKey())), $stat['stat']) }}</td>
                                    @endforeach
                                </body>
                            </table>
                        </div>
                    @endforeach
                @endforeach
                </div>

                <div class="card-body row">
                    <div class="col-md-12 row justify-content-center align-items-center">
                        <div class="image">
                            <div class="char-image-square ship small light">
                                <img src="/images/units/CAPITALNEGOTIATOR.png">
                            </div>
                        </div>
                        <h2 class="section-head">Key Ships</h2>
                    </div>
                @foreach ($ship_list as $id => $character)
                    @foreach ($data as $member_id => $member)
                        @empty($member->get(strtolower($id)))
                        <div class="col-md-5">
                            <div class="row justify-content-center align-items-center locked-message">
                                <strong>{{ $character['name'] }} Not Unlocked</strong>
                            </div>
                        </div>
                        @else
                        <div class="col-md-1">
                            <div class="char-image-wrapper">
                            @include('shared.char', [
                                'character' => $member->get(strtolower($id)),
                                'noStats' => true,
                                'noMods' => true,
                                'size' => 'ship-80',
                            ])
                            </div>
                            <div class="char-name">{{ $character['name'] }}</div>
                        </div>
                        <div class="col-md-4 {{ $character['alignment'] }} column justify-content-center align-items-center">
                            <table class="gear-table">
                                <thead>
                                    <th colspan="3" {{ $winner[strtolower($id.'_'.$stat_list->first()['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>
                                        <div class="mod-header-wrapper">
                                            <span class="mod-set-image tier-5 mini {{ $stat_list->first()['key'] }}" overlay="{{ $stat_list->first()['display'] }}"></span>
                                        </div>
                                    </th>
                                    <td colspan="3" {{ $winner[strtolower($id.'_'.$stat_list->first()['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>
                                        <span>{{ $member->get(strtolower($id.'_'.$stat_list->first()['stat']->getKey())) }}</span>
                                    </td>
                                </thead>
                                <thead>
                                    @foreach ($stat_list->skip(1) as $stat)
                                    <th {{ $winner[strtolower($id.'_'.$stat['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>
                                        <div class="mod-header-wrapper">
                                            @isset($stat['key'])
                                            <span class="mod-set-image tier-5 mini {{ $stat['key'] }}{{ !empty($stat['display']) ? ' overlaid' : '' }}"></span>
                                            @endisset
                                            @isset($stat['icon'])
                                            <ion-icon name="{{ $stat['icon'] }}" size="micro" class="gold"></ion-icon>
                                            @endisset
                                            @if (!empty($stat['display']))
                                            <span class="overlay">{{ $stat['display'] }}</span>
                                            @endif
                                        </div>
                                    </th>
                                    @endforeach
                                </thead>
                                <tbody>
                                    @foreach ($stat_list->skip(1) as $stat)
                                    <td {{ $winner[strtolower($id.'_'.$stat['stat']->getKey())]->contains($member_id) ? 'winner' : '' }}>{{ format_stat($member->get(strtolower($id.'_'.$stat['stat']->getKey())), $stat['stat']) }}</td>
                                    @endforeach
                                </body>
                            </table>
                        </div>
                        @endempty
                    @endforeach
                @endforeach
                </div>

                @bot
                <div class="row justify-content-end footer-logo">
                    <div class="logo">@include('shared.logo')</div>
                </div>
                @endbot

            </div>
        </div>
    </div>
</div>
@endsection