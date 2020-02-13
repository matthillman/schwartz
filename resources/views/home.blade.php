@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            {{--
            <form method="POST" action="{{ route('notify') }}">
                @csrf
                <button type="submit" class="btn btn-primary">{{ __('Test Notifications') }}</button>
            </form>
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
                        <a href="{{ route('schwartz.mods') }}">Schwartz guilds Mod List</a>
                    </div>
                </div>
            </div>
            --}}

            <div class="card">
                <div class="card-header">Tools</div>
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                <div class="card-body">
                    <div class="icon-grid">

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('guilds') }}"><div class="image"><ion-icon name="people" size="huge"></ion-icon></div></a></div>
                            <div class="label"><a href="{{ route('guilds') }}">Guilds</a></div>
                        </div>

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('auth.mods') }}"><div class="image"><div class="icon mod-image diamond speed tier-6 gold giant"></div></div></a></div>
                            <div class="label"><a href="{{ route('auth.mods') }}">Mod Set Maker</a></div>
                        </div>

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('tw-teams.index') }}"><div class="image"><ion-icon name="help-buoy" size="huge"></ion-icon></div></a></div>
                            <div class="label"><a href="{{ route('tw-teams.index') }}">TW Team Counters</a></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Schwartz Guilds</div>

                <div class="card-body">
                    <div class="icon-grid">

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('schwartz.guilds') }}"><div class="image"><ion-icon name="list" size="huge"></ion-icon></div></a></div>
                            <div class="label"><a href="{{ route('schwartz.guilds') }}">GP List</a></div>
                        </div>

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('schwartz.mods') }}"><div class="image"><div class="icon mod-image arrow health tier-6 gold giant"></div></div></a></div>
                            <div class="label"><a href="{{ route('schwartz.mods') }}">Mods</a></div>
                        </div>

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
@endauth
        </div>
    </div>
</div>
@endsection
