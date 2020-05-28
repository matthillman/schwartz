@extends('layouts.app')
@section('title', '—TW Plan')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header row no-margin justify-content-between align-items-start">
                    <div class="column grow">
                        <h2>TW Plan: {{ $plan->name }}</h2>
                        <div class="small-note">{{ $plan->guild->name }}</div>
                    </div>

                    <div class="column">
                        <button class="btn btn-primary btn-icon with-text striped" @@click="showGlobalModal = !showGlobalModal">
                            <ion-icon name="add" size="small"></ion-icon>
                            <span>Add a Squad</span>
                        </button>
                    </div>
                </div>

                <div v-show-slide="showGlobalModal">
                    <form method="POST" action="{{ route('squads.add') }}" >
                        @csrf
                        <input type="hidden" name="leader_id" value="" id="leader_id">
                        <input type="hidden" name="group" value="{{ $plan->squad_group->id }}">
                        <input type="hidden" name="other_members" value="" id="other_members">

                        <div class="row no-margin add-row input-group add-squad-row">
                            <unit-select :placeholder="`Leader`" @@input="val => set('leader_id', val ? val.base_id : null)" required></unit-select>
                            <input class="form-control" type="text" placeholder="Squad Name" id="name" name="name" required>
                            <input class="form-control" type="text" placeholder="Squad Description" id="description" name="description" required>
                            <button type="submit" class="btn btn-primary striped"><span>{{ __('Add') }}</span></button>
                        </div>
                        <div class="row no-margin add-row input-group add-squad-row multiple">
                            <unit-select multiple :placeholder="`Other Members`" @@input="val => set('other_members', val.map(u => u.base_id).reduce((c, u) => [c, u].join(','), '') )"></unit-select>
                        </div>
                    </form>
                </div>

                <tw-plan
                    :user-id="{{ auth()->user()->id }}"
                    :plan="{{ $plan->toJson() }}"
                    :squads="{{ $squads->toJson() }}"
                    :units="{{ $units->whereIn('base_id', $unitIDs)->keyBy('base_id')->toJson() }}"
                    :members="{{ $plan->guild->members->sortBy('sort_name', SORT_NATURAL|SORT_FLAG_CASE)->map(function($m) use ($unitIDs) { return $m->characterSet($unitIDs)->put('dm_status', $m->roles->dm_status); })->values()->toJson() }}"
                    :active-members="{{ $plan->members }}"
                ></tw-plan>
            </div>
        </div>
    </div>
</div>
@endsection