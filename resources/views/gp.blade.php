@extends('layouts.app')

@section('viewport', '830')

@section('content')
<div class="container guild-stats">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>Guilds</h2></div>

                <div class="card-body">
                    <members
                        v-bind:guilds="{{ $guilds->toJson() }}"
                        @if (isset($members))
                        v-bind:members="{{ $members->toJson() }}"
                        @endif
                    ></members>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')