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

                <tw-plan
                    :user-id="{{ auth()->user()->id }}"
                    :plan="{{ $plan->toJson() }}"
                    :active-members="{{ $plan->members }}"
                ></tw-plan>
            </div>
        </div>
    </div>
</div>
@endsection