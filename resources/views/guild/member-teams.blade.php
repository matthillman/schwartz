@extends('layouts.app')
@section('body-class', 'no-bg')
@section('title')—{{ $guild->name }} (Member Teams)@endsection
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card stripes">
                <div class="card-header row justify-content-between align-items-center">
                    <h2>Guild Teams by Member</h2>

                    <div>
                        <form method="GET" action="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team]) }}" >
                            <div class="row add-row">
                                <button type="submit" class="btn btn-primary">{{ __('Group by Team') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <div class="masonry-grid">
                        @foreach($members as $member)
                            <form method="GET" action="{{ route('member.teams', ['ally' => $member->ally_code, 'team' => $team]) }}">
                                <button type="submit" class="btn btn-primary btn-icon inverted">
                                    <ion-icon name="link" size="medium"></ion-icon>
                                    <h1>
                                        <span>{{ $member->player }}</span>
                                    </h1>
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')