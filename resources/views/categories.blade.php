@extends('layouts.app')
@section('title', '—Units')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2>Categories</h2></div>

                <div class="card-body">
                    <div class="unit-list">
                        <div class="row">
                            <div><h3>Type</h3></div>
                            <div><h3>Description</h3></div>
                        </div>

                        <search :url="'{{ route('search.categories') }}'" :help-note="`Searches category description`" v-slot="result">
                            <div class="row">
                                <div>@{{ result.item.partition }}</div>
                                <div>@{{ result.item.description }}</div>
                            </div>
                        </search>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
