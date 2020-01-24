@extends('layouts.html')

@section('viewport', '600')

@push('styles')
    <link href="{{ asset('css/welcome.css') }}" rel="stylesheet">
@endpush

@section('body')
<div class="the-poster card">
    <h1>The Schwartz Alliance</h1>
    <h2>Recruiting Dedicated Players</h2>
    <img src="/images/welcome/spaceballs.png">
    <table>
        <thead>
            <tr>
                <th><div>Guild</div></th>
                <th><div>GP</div></th>
                <th><div>TB•Stars</div></th>
                <th><div>Focus</div></th>
                <th><div>Raids</div></th>
            </tr>
        </thead>
        <tbody class="rainbow-colors">
        @foreach ($guilds as $guild)
            <tr>
                <td><div>{{ $guild->name }}</div></td>
                <td><div>{{ intval(floor($guild->gp / 1000000)) }}M</div></td>
                <td><div>{{ $guild->tb }}•{{ $guild->stars }}</div></td>
                <td><div>{{ $guild->focus }}</div></td>
                <td><div>{{ $guild->raids }}</div></td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="discord-link">HTTPS://DISCORD.GG/KZ5<span class="small">tv</span>RU</div>
</div>
@endsection