@extends('layouts.app')

@section('content')
<div class="container home">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header row justify-content-between align-items-center">
            @if (isset($team->name))
                    <h2>Edit {{ $team->name }}</h2>
            @else
                    <h2>{{ __('Add Territory War Team') }}</h2>
            @endif
                    <div class="row add-row">
                        <a href="{{ url()->previous() }}" class="btn btn-primary">{{ __('Back') }}</a>
                    </div>
                </div>

            @if (isset($team->name))
                <form method="POST" action="{{ route('tw-teams.update', $team->id) }}">
                    @method('PUT')
            @else
                <form method="POST" action="{{ route('tw-teams.store') }}">
            @endif
                    @csrf
                    <div class="card-body">
                        @if (session('twStatus'))
                            <div class="alert alert-success">
                                {{ session('twStatus') }}
                            </div>
                        @endif

                        <div>
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Team Name') }}</label>

                                <div class="col-md-6">
                                    <input id="name" type="text"
                                        class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                        name="name" value="{{ old('name', optional($team)->name) }}"
                                        required autofocus>

                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('name') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="aliases" class="col-md-4 col-form-label text-md-right">{{ __('Aliases') }}</label>

                                <div class="col-md-6">
                                    <input id="aliases" type="text"
                                        class="form-control{{ $errors->has('aliases') ? ' is-invalid' : '' }}"
                                        name="aliases" value="{{ old('aliases', optional($team)->aliases) }}"
                                        placeholder="separated, by, commas"
                                        required>

                                    @if ($errors->has('aliases'))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first('aliases') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <tw-form>
                        <template slot-scope="slot">
                        @foreach(old('counters', optional($team)->counters) as $index => $counter)
                        <div class="counter">
                            <div class="form-group row">
                                <label for="counters" class="col-md-4 col-form-label text-md-right">{{ __('Team') }}</label>

                                <div class="col-md-6">
                                    <input type="text"
                                        class="form-control{{ $errors->has("counter.$index") ? ' is-invalid' : '' }}"
                                        name="counter[]" value="{{ old("counter.$index", $counter['name']) }}"
                                        placeholder="Name"
                                        required>

                                    @if ($errors->has("counter.$index"))
                                        <span class="invalid-feedback">
                                            <strong>{{ $errors->first("counter.$index") }}</strong>
                                        </span>
                                    @endif
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger" @@click.prevent="slot.delete">Delete</button>
                                </div>
                            </div>
                            <div class="form-group row border-bottom">
                                <label for="counters" class="col-md-4 col-form-label text-md-right">{{ __('Notes') }}</label>

                                <div class="col-md-6">
                                    <input id="notes-{{$index}}" type="text"
                                        class="form-control{{ $errors->has("notes.$index") ? ' is-invalid' : '' }}"
                                        name="notes[]" value="{{ old("notes.$index", $counter['description']) }}"
                                    >
                                </div>
                            </div>
                        </div>
                        @endforeach
                        </template>
                    </tw-form>

                    <div class="card-body">
                        <div class="form-group row mb-0">
                            <div class="row justify-content-center col-md-12 spacing">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Save') }}
                                </button>
                                <a href="{{ url()->previous() }}" class="btn btn-primary">{{ __('Cancel') }}</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
