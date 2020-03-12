@extends('layouts.app')
@section('title', '—Units')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2>Units</h2></div>

                <div class="card-body">
                    <div class="unit-list">
                        <div class="row">
                            <div><h3>ID</h3></div>
                            <div><h3>Name</h3></div>
                        </div>

                        <search :url="'{{ route('search.units') }}'" :help-note="`Searches base_id, name and description`" v-slot="result">
                            <div class="row">
                                <div>@{{ result.item.base_id }}</div>
                                <div class="column">
                                    <div>@{{ result.item.name }}</div>
                                    <div class="small-note">@{{ result.item.description }}</div>
                                </div>
                                <a :href="result.item.url" target="_gg" class="gg-link">
                                    @include('shared.bb8')
                                </a>
                            </div>
                        </search>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
