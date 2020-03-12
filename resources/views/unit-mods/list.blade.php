@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2>Unit Mod Preferences</h2></div>

                <div class="card-body">
                    <div class="unit-list">
                        <div class="row">
                            <div><h3>Unit</h3></div>
                        </div>
                    @foreach($units as $unit)
                        <div class="row">
                            <div>{{ $unit->name }}</div>
                            <div>
                                <form method="GET" action="{{ route('character-mods.show', ['unit' => strtolower($unit->base_id)]) }}" >
                                    <button type="submit" class="btn btn-primary">{{ __('Edit Mod Preferences') }}</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
