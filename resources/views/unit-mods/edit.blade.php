@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2>{{ $preference->unit->name }} Mod Preferences</h2></div>

                <div class="card-body">
                    <mod-preference unit="{{ $preference->toJson() }}"></mod-preference>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
