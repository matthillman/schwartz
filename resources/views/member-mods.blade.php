@extends('layouts.app')

@section('title', '—Guild Mods')
@section('viewport', '830')

@section('content')
<div class="container guild-stats">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2>Guilds</h2></div>

                <div class="card-body">
                    <member-mods
                        v-bind:guilds="{{ $guilds->toJson() }}"
                        @if (isset($mods))
                        v-bind:mods="{{ $mods->toJson() }}"
                        @endif
                    ></member-mods>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')