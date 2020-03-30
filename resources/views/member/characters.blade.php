@extends('layouts.app')
@section('title')—{{ $member->player }}—Characters @endsection
@section('content')
<div class="container member-profile">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-baseline">
                    @include('shared.back')
                    <h2 class="flex-grow">{{ $member->player }}'s @isset($selected_category) <strong>{{ $selected_category->description }}</strong>@endisset Characters</h2>
                </div>
                @person
                <div class="card-body">
                    <div class="col-12 character-filter-wrapper">
                        <v-select
                            multiple
                            :options="{{ $categories->flatten()->toJson() }}"
                            label="description"
                            :value="[{{ is_null($selected_category) ? null : $selected_category->toJson() }}]"
                            @@input="val => go(`{{ route('member.characters', ['ally' => $member->ally_code]) }}${val.length ? `?category=${val[val.length - 1].category_id}` : ''}`)"
                        ></v-select>
                    </div>
                </div>
                @endperson
                <div class="card-body character-list dark-back" highlight="none">
                    @foreach ($units->sortByDesc('power')->chunk(6) as $chunk)
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
                    <div class="col-12 row no-margin justify-content-around glass-back">
                        <div>Materials to Max Abilities</div>

                        @forelse (App\Character::materialsNeededForSkills($units->pluck('skill_list')) as $icon => $amount)
                            <div>
                                <img src="/images/units/abilities/{{ $icon }}.png" width="22">
                                <span>{{ number_format($amount) }}</span>
                            </div>
                        @empty
                            <div>All Abilities Maxed!</div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection