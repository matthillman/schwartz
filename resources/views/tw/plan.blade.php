@extends('layouts.app')
@section('title', '—TW Plan')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @include('shared.status')

            <div class="card">
                <div class="card-header"><h2>TW Plan: {{ $plan->name }}</h2></div>

                <div class="card-body">
                    <div class="row no-margin">
                        <div class="col-md-8">

                            <h2>Zone Config</h2>

                            <div class="row justify-content-center no-margin">
                            @foreach ([[8, 9, 10], [5, 6, 7], [3, 4], [1, 2]] as $slice)
                                <div class="column no-margin zone-wrapper">
                                @foreach ($slice as $zone)
                                    <div class="zone zone-{{ $zone }}">
                                        <img src="/images/tw/defense-zone-{{ $zone }}.png">
                                        <div class="zone-content-wrapper">
                                            <div>Zone {{ $zone }}</div>
                                            @foreach ($plan->{"zone_{$zone}"} as $squad => $members)
                                                {{ $squad }} => {{ $members }}
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                                </div>
                            @endforeach
                            </div>

                            <div class="page-wrapper">
                                @foreach (range(1, 10) as $zone)
                                    <tw-zone
                                        :zone="{{ $zone }}"
                                        :zone-data="{{ collect($plan->{"zone_{$zone}"})->toJson() }}"
                                        :squads="{{ $plan->squad_group->squads->keyBy('id')->toJson() }}"
                                        :units="{{ $units->whereIn('base_id', $unitIDs)->keyBy('base_id')->toJson() }}"
                                        :members="{{ $plan->guild->members->map(function($m) use ($unitIDs) { return $m->characterSet($unitIDs); })->toJson() }}"
                                    ></tw-zone>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <collapsable start-open>
                                <template #top-trigger="{ open }">
                                    <div class="row no-margin align-items-start">
                                        <ion-icon :name="open ? `chevron-down` : `chevron-forward`" size="medium"></ion-icon>
                                        <h4>Squads</h4>
                                    </div>
                                </template>
                                <table class="squad-table micro">
                                    <tbody>
                                        @foreach ($plan->squad_group->squads as $squad)
                                            <tr class="squad-row">
                                                <td class="top">
                                                    <div class="column char-image-column">
                                                        <div class="char-image-square small {{ $units->where('base_id', $squad->leader_id)->first()->alignment }}">
                                                            <img src="/images/units/{{$squad->leader_id}}.png">
                                                        </div>
                                                    </div>
                                                </td>
                                                @forelse ($squad->additional_members as $char_id)
                                                    <td class="top">
                                                        <div class="column char-image-column">
                                                            <div class="char-image-square small {{ $units->where('base_id', $char_id)->first()->alignment }}">
                                                                <img src="/images/units/{{$char_id}}.png">
                                                            </div>
                                                        </div>
                                                    </td>
                                                @empty
                                                    @if (count($plan->squad_group->squads->max('additional_members')) === 0)
                                                    <td><div>&nbsp;</div></td>
                                                    @endif
                                                @endforelse
                                                @for ($i = 0; $i < count($plan->squad_group->squads->max('additional_members')) - count($squad->additional_members); $i++)
                                                <td><div>&nbsp;</div></td>
                                                @endfor
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </collapsable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection