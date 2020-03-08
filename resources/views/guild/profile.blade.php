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
                        <h2>Guild Server IDs</h2>
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

                <div class="card-body">
                @foreach($guilds as $guild)
                    <auto-text-field :route="`{{ route('guild.profile.update', ['guild' => $guild->id]) }}`" :label="`{{ $guild->name }}`" :value="`{{ $guild->server_id }}`"></auto-text-field>
                @endforeach
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')