@extends('layouts.app')

@section('viewport', '830')

@section('content')
<div class="container guild-stats">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>Guilds</h2></div>

                <div class="card-body">
                    <table>
                        <thead>
                            <th>Name</th>
                            <th>guild_id</th>
                            <th>GP</th>
                        </thead>
                        <tbody>
                            @foreach ($guilds as $guild)
                            <tr>
                                <td>{{ $guild->name }}</td>
                                <td>{{ $guild->guild_id }}</td>
                                <td>{{ $guild->gp }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')