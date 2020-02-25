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

                @if ($squads->count() > 0)
                <div class="card-body squad-list-body">

                    <table class="squad-table">
                        <thead>
                            <tr>
                                <th class="blank">&nbsp;</th>
                                <th><span>Team</span></th>
                                <th><span>Leader</span></th>
                                <th colspan="4"><span>Members</span></th>
                                <th class="blank">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($squads as $squad)
                                <tr class="squad-row">
                                    <td class="blank">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" v-model="selectedSquadArray" :value="{{ $squad->id }}">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="description-wrapper">
                                            <h5>{{ $squad->display }}</h5>
                                            <div class="small-note">{{ $squad->description }}</div>
                                        </div>
                                    </td>
                                    <td class="top">
                                        <div class="column char-image-column">
                                            <div class="char-image-square medium {{ $units->where('base_id', $squad->leader_id)->first()->alignment }}">
                                                <img src="/images/units/{{$squad->leader_id}}.png">
                                            </div>
                                            <div class="char-name">{{ $units->where('base_id', $squad->leader_id)->first()->name }}</div>
                                        </div>
                                    </td>
                                    @foreach (explode(',', $squad->additional_members) as $char_id)
                                        <td class="top">
                                            <div class="column char-image-column">
                                                <div class="char-image-square medium {{ $units->where('base_id', $char_id)->first()->alignment }}">
                                                    <img src="/images/units/{{$char_id}}.png">
                                                </div>
                                                <div class="char-name">{{ $units->where('base_id', $char_id)->first()->name }}</div>
                                            </div>
                                        </td>
                                    @endforeach
                                    <td class="blank">
                                        <form class="column justify-content-center align-items-center" method="POST" action="{{ route('squad.delete', ['id' => $squad->id ]) }}">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-icon"><ion-icon name="trash" size="medium"></ion-icon></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                </div>

                @else
                    <div class="card-body">
                        <h4>No squads configured</h4>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
