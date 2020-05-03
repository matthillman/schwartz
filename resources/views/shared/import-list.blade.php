@extends('layouts.app')

@section('viewport', '830')

@section('content')
<div class="container guild-stats">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><h2>{{ $title }}</h2></div>

                <div class="card-body">
                    <table>
                        <thead>
                            @foreach ($columns as $id => $heading)
                            <th>{{ $heading }}</th>
                            @endforeach
                        </thead>
                        <tbody>
                            @foreach ($data as $item)
                            <tr>
                                @foreach ($columns as $id => $heading)
                                    @if (is_array($item))
                                    <td>{{ $item[$id] }}</td>
                                    @else
                                    <td>{{ $item->{$id} }}</td>
                                    @endif
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
