@extends('layouts.app')
@section('title', '—TW Plan')
@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
           @include('shared.status');

            <div class="card">
                <div class="card-header"><h2>TW Plan: {{ $plan->name }}</h2></div>
            </div>
        </div>
    </div>
</div>