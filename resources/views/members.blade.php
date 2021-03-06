@extends('layouts.app')
@section('title', '—Players')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            @include('shared.status')

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

            <div class="card radiant-back">
                <div class="card-header row no-margin justify-content-between align-items-center">
                    <h2>Your Accounts</h2>

                    <expanda-text name="ally_code" action="{{ route('members.register') }}" icon="person-add">
                        @csrf
                    </expanda-text>
                </div>

                <div class="card-body">
                    <div class="guild-list">
                    @forelse(auth()->user()->accounts->sortByDesc('gp') as $member)
                        <div class="row cut-corner">
                            <member-row :member="{{ $member->toJson() }}"></member-row>
                        </div>
                    @empty
                        <div>No accounts found for the current user 😞</div>
                    @endforelse
                    </div>
                </div>
            </div>

            <div class="card radiant-back">
                <div class="card-header"><h2>Find a Player</h2></div>
                <div class="card-body guild-list">
                <search :url="'{{ route('search.members') }}'" :help-note="`Searches player name and ally code of any player that has been previously scraped`" :results-class="['row', 'cut-corner']" v-slot="result">
                    <member-row :member="result.item"></member-row>
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