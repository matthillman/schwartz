@extends('layouts.app')
@section('title', '—TW Assignments')
@section('viewport', '509')
@section('content')
<div class="container tw-assignments">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header row no-margin justify-content-between align-items-end">
                    <div class="column">
                        <h3>TW Defense Assignments</h3>
                        <h3>for <strong>{{ $member['player'] }}</strong></h3>
                        <div class="small-note">{{ $plan->name }} ({{ $plan->guild->name }})</div>
                    </div>

                    <div>Total Banners: {{ collect(range(1, 10))
                        ->map(function($z) use ($plan, $member) {
                            return $plan->{"zone_$z"}
                                ->flatten()
                                ->map(function($ac) use ($member, $z) {
                                    return $ac == $member['ally_code'] ? ($z == 5 || $z == 8 ? 34 : 30) : 0;
                                });
                        })
                        ->flatten()
                        ->sum()
                    }}</div>

                </div>

                @foreach (range(1, 10) as $zone)
                    @if ($plan->{"zone_$zone"}->flatten()->contains($member['ally_code']))
                    <div class="card-body zone-assignments">
                        <div class="row">
                            <div class="col-12 row no-margin justify-content-center dark-back header">
                                <h1>Zone {{ $zone }}</h1>
                            </div>
                            <div class="col-4">
                                <div class="assignment-zone">
                                    <img class="zone-{{ $zone }}" src="/images/tw/defense-zone-{{ $zone }}.png">
                                    <div class="zone-content-wrapper">
                                        <div class="column justify-content-center align-items-center">
                                            <h1>{{ $zone }}</h1>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-8">
                                @if(!empty($plan->{"zone_{$zone}_notes"}))
                                <div class="row no-margin notes-row align-content-stretch">
                                    <div class="small-note">Notes:</div>
                                    <div class="notes">{{ $plan->{"zone_{$zone}_notes"} }}</div>
                                </div>
                                @endif
                                <div><strong>Place these teams:</strong></div>
                                <div class="team-wrapper dark-back">
                                @foreach ($plan->{"zone_$zone"} as $squadID => $members)
                                    @if (in_array($member['ally_code'], $members))
                                    <div class="row no-margin">
                                        @if (!$member->get('characters')->where('unit_name', $squads->get($squadID)->leader_id)->isEmpty())
                                        <div class="character-bg-wrapper glass-back {{ $units[$squads->get($squadID)->leader_id]->combat_type == 1 ? '' : 'ship' }}">
                                            <character
                                                :character="{{ $member->get('characters')->where('unit_name', $squads->get($squadID)->leader_id)->first()->toJson() }}"
                                                no-stats no-mods no-zetas
                                                :classes="'large'"
                                            ></character>
                                        </div>
                                        @else
                                        <div>LEADER NOT UNLOCKED</div>
                                        @endif
                                        <div class="column justify-content-center align-items-center grow"><h3 class="squad-name">{{ $squads[$squadID]->display }}</h3></div>
                                        </div>

                                        <div class="row no-margin">
                                            @if($units[$squads->get($squadID)->leader_id]->combat_type == 1)
                                            @foreach ($squads->get($squadID)->additional_members as $base_id)
                                            <div class="character-bg-wrapper glass-back">
                                                @if (!$member->get('characters')->where('unit_name', $base_id)->isEmpty())
                                                <character
                                                    :character="{{ $member->get('characters')->where('unit_name', $base_id)->first()->toJson() }}"
                                                    no-stats no-mods no-zetas
                                                    :classes="'medium'"
                                                ></character>
                                                @else
                                                <div>{{$base_id}} not ulocked!!</div>
                                                @endif
                                            </div>
                                            @endforeach
                                            @else
                                            @foreach (collect($squads->get($squadID)->additional_members)->slice(0, 3) as $base_id)
                                            <div class="character-bg-wrapper glass-back ship">
                                                @if (!$member->get('characters')->where('unit_name', $base_id)->isEmpty())
                                                <character
                                                    :character="{{ $member->get('characters')->where('unit_name', $base_id)->first()->toJson() }}"
                                                    no-stats no-mods no-zetas
                                                    :classes="'medium'"
                                                ></character>
                                                @else
                                                <div>{{$base_id}} not ulocked!!</div>
                                                @endif
                                            </div>
                                            @endforeach
                                            @endif
                                        </div>

                                        @if($units[$squads->get($squadID)->leader_id]->combat_type == 2)
                                        <div class="small-note">Reinforcements</div>
                                        <div class="row no-margin">
                                            @foreach (collect($squads->get($squadID)->additional_members)->slice(3) as $base_id)
                                            <div class="character-bg-wrapper glass-back ship">
                                                @if (!$member->get('characters')->where('unit_name', $base_id)->isEmpty())
                                                <character
                                                    :character="{{ $member->get('characters')->where('unit_name', $base_id)->first()->toJson() }}"
                                                    no-stats no-mods no-zetas
                                                    :classes="'medium'"
                                                ></character>
                                                @else
                                                <div>{{$base_id}} not ulocked!!</div>
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                        @endif
                                    @endif
                                @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection