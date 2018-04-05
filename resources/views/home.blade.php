@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Guides</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="guides">
                        <a href="{{ guide('territory-wars-guide')}}">Territory Wars Guide</a>
                    </div>
                </div>
            </div>
@auth('admin')
            <div class="card">
                <div class="card-header">User Requests</div>

                <div class="card-body">

                    Users that have registered will show card-header
                </div>
            </div>

            <div class="card">
                <div class="card-header">Recruiting</div>

                <div class="card-body">

                    Users that have registered will show card-header
                </div>
            </div>
@endauth
        </div>
    </div>
</div>
@endsection
