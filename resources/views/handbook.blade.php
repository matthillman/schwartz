@extends('layouts.app')

@section('content')
<div class="container">
    <div class="markdown">
    @foreach($sections as $section)
        <div>{!! $section !!}</div>
    @endforeach
    </div>
</div>
@endsection