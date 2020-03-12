@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-8">
            <div class="card">
                @if ($updating)
                <div class="card-body">
                    <h2>Syncing roles from discord</h2>
                    <loading-indicator></loading-indicator>
                </div>
                @else
                <div class="card-header">Waiting on Approval</div>

                <div class="card-body">
                @if (auth()->user()->active)
                    <a href="{{ route('home') }}">You have been approved! Click to continue</a>
                @else
                    You have registered and are awaiting approval. Please contact an admin.
                @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@if (auth()->user())
@include('shared.update_listener')
@endif