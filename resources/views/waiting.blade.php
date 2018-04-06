@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Waiting on Approval</div>

                <div class="card-body">
                @if (Auth::user()->active)
                    <a href="{{ route('home') }}">You have been approved! Click to continue</a>
                @else
                    You have registered and are awaiting approval. Please contact an admin.
                @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
