@extends('layouts.app')

@section('viewport', '1300')

@section('content')
<div class="container mods">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header">Mods: Set Maker</div>

                <div class="card-body">
                @guest
                    <mods></mods>
                @else
                    <mods user="{{ auth()->user()->id }}"></mods>
                @endguest
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
