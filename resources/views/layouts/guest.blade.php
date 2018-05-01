@extends('layouts.html')

@push('styles')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{ mix('css/login.css') }}" rel="stylesheet">
@endpush

@section('body')
    <div id="app">
        @include('shared.nav')

        <main class="flex-center outer">
            @yield('content')
        </main>
    </div>
@endsection
