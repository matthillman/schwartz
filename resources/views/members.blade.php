@extends('layouts.app')
@section('title', '—Players')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            @if (session('memberStatus'))
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-success">
                        {{ session('memberStatus') }}
                    </div>
                </div>
            </div>
            @endif

            <div class="card radiant-back">
                <div class="card-header"><h2>Compare Players</h2></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('members.refresh') }}" >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="members" id="scrape-members" v-model="memberCompare">
                        <div class="row no-margin justify-content-between align-items-start">
                            <div>Enter ally codes one per line (or check boxes below)</div>
                            <button type="submit" class="btn btn-primary btn-icon striped"><ion-icon name="refresh" size="medium"></ion-icon></button>
                        </div>
                    </form>
                    <form method="POST" action="{{ route('members.post.compare') }}" >
                        @csrf
                        <div class="row add-row align-items-start input-group">
                            <textarea placeholder="Enter ally codes to compare"
                                name="members"
                                class="form-control no-resize"
                                :rows="memberCompareArray.length + 1"
                                v-model="memberCompare"
                            ></textarea>
                            <button type="submit" class="btn btn-primary striped"><span>{{ __('Compare') }}</span></button>
                        </div>
                        <collapsable>
                            <template #top-trigger="{ open }">
                                <div class="row no-margin align-items-center">
                                    <ion-icon :name="open ? `chevron-down` : `chevron-forward`"></ion-icon> <span>Add Custom Units</span>
                                </div>
                            </template>

                            <div class="card-body">
                                    <div class="row add-row input-group add-squad-row multiple">
                                        <input type="hidden" name="units" value="" id="units">
                                        <unit-select multiple
                                            :placeholder="`Leave blank for default unit list`"
                                            @@input="val => set('units', val.map(u => u.base_id).reduce((c, u) => [c, u].join(',')))"
                                        ></unit-select>
                                    </div>
                                </form>
                            </div>
                        </collapsable>
                    </form>
                </div>
            </div>

            @user('accounts')
                <div class="card radiant-back">
                    <div class="card-header"><h2>Your Accounts</h2></div>

                    <div class="card-body">
                        <div class="guild-list">
                        @forelse(auth()->user()->accounts as $member)
                            <div class="row cut-corner">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" v-model="memberCompareArray" :value="`{{ $member->ally_code }}`">
                                </div>
                                <div class="grow">
                                    <h4>{{ $member->player }}</h4>
                                    <div class="small-note">{{ preg_replace('/^(\d{3})(\d{3})(\d{3})$/', "$1–$2–$3", $member->ally_code) }}</div>
                                    <div class="small-note">{{ number_format($member->gp) }} GP</div>
                                </div>

                                <div class="column align-items-end">
                                    <h4>{{ $member->guild->name ?? 'Guildless' }}</h4>

                                    <div class="row no-margin align-items-center justify-content-center item-margin">
                                        <span class="status-indicator" v-if="modJobStatusByAllyCode[`{{ $member->ally_code }}`]">
                                            <svg v-if="modJobStatusByAllyCode[`{{ $member->ally_code }}`] == 'completed'" class="fill-success" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                                            </svg>

                                            <svg v-if="modJobStatusByAllyCode[`{{ $member->ally_code }}`] == 'reserved' || modJobStatusByAllyCode[`{{ $member->ally_code }}`] == 'pending'" class="fill-warning" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                                            </svg>

                                            <svg v-if="modJobStatusByAllyCode[`{{ $member->ally_code }}`] == 'failed'" class="fill-danger" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                                            </svg>
                                        </span>

                                        <button type="button" @@click="go(`/member/{{ $member->ally_code }}`)" class="btn btn-primary btn-icon striped" title="Profile"><ion-icon name="person" size="medium"></ion-icon></button>
                                        <button type="button" @@click="go(`/member/{{ $member->ally_code }}/characters`)"  class="btn btn-primary btn-icon striped" title="Characters"><ion-icon name="people-circle-outline" size="medium"></ion-icon></button>
                                        <button type="button" @@click="go(`/member/{{ $member->ally_code }}/ships`)"  class="btn btn-primary btn-icon striped" title="Ships"><ion-icon name="planet" size="medium"></ion-icon></button>
                                        <a href="{{ $member->url }}" target="_gg" class="gg-link striped round">
                                            @include('shared.bb8')
                                        </a>
                                        <form method="POST" :action="`/member/{{ $member->id }}/refresh`">
                                            @method('PUT')
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-icon striped"><ion-icon name="refresh" size="medium"></ion-icon></button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div>No accounts found for the current user 😞</div>
                        @endforelse
                        </div>
                    </div>
                </div>
            @enduser

            <div class="card radiant-back">
                <div class="card-header"><h2>Find a Player</h2></div>
                <div class="card-body guild-list">
                <search :url="'{{ route('search.members') }}'" :help-note="`Searches player name and ally code of any player that has been previously scraped`" :results-class="['row', 'cut-corner']" v-slot="result">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" v-model="memberCompareArray" :value="result.item.ally_code">
                    </div>
                    <div class="grow">
                        <h4>@{{ result.item.player }}</h4>
                        <div class="small-note">@{{ result.item.ally_code.replace(/^(\d{3})(\d{3})(\d{3})$/, "$1–$2–$3") }}</div>
                        <div class="small-note">@{{ result.item.gp.toLocaleString() }} GP</div>
                    </div>

                    <div class="column align-items-end">

                        <h4>@{{ result.item.guild.name || 'Guildless' }}</h4>

                        <div class="row no-margin align-items-center justify-content-center item-margin">
                            <span class="status-indicator" v-if="modJobStatusByAllyCode[result.item.ally_code]">
                                <svg v-if="modJobStatusByAllyCode[result.item.ally_code] == 'completed'" class="fill-success" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                    <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM6.7 9.29L9 11.6l4.3-4.3 1.4 1.42L9 14.4l-3.7-3.7 1.4-1.42z"></path>
                                </svg>

                                <svg v-if="modJobStatusByAllyCode[result.item.ally_code] == 'reserved' || modJobStatusByAllyCode[result.item.ally_code] == 'pending'" class="fill-warning" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                    <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM7 6h2v8H7V6zm4 0h2v8h-2V6z"/>
                                </svg>

                                <svg v-if="modJobStatusByAllyCode[result.item.ally_code] == 'failed'" class="fill-danger" viewBox="0 0 20 20" style="width: 1.5rem; height: 1.5rem;">
                                    <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm1.41-1.41A8 8 0 1 0 15.66 4.34 8 8 0 0 0 4.34 15.66zm9.9-8.49L11.41 10l2.83 2.83-1.41 1.41L10 11.41l-2.83 2.83-1.41-1.41L8.59 10 5.76 7.17l1.41-1.41L10 8.59l2.83-2.83 1.41 1.41z"/>
                                </svg>
                            </span>
                            <button type="button" @@click="go(`/member/${result.item.ally_code}`)" class="btn btn-primary btn-icon striped" title="Profile"><ion-icon name="person" size="medium"></ion-icon></button>
                            <button type="button" @@click="go(`/member/${result.item.ally_code}/characters`)"  class="btn btn-primary btn-icon striped" title="Characters"><ion-icon name="people-circle-outline" size="medium"></ion-icon></button>
                            <button type="button" @@click="go(`/member/${result.item.ally_code}/ships`)"  class="btn btn-primary btn-icon striped" title="Ships"><ion-icon name="planet" size="medium"></ion-icon></button>
                            <a :href="result.item.url" target="_gg" class="gg-link striped round">
                                @include('shared.bb8')
                            </a>
                            <form method="POST" :action="`/member/${result.item.id}/refresh`">
                                @method('PUT')
                                @csrf
                                <button type="submit" class="btn btn-primary btn-icon striped"><ion-icon name="refresh" size="medium"></ion-icon></button>
                            </form>
                        </div>
                    </div>
                </search>
                </div>

                <div class="card-header"><h2>Add a Player</h2></div>

                <div class="card-body">
                    <form method="POST" action="{{ route('member.add') }}" >
                        @csrf
                        <div>Enter an ally code to add the player. Only needed if the guild has not been scraped previously.</div>
                        <div class="row add-row input-group">
                            <input class="form-control" type="text" name="member">
                            <button type="submit" class="btn btn-primary striped"><span>{{ __('Add Player') }}</span></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('shared.guild_listener')