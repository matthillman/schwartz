@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            {{--
            <form method="POST" action="{{ route('roles.update') }}">
                @csrf
                <button type="submit" class="btn btn-primary">{{ __('Sync Roles') }}</button>
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
                            <div class="icon-wrapper"><a href="{{ route('members') }}"><div class="image"><ion-icon name="person" size="huge"></ion-icon></div></a></div>
                            <div class="label"><a href="{{ route('members') }}">Players</a></div>
                        </div>

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('auth.mods') }}"><div class="image"><div class="icon mod-image diamond speed tier-6 gold giant"></div></div></a></div>
                            <div class="label"><a href="{{ route('auth.mods') }}">Mod Set Maker</a></div>
                        </div>

                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('tw-teams.index') }}"><div class="image"><ion-icon name="help-buoy" size="huge"></ion-icon></div></a></div>
                            <div class="label"><a href="{{ route('tw-teams.index') }}">TW Team Counters</a></div>
                        </div>

                        @if (auth()->user()->edit_teams)
                        <div class="item">
                            <div class="icon-wrapper"><a href="{{ route('squads') }}"><div class="image"><ion-icon name="save" size="huge"></ion-icon></div></a></div>
                            <div class="label"><a href="{{ route('squads') }}">Squad Management</a></div>
                        </div>
                        @endif
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
                <div class="card-header row justify-content-between align-items-center">
                    <div>Recent Jobs</div>

                    <div>
                        <form method="GET" action="{{ route('horizon.index', ['view' => 'recent-jobs']) }}" >
                            <div class="">
                                <button type="submit" class="btn btn-primary">
                                    <div class="flex-center">
                                        <svg class="horizon-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 30 30">
                                            <path class="fill-primary" d="M5.26176342 26.4094389C2.04147988 23.6582233 0 19.5675182 0 15c0-4.1421356 1.67893219-7.89213562 4.39339828-10.60660172C7.10786438 1.67893219 10.8578644 0 15 0c8.2842712 0 15 6.71572875 15 15 0 8.2842712-6.7157288 15-15 15-3.716753 0-7.11777662-1.3517984-9.73823658-3.5905611zM4.03811305 15.9222506C5.70084247 14.4569342 6.87195416 12.5 10 12.5c5 0 5 5 10 5 3.1280454 0 4.2991572-1.9569336 5.961887-3.4222502C25.4934253 8.43417206 20.7645408 4 15 4 8.92486775 4 4 8.92486775 4 15c0 .3105915.01287248.6181765.03811305.9222506z"/>
                                        </svg>
                                        <span>{{ __('Horizon') }}</span>
                                    </div>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body flush">
                    <horizon-jobs></horizon-jobs>
                </div>
            </div>
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
