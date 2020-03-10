@extends('layouts.app')
@section('title', '—TW Plan')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            @include('shared.status')

            <div class="card">
                <div class="card-header row no-margin justify-content-between align-items-start">
                    <div class="column">
                        <h2>TW Plan: {{ $plan->name }}</h2>
                        <div class="small-note">{{ $plan->guild->name }}</div>
                    </div>

                    <button class="btn btn-primary btn-icon with-text" @@click="go(`{{ route('squads', ['group' => $plan->squad_group->id ]) }}`)">
                        <ion-icon name="body" size="small"></ion-icon>
                        <span>Edit Squads</span>
                    </button>
                </div>
                <tw-plan
                    :plan="{{ $plan->toJson() }}"
                    :squads="{{ $plan->squad_group->squads->keyBy('id')->toJson() }}"
                    :units="{{ $units->whereIn('base_id', $unitIDs)->keyBy('base_id')->toJson() }}"
                    :members="{{ $plan->guild->members->map(function($m) use ($unitIDs) { return $m->characterSet($unitIDs); })->sortBy('player')->values()->toJson() }}"
                ></tw-plan>
            </div>
        </div>
    </div>
</div>
@endsection