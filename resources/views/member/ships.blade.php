@extends('layouts.app')
@section('title')—{{ $member->player }}—Ships @endsection
@section('content')
<div class="container member-profile">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-baseline">
                    @include('shared.back')
                    <h2 class="flex-grow">{{ $member->player }}'s @isset($selected_category) <strong>{{ $selected_category->description }}</strong>@endisset Ships</h2>
                </div>
                @person
                <div class="card-body">
                    <div class="col-12 character-filter-wrapper">
                        <v-select
                            multiple
                            :options="{{ $categories->flatten()->toJson() }}"
                            label="description"
                            :value="[{{ is_null($selected_category) ? null : $selected_category->toJson() }}]"
                            @@input="val => go(`{{ route('member.ships', ['ally' => $member->ally_code]) }}${val.length ? `?category=${val[val.length - 1].category_id}` : ''}`)"
                        ></v-select>
                    </div>
                </div>
                @endperson

                @foreach ($member->characters()->where('combat_type', 2)->get()->partition(function ($u) { return $u->is_capital_ship; }) as $units)
                <div class="card-body character-list dark-back" highlight="none">
                    @foreach ($units->sortByDesc('power')->filter(function($char) use ($selected_category) {
                        return is_null($selected_category) || in_array($selected_category->category_id, $char->category_list);
                    })->chunk(6) as $chunk)
                    <div class="col-12 row">
                        @foreach ($chunk as $character)
                        <a class="col-2 character-wrapper" href="{{ route('member.character', ['ally' => $member->ally_code, 'id' => $character->unit_name ]) }}">
                            @include('shared.char', [
                                'character' => $character,
                                'noMods' => true,
                                'showName' => true,
                                'size' => 'large',
                            ])
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                    @if($units->first()->is_capital_ship)
                    <div class="col-12 row no-margin justify-content-around glass-back">
                        <div>Materials to Max Capital Ship Abilities</div>

                        @forelse ($units->pluck('ability_materials_needed')->filter(function($i) { return !empty($i); })->reduce(function($total, $needed) {
                            return collect($needed)->keys()->mapWithKeys(function($icon) use ($needed, $total) {
                                return [$icon => ($total[$icon] ?? 0) + $needed[$icon]];
                            });
                        }, []) as $icon => $amount)
                            <div>
                                <img src="/images/units/abilities/{{ $icon }}.png" width="22">
                                <span>{{ number_format($amount) }}</span>
                            </div>
                        @empty
                            <div>All Capital Ships Maxed!</div>
                        @endforelse
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

        </div>
    </div>
</div>
@endsection