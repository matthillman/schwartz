@extends('layouts.app')
@section('title', '—Guild Compare')
@section('body-class', 'no-bg')
@push('styles')
<style type="text/css">
:root {
    --compare-count: {{ $data->count() }};
}
</style>
@endpush
@section('content')
<div class="container comparison-container">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-body row">
                    <div class="col-2">&nbsp;</div>
                @foreach ($data as $id => $guild)
                    <div class="col-5">
                        <h4>{{ $guild['name'] }}</h4>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                    <div class="col-2">&nbsp;</div>
                @foreach ($data as $id => $guild)
                    <div class="col-5 column justify-content-center align-items-center">
                        <table class="gear-table">
                            <thead>
                                <tr>
                                    <th {{ $winner['member_count'] === $id ? 'winner' : '' }}>Members</th>
                                    <th {{ $winner['gp'] === $id ? 'winner' : '' }}>GP</th>
                                    <th {{ $winner['zetas'] === $id ? 'winner' : '' }}>
                                        <img src="/images/units/abilities/zeta.png" class="zeta-image">
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['member_count'] === $id ? 'winner' : '' }}>{{ $guild['member_count'] }}</td>
                                    <td {{ $winner['gp'] === $id ? 'winner' : '' }}>{{ number_format($guild['gp']) }}</td>
                                    <td {{ $winner['zetas'] === $id ? 'winner' : '' }}>{{ number_format($guild['zetas']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                    <div class="col-2 column justify-content-center align-items-center">
                        <span class="gear-icon tier12 giant">
                            <span class="gear-icon-inner">
                                <img class="gear-icon-img" src="/images/gear/tex.equip_powercellinjector.png" alt="Power Cell Injector (Plasma)">
                            </span>
                        </span>
                    </div>
                @foreach ($data as $id => $guild)
                    <div class="col-5 column justify-content-center align-items-center">
                        <table class="gear-table">
                            <thead>
                                <tr>
                                    <th {{ $winner['relic_7'] === $id ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">7</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['relic_6'] === $id ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">6</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['relic_5'] === $id ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">5</span></div>
                                        </div>
                                    </th>
                                    <th {{ $winner['r_total'] === $id ? 'winner' : '' }}>
                                        <div class="portrait relic-only centered">
                                            <div class="relic"><span class="value">5+</span></div>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['relic_7'] === $id ? 'winner' : '' }}>{{ number_format($guild['relic_7']) }}</td>
                                    <td {{ $winner['relic_6'] === $id ? 'winner' : '' }}>{{ number_format($guild['relic_6']) }}</td>
                                    <td {{ $winner['relic_5'] === $id ? 'winner' : '' }}>{{ number_format($guild['relic_5']) }}</td>
                                    <td {{ $winner['r_total'] === $id ? 'winner' : '' }}>{{ number_format($guild['relic_7'] + $guild['relic_6'] + $guild['relic_5']) }}</td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr>
                                    <th {{ $winner['gear_13'] === $id ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g13" style="--gear-image:url('/images/units/gear/gear-icon-g13.png');"></div>
                                            <span class="value">13</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['gear_12'] === $id ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g12" style="--gear-image:url('/images/units/gear/gear-icon-g12.png');"></div>
                                            <span class="value">12</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['gear_11'] === $id ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g11" style="--gear-image:url('/images/units/gear/gear-icon-g11.png');"></div>
                                            <span class="value">11</span>
                                        </div>
                                    </th>
                                    <th {{ $winner['g_total'] === $id ? 'winner' : '' }}>
                                        <div class="portrait mini centered">
                                            <div class="gear g11" style="--gear-image:url('/images/units/gear/gear-icon-g11.png');"></div>
                                            <span class="value">11+</span>
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['gear_13'] === $id ? 'winner' : '' }}>{{ number_format($guild['gear_13']) }}</td>
                                    <td {{ $winner['gear_12'] === $id ? 'winner' : '' }}>{{ number_format($guild['gear_12']) }}</td>
                                    <td {{ $winner['gear_11'] === $id ? 'winner' : '' }}>{{ number_format($guild['gear_11']) }}</td>
                                    <td {{ $winner['g_total'] === $id ? 'winner' : '' }}>{{ number_format($guild['gear_13'] + $guild['gear_12'] + $guild['gear_11']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                    <div class="col-2 column justify-content-center align-items-center"><div class="image"><div class="icon mod-image diamond speed tier-6 gold large"></div></div></div>
                @foreach ($data as $id => $guild)
                    <div class="col-5 column justify-content-center align-items-center">
                        <table class="gear-table">
                            <thead>
                                <tr>
                                    <th {{ $winner['mods_six_dot'] === $id ? 'winner' : '' }}>6•</th>
                                    <th {{ $winner['mods_one_fifty_offense'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>150</span> <span class="mod-set-image offense tier-5 mini"></span></div></th>
                                    <th {{ $winner['mods_one_hundred_offense'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>100</span> <span class="mod-set-image offense tier-5 mini"></span></div></th>
                                    <th {{ $winner['mods_four_percent_offense'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>4%</span> <span class="mod-set-image offense tier-5 mini"></span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['mods_six_dot'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_six_dot']) }}</td>
                                    <td {{ $winner['mods_one_fifty_offense'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_one_fifty_offense']) }}</td>
                                    <td {{ $winner['mods_one_hundred_offense'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_one_hundred_offense']) }}</td>
                                    <td {{ $winner['mods_four_percent_offense'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_four_percent_offense']) }}</td>
                                </tr>
                            </tbody>

                            <thead>
                                <tr>
                                    <th {{ $winner['mods_twenty_five_plus'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>25</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                    <th {{ $winner['mods_twenty_plus'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>20</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                    <th {{ $winner['mods_fifteen_plus'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>15</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                    <th {{ $winner['mods_ten_plus'] === $id ? 'winner' : '' }}><div class="row justify-content-center align-items-center"><span>10</span> <span class="mod-set-image speed tier-5 mini"></span></div></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td {{ $winner['mods_twenty_five_plus'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_twenty_five_plus']) }}</td>
                                    <td {{ $winner['mods_twenty_plus'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_twenty_plus']) }}</td>
                                    <td {{ $winner['mods_fifteen_plus'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_fifteen_plus']) }}</td>
                                    <td {{ $winner['mods_ten_plus'] === $id ? 'winner' : '' }}>{{ number_format($guild['mods_ten_plus']) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endforeach
                </div>

                <div class="card-body row">
                @foreach ($character_list as $id => $character)
                    <div class="col-2">
                        <div class="char-image-square {{ $character['alignment'] }}">
                            <img src="/images/units/{{$id}}.png">
                        </div>
                        <div class="char-name">{{ $character['name'] }}</div>
                    </div>
                    @foreach ($data as $guild_id => $guild)
                        <div class="col-5 {{ $character['alignment'] }} column justify-content-center align-items-center">
                            <table class="gear-table">
                                <thead>
                                    <tr>
                                        <th {{ $winner[strtolower("${id}_r7")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait relic-only centered">
                                                <div class="relic"><span class="value">7</span></div>
                                            </div>
                                        </th>
                                        <th {{ $winner[strtolower("${id}_r6")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait relic-only centered">
                                                <div class="relic"><span class="value">6</span></div>
                                            </div>
                                        </th>
                                        <th {{ $winner[strtolower("${id}_r5")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait relic-only centered">
                                                <div class="relic"><span class="value">5</span></div>
                                            </div>
                                        </th>
                                        <th {{ $winner[strtolower("${id}_r_total")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait relic-only centered">
                                                <div class="relic"><span class="value">5+</span></div>
                                            </div>
                                        </th>
                                        <th {{ $winner[strtolower("${id}_13")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait mini centered">
                                                <div class="gear g13" style="--gear-image:url('/images/units/gear/gear-icon-g13.png');"></div>
                                                <span class="value">13</span>
                                            </div>
                                        </th>
                                        <th {{ $winner[strtolower("${id}_12")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait mini centered">
                                                <div class="gear g12" style="--gear-image:url('/images/units/gear/gear-icon-g12.png');"></div>
                                                <span class="value">12</span>
                                            </div>
                                        </th>
                                        <th {{ $winner[strtolower("${id}_11")] === $guild_id ? 'winner' : '' }}>
                                            <div class="portrait mini centered">
                                                <div class="gear g11" style="--gear-image:url('/images/units/gear/gear-icon-g11.png');"></div>
                                                <span class="value">11</span>
                                            </div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td {{ $winner[strtolower("${id}_r7")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_r7")]) }}</span></td>
                                        <td {{ $winner[strtolower("${id}_r6")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_r6")]) }}</span></td>
                                        <td {{ $winner[strtolower("${id}_r5")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_r5")]) }}</span></td>
                                        <td {{ $winner[strtolower("${id}_r_total")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_r_total")]) }}</span></td>
                                        <td {{ $winner[strtolower("${id}_13")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_13")]) }}</span></td>
                                        <td {{ $winner[strtolower("${id}_12")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_12")]) }}</span></td>
                                        <td {{ $winner[strtolower("${id}_11")] === $guild_id ? 'winner' : '' }}><span>{{ number_format($guild[strtolower("${id}_11")]) }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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