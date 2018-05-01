@extends('layouts.html')

@push('styles')
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
@endpush

@section('body')
    <div id="app">
        @include('shared.nav')

        <main class="flex-center outer">
            <div class="content">
                @yield('content')
            </div>
        </main>
    </div>
@endsection
