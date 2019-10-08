@extends('layouts.app')
@section('body-class', 'no-bg')
@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card stripes">
                <div>
                    Highlighting based on <strong>{{$highlight}}</strong>
                </div>

                    <div class="card-body" highlight="{{$highlight}}">
                        <h1>
                            <a href="https://swgoh.gg{{ $member->url }}" target="_gg">
                                {{ $member->player }}
                            </a>
                        </h1>

                        @foreach($teams as $title => $team)
                            @include('shared.unit_table', [
                                'team' => $title,
                                'characters' => $team
                            ])
                        @endforeach
                    </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')