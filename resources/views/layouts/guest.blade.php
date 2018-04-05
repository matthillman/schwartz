@extends('layouts.html')

@push('styles')
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/login.css') }}" rel="stylesheet">
@endpush

@section('body')
    <div id="app">
        @include('shared.nav')

        <main class="flex-center outer">
            @yield('content')
        </main>
    </div>
@endsection
