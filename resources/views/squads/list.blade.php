@extends('layouts.app')
@section('title', '—Squads')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('shared.status');

            <div class="card">
                <div class="card-header"><h2>Squads</h2></div>

                <squad-tabs
                    :groups="{{ $groups->toJson() }}"
                    :guilds="{{ $guilds->toJson() }}"
                    :selected="{{ $group->id }}"
                ></squad-tabs>

                <div class="card-header row justify-content-between align-items-baseline no-margin">
                    <div class="column grow"><h4>{{ $group->name }}</h4><div class="small-note"><strong>{{ $group->guild_id === -1 ? "Personal" : ($group->guild_id === 0 ? "Global" : "{$group->guild->name}") }}</strong> Squad Group</div></div>

                    <div class="column">
                        @if ($group->guild_id > 0)
                        @can('edit-guild', $group->guild_id)
                        <convert-squad-to-plan
                            :group="{{ $group->toJson() }}"
                            :plans="{{ $group->plans->sortBy('name')->toJson() }}"
                        ></convert-squad-to-plan>
                        @endcan
                        @endif

                        <popover class="teams" :name="`guild-squads`">
                            <div slot="face">
                                <button class="btn btn-primary btn-icon with-text"><ion-icon name="eye" size="small"></ion-icon><span>View For Guild</span></button>
                            </div>
                            <div slot="content">
                                <ul>
                                @foreach ($guilds->where('value', '>', 0) as $guild)
                                    <li>
                                        <a href="{{ route('guild.members', ['guild' => $guild['value'], 'team' => $group->id, 'mode' => 'guild', 'index' => 0]) }}">{{ $guild['label'] }}</a>
                                    </li>
                                @endforeach
                                </ul>
                            </div>
                        </popover>
                    </div>

                    @if ($group->guild_id >= 0)
                    @can('edit-squad', $group)
                    <div class="column space-left">
                        <auto-checkbox button :route="`{{ route('squads.group.publish', ['group' => $group->id]) }}`" {{ $group->publish ? 'checked ' : '' }}:label="`{{ $group->guild_id !== 0 ? "Publish to Guild List" : "Publish Globally" }}`"></auto-checkbox>
                    </div>
                    @endcan
                    @endif
                </div>

                <collapsable card-body {{ !is_null($edit_squad->id)|| $chars->count() == 0 && $ships->count() == 0 ? 'start-open' : '' }}>
                    @if(is_null($edit_squad->id))
                    <template #top-trigger="{ open }">
                        <button class="btn btn-primary btn-icon-text">
                            <div class="row no-margin align-items-center">
                                <ion-icon :name="open ? `chevron-down` : `chevron-forward`"></ion-icon> <span>Add a Squad</span>
                            </div>
                        </button>
                    </template>
                    @endif

                    <div class="card-body squad-row-toggle">
                        <form method="POST" action="{{ route('squads.add') }}" >
                            @csrf
                            <input type="hidden" name="id" value="{{$edit_squad->id}}" id="id">
                            <input type="hidden" name="leader_id" value="{{$edit_squad->leader_id}}" id="leader_id">
                            <input type="hidden" name="group" value="{{ $group->id }}">
                            <input type="hidden" name="other_members" value="{{implode(',', $edit_squad->additional_members ?: [])}}" id="other_members">

                            <div class="row no-margin input-group add-squad-row">
                                <unit-select
                                    :placeholder="`Leader`"
                                    @if(!is_null($edit_squad->id))
                                    :value="{{ $units->where('base_id', $edit_squad->leader_id)->first()->toJson() }}"
                                    @endif
                                    @@input="val => set('leader_id', val ? val.base_id : null)" required
                                ></unit-select>
                                <input class="form-control" type="text" placeholder="Squad Name" id="name" name="name" value="{{$edit_squad->display}}" required>
                                <input class="form-control" type="text" placeholder="Squad Description" id="description" name="description" value="{{$edit_squad->description}}" required>
                                <button type="submit" class="btn btn-primary">{{ !is_null($edit_squad->id) ? __('Save') : __('Add') }}</button>
                            </div>
                            <div class="row no-margin input-group add-squad-row multiple">
                                <unit-select multiple
                                    :placeholder="`Other Members`"
                                    @if(!is_null($edit_squad->id))
                                    :value="{{ $units->whereIn('base_id', $edit_squad->additional_members)->values()->toJson() }}"
                                    @endif
                                    @@input="val => set('other_members', val.map(u => u.base_id).reduce((c, u) => [c, u].join(','), '') )"
                                ></unit-select>
                            </div>
                        </form>
                    </div>
                </collapsable>

                @foreach ([$chars, $ships] as $squads)
                    @if ($squads->count() > 0)
                    <div class="card-body squad-list-body dark-back">
                        <table class="squad-table">
                            <thead>
                                <tr>
                                    <th class="blank">&nbsp;</th>
                                    <th><span>Team</span></th>
                                    <th><span>Leader</span></th>
                                    <th colspan="{{ count($squads->max('additional_members')) }}"><span>Members</span></th>
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
                                        @forelse ($squad->additional_members as $char_id)
                                            <td class="top">
                                                <div class="column char-image-column">
                                                    <div class="char-image-square medium {{ $units->where('base_id', $char_id)->first()->alignment }}">
                                                        <img src="/images/units/{{$char_id}}.png">
                                                    </div>
                                                    <div class="char-name">{{ $units->where('base_id', $char_id)->first()->name }}</div>
                                                </div>
                                            </td>
                                        @empty
                                            @if (count($squads->max('additional_members')) === 0)
                                            <td><div>&nbsp;</div></td>
                                            @endif
                                        @endforelse
                                        @for ($i = 0; $i < count($squads->max('additional_members')) - count($squad->additional_members); $i++)
                                        <td><div>&nbsp;</div></td>
                                        @endfor
                                        <td class="blank">
                                            <div class="column justify-content-around align-items-center">
                                                <button @@click="go(`{{ route('squads', ['group' => $group->id, 'squad' => $squad->id ]) }}`)" class="btn btn-primary btn-icon"><ion-icon name="pencil" size="small"></ion-icon></button>
                                                <form class="column justify-content-center align-items-center" method="POST" action="{{ route('squad.delete', ['id' => $squad->id ]) }}">
                                                    @method('DELETE')
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-icon"><ion-icon name="trash" size="small"></ion-icon></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                    @endif
                @endforeach

                @if ($chars->count() == 0 && $ships->count() == 0)
                    <div class="card-body">
                        <h4>No squads configured</h4>
                    </div>
                @else

                 @if($group->guild_id == 0)
                    <collapsable card-body>
                    @endif
                        <form method="POST" :action="`/squads/message/{{ $group->guild_id > 0 ? $group->guild->admin_channel : '${messageChannel}' }}`">
                            @method('PUT')
                            @csrf
                            <div class="row no-margin add-row input-group align-items-baseline">
                                <input type="hidden" name="squads" :value="selectedSquadArray.join(',')">
                                @if($group->guild_id == 0)
                                <label for="discord-channel">Discord Channel ID:</label>
                                <input class="form-control" id="discord-channel" type="text" placeholder="Channel ID" v-model="messageChannel">
                                @endif
                                <button type="submit" :disabled="!selectedSquadArray.length" class="btn btn-primary">{{ $group->guild_id > 0 ? __('Send add messages for checked squads') : __('Send Messages') }}</button>
                            </div>
                        </form>

                    @if($group->guild_id == 0)
                        <template #top-trigger="{ open }">
                            <button class="btn btn-primary btn-icon-text">
                                <div class="row no-margin align-items-center">
                                    <ion-icon :name="open ? `chevron-down` : `chevron-forward`"></ion-icon> <span>Send add messages for checked squads</span>
                                </div>
                            </button>
                        </template>
                    </collapsable>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
