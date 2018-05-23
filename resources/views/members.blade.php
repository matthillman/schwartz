@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>STR Teams</h2></div>

                <div class="card-body">
                    <div class="member-list">
                    @foreach($members as $member)
                        <div class="member">
                            <div class="row">
                                <div>{{ $member->player }}</div>
                                <a href="https://swgoh.gg{{ $member->url }}" target="_gg" class="gg-link">
                                    @include('shared.bb8')
                                </a>
                            </div>
                            <div class="team-set">
                            @foreach(['REYJEDITRAINING', 'BB8', 'R2D2_LEGENDARY', 'REY', 'RESISTANCETROOPER'] as $character)
                                @include('shared.char', [
                                    'character' => $member->characters->firstWhere('unit_name', $character),
                                    'unit' => $units->firstWhere('base_id', $character)
                                ])
                            @endforeach
                            </div>
                        </div>
                    @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
