@extends('layouts.app')
@section('title', '—Squads')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header"><h2>Add a Squad</h2></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('squads.add') }}" >
                        @csrf
                        <div class="row add-row input-group add-squad-row">
                            <input type="hidden" name="leader_id" value="" id="leader_id">
                            <v-select
                                :options="{{ $units->toJson() }}"
                                :placeholder="`Leader`"
                                :label="'name'"
                                @@input="val => set('leader_id', val ? val.base_id : null)"
                            >
                                <template v-slot:option="option">
                                    <div class="portrait-preview">
                                        <img class="character" :src="`/images/units/${ option.base_id }.png`" />
                                        <div class="character-name">
                                            @{{ option.name }}
                                        </div>
                                    </div>
                                </template>
                                <template v-slot:selected-option="option">
                                    <div class="portrait-preview">
                                        <img class="character" :src="`/images/units/${ option.base_id }.png`" />
                                        <div class="character-name">
                                            @{{ option.name }}
                                        </div>
                                    </div>
                                </template>
                            </v-select>
                            <input class="form-control" type="text" placeholder="Squad Name" name="name">
                            <input class="form-control" type="text" placeholder="Squad Description" name="description">
                            <button type="submit" class="btn btn-primary">{{ __('Add') }}</button>
                        </div>
                        <div class="row add-row input-group add-squad-row multiple">
                            <input type="hidden" name="other_members" value="" id="other_members">
                            <v-select
                                multiple
                                :options="{{ $units->toJson() }}"
                                :placeholder="`Other Members`"
                                :label="'name'"
                                @@input="val => set('other_members', val.map(u => u.base_id).reduce((c, u) => [c, u].join(',')))"
                            >
                                <template v-slot:option="option">
                                    <div class="portrait-preview">
                                        <img class="character" :src="`/images/units/${ option.base_id }.png`" />
                                        <div class="character-name">
                                            @{{ option.name }}
                                        </div>
                                    </div>
                                </template>
                                <template v-slot:selected-option="option">
                                    <div class="portrait-preview">
                                        <img class="character" :src="`/images/units/${ option.base_id }.png`" />
                                        <div class="character-name">
                                            @{{ option.name }}
                                        </div>
                                    </div>
                                </template>
                            </v-select>
                        </div>
                    </form>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h2>Squads</h2></div>

                <div class="card-body">
                    <p>Send add messages for checked squads</p>

                    <form method="POST" :action="`/squads/message/${messageChannel}`">
                        @method('PUT')
                        @csrf
                        <div class="row add-row input-group">
                            <input type="hidden" name="squads" :value="selectedSquadArray.join(',')">
                            <input class="form-control" type="text" placeholder="Channel ID" v-model="messageChannel">
                            <button type="submit" class="btn btn-primary">{{ __('Send Messages') }}</button>
                        </div>
                    </form>
                </div>

                <div class="card-body squad-list-body">

                    @foreach ($squads as $squad)
                        <div class="row align-items-center squad-row">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" v-model="selectedSquadArray" :value="{{ $squad->id }}">
                            </div>
                            <div class="grow">
                                <div>{{ $squad->display }}</div>
                                <div class="small-note">{{ $squad->description }}</div>
                            </div>
                            <div class="row align-items-start no-margin">
                                <span>Leader:</span>
                                <div class="column char-image-column">
                                    <div class="char-image-square medium {{ $units->where('base_id', $squad->leader_id)->first()->alignment }}">
                                        <img src="/images/units/{{$squad->leader_id}}.png">
                                    </div>
                                    <div class="char-name">{{ $units->where('base_id', $squad->leader_id)->first()->name }}</div>
                                </div>
                            </div>
                            <div class="row align-items-start no-margin">
                                <span>Members:</span>
                                @foreach (explode(',', $squad->additional_members) as $char_id)
                                    <div class="column char-image-column">
                                        <div class="char-image-square medium {{ $units->where('base_id', $char_id)->first()->alignment }}">
                                            <img src="/images/units/{{$char_id}}.png">
                                        </div>
                                        <div class="char-name">{{ $units->where('base_id', $char_id)->first()->name }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <form method="POST" action="{{ route('squad.delete', ['id' => $squad->id ]) }}">
                                @method('DELETE')
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon"><ion-icon name="trash" size="medium"></ion-icon></button>
                            </form>
                        </div>
                    @endforeach

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
