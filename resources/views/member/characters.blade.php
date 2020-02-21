@extends('layouts.app')
@section('title')—{{ $member->player }}—Characters @endsection
@section('content')
<div class="container member-profile">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-baseline">
                    <button type="button" @@click="back" class="btn btn-dark btn-icon back-button">
                        <ion-icon name="chevron-back-circle" size="medium"></ion-icon>
                    </button>
                    <h2 class="flex-grow">{{ $member->player }}'s Characters</h2>
                    <highlight-widget :starting="'gear'" class="flex-stay"></highlight-widget>
                </div>
                <div class="card-body">
                    <div class="col-md-12 character-filter-wrapper">
                        <v-select
                            multiple
                            :options="{{ $categories->flatten()->toJson() }}"
                            label="description"
                            :value="[{{ is_null($selected_category) ? null : $selected_category->toJson() }}]"
                            @@input="val => go(`{{ route('member.characters', ['ally' => $member->ally_code]) }}${val.length ? `?category=${val[val.length - 1].category_id}` : ''}`)"
                        ></v-select>
                    </div>
                </div>
                <div class="card-body character-list" highlight="gear" v-highlight:[highlight]>
                    @foreach ($member->characters()->with('zetas')->where('combat_type', 1)->get()->sortByDesc('power')->filter(function($char) use ($selected_category) {
                        return is_null($selected_category) || in_array($selected_category->category_id, $char->category_list);
                    })->chunk(6) as $chunk)
                    <div class="col-md-12 row">
                        @foreach ($chunk as $character)
                        <a class="col-md-2 character-wrapper" href="{{ route('member.character', ['ally' => $member->ally_code, 'id' => $character->unit_name ]) }}">
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
                </div>
            </div>

        </div>
    </div>
</div>
@endsection