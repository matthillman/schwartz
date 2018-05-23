@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>Units</h2></div>

                <div class="card-body">
                    <div class="unit-list">
                        <div class="row">
                            <div><h3>ID</h3></div>
                            <div><h3>Name</h3></div>
                        </div>
                    @foreach($units as $unit)
                        <div class="row">
                            <div>{{ $unit->base_id }}</div>
                            <div>{{ $unit->name }}</div>
                            <a href="{{ $unit->url }}" target="_gg" class="gg-link">
                                @include('shared.bb8')
                            </a>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
