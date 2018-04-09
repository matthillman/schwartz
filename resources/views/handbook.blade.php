@extends('layouts.app')

@section('content')
    <div class="markdown">
        @foreach($sections as $section)
            <h1 class="title">{{ $section['title'] }}</h1>
            @foreach ($section['content'] as $sub)
                <div>{!! $sub !!}</div>
            @endforeach
        @endforeach
    </div>
@endsection