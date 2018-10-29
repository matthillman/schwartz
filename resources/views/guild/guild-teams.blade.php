@extends('layouts.app')

@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-center">
                    <h2>Guild Teams</h2>

                    <div>
                        <form method="GET" action="{{ route('guild.members', ['guild' => $guild->id, 'team' => $team, 'mode' => 'members']) }}" >
                            <div class="row add-row">
                                <button type="submit" class="btn btn-primary">{{ __('Group by Member') }}</button>
                            </div>
                        </form>
                    </div>
                </div>

                @foreach($teams as $title => $team)
                    <div class="card-body" highlight="{{$highlight}}">
                        <h1>{{ $title }}</h1>
                        @include('shared.member_table', [
                            'characters' => $team
                        ])
                    </div>
                @endforeach

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')