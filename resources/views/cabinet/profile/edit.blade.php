@extends('layouts.app')

@section('content')
    @include('cabinet._nav')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <form method="POST" action="{{ route('cabinet.profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="name" class="col-form-label">First Name</label>
                    <input type="text" id="name" name="name" class="form-control {{$errors->has('name') ? ' is-invalid' : ''}}" value="{{ $user->name }}" required>
                    @if ($errors->has('name'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('name') }}</strong></span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="last_name" class="col-form-label">First Name</label>
                    <input type="text" id="last_name" name="last_name" class="form-control {{$errors->has('last_name') ? ' is-invalid' : ''}}" value="{{ $user->last_name }}" required>
                    @if ($errors->has('last_name'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('last_name') }}</strong></span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="phone" class="col-form-label">Phone</label>
                    <input type="tel" id="phone" name="phone" class="form-control {{$errors->has('phone') ? ' is-invalid' : ''}}" value="{{ $user->phone }}" required>
                    @if ($errors->has('phone'))
                        <span class="invalid-feedback"><strong>{{ $errors->first('phone') }}</strong></span>
                    @endif
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
@endsection
