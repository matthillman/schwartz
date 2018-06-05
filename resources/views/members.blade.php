@extends('layouts.app')

@section('content')
<div class="container guild-members">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>STR Teams</h2></div>

                <div class="card-body">
                    <h1>RJT</h1>
                    @include('shared.member_table', [
                        'characters' => ['REYJEDITRAINING', 'BB8', 'R2D2_LEGENDARY', 'REY', 'RESISTANCETROOPER', 'VISASMARR', 'HERMITYODA']
                    ])
                </div>
                <div class="card-body">
                    <h1>Chex</h1>
                    @include('shared.member_table', [
                        'characters' => ['COMMANDERLUKESKYWALKER', 'HANSOLO', 'DEATHTROOPER', 'CHIRRUTIMWE', 'PAO', 'CT7567', 'ANAKINKNIGHT']
                    ])
                </div>
                <div class="card-body">
                    <h1>Nightsister</h1>
                    @include('shared.member_table', [
                        'characters' => ['ASAJVENTRESS', 'DAKA', 'TALIA', 'NIGHTSISTERACOLYTE', 'NIGHTSISTERINITIATE', 'NIGHTSISTERZOMBIE', 'MOTHERTALZIN']
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
