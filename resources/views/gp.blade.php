@extends('layouts.app')

@section('viewport', '716')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>Guilds</h2></div>

                <div class="card-body">
                    <members guilds="{{ $guilds->toJson() }}"></members>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')