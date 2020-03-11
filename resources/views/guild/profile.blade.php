@extends('layouts.app')
@section('title')—Guild Profiles @endsection
@section('body-class', 'no-bg')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="column">
                        <h2>Guild Discord Configuration</h2>
                        <div class="small-note">This is the ID of the discord server where your guild roles are defined. These roles are used to authorize other guild-related actions.</div>
                        <div class="small-note">Only officers can update these items</div>
                    </div>
                </div>

                <div class="card-body">
                    <div>Found the following ally codes for discord id {{ auth()->user()->discord_id }}:</div>
                    <ul>
                        @foreach (auth()->user()->accounts as $account)
                            <li>{{ $account->ally_code }}</li>
                        @endforeach
                    </ul>
                </div>

                @foreach($guilds as $guild)
                <div class="card-body">
                    <h2>{{ $guild->name }}</h2>

                    <auto-text-field :route="`{{ route('guild.profile.update', ['guild' => $guild->id]) }}`" :label="`Server ID`" :value="`{{ $guild->server_id }}`"></auto-text-field>

                    @foreach ($guild->members->sortBy('player') as $member)
                    <div class="input-group discord-select row no-margin">
                        <div class="input-group-prepend">
                            <span class="input-group-text">{{ $member->player }} ({{ $member->ally_code }})</span>
                        </div>
                        <v-select :options="['Frax#4201']" :placeholder="`Pick Discord User`"></v-select>
                    </div>
                    @endforeach
                </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')