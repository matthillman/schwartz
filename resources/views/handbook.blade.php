@extends('layouts.app')

@section('content')
    <div class="markdown">
    @foreach($sections as $section)
        <div>{!! $section !!}</div>
    @endforeach
    </div>
@endsection