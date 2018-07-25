@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Guides</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="guides">
                        <a href="{{ guide('territory-wars-guide') }}">Territory Wars Guide</a>
                        <a href="{{ handbook('rots') }}">ROTS Handbook</a>
                        <a href="{{ route('auth.mods') }}">Mod Set Maker</a>
                        <a href="{{ route('tw-teams.index') }}">TW Team Counters</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Guilds</div>

                <div class="card-body">
                    <div class="guides">
                        <a href="{{ route('guilds') }}">Guild Teams</a>
                        <a href="{{ route('schwartz.guilds') }}">Schwartz guilds GP List</a>
                    </div>
                </div>
            </div>

@auth('admin')
            <div class="card">
                <div class="card-header">User Requests</div>

                <div class="card-body">
                    @if (session('userStatus'))
                        <div class="alert alert-success">
                            {{ session('userStatus') }}
                        </div>
                    @endif

                    @forelse($userRequests as $user)
                        <div class="prospective-user">
                            <div>{{ $user->name }} ({{ $user->discord }})</div>
                            <form method="POST" action="{{ route('approve.user', ['id' => $user->id]) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-primary">{{ __('Approve') }}</button>
                            </form>
                            <form method="POST" action="{{ route('approve.admin', ['id' => $user->id]) }}">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning">{{ __('Approve as Admin') }}</button>
                            </form>
                        </div>
                    @empty
                        <div>No pending users</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header">Recruiting</div>

                <div class="card-body">
                    <table>
                        <tbody>
                        @forelse($recruits as $recruit)
                            <tr>
                                <td><a href="{{ $recruit->url }}" target="_blank">{{ $recruit->discord }}</a></td>
                                <td>{{ $recruit->referral }}</td>
                                <td>{{ $recruit->pitch }}</td>
                            </tr>
                        @empty
                            <tr><td>No information requests</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
@endauth
        </div>
    </div>
</div>
@endsection
