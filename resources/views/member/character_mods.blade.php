
@extends('layouts.app')
@section('body-class', 'no-bg')
@section('title')—{{ $character->unit->name }}’s Mods @endsection
@section('content')
<div class="container mod-container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>{{ $character->member->player }}: {{ $character->unit->name }}</h2></div>
                <div class="card-body mod-details">
                    @if ($character->mods->count())
                    @foreach (['square', 'arrow', 'diamond', 'triangle', 'circle', 'cross'] as $shape)
                        <div>
                            @if ($character->mods->where('slot', '=', $shape)->count())
                                <mod :mod="{{ $character->mods->where('slot', '=', $shape)->first()->toJson() }}"></mod>
                            @else
                                <div class="mod missing">No {{ $shape }} equipped</div>
                            @endif
                        </div>
                    @endforeach
                    @else
                        <div>No mods found</div>
                    @endif
                </div>
                <div class="card-footer bonuses">
                    @if ($character->mods->count())
                    @foreach ($attributes as $label => $stat)
                        <div>{{ $label }}: +{{ format_stat($character->modTotal($stat), $stat) }}</div>
                    @endforeach
                    @endif
                    @bot
                    <div class="row justify-content-end footer-logo">
                        <div class="logo">@include('shared.logo')</div>
                    </div>
                    @endbot
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')