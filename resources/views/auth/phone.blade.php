@extends('layouts.app')

@section('content')
    <form method="post" action="{{ route('login.phone') }}">
        @csrf
        <div class="form-group">
            <label for="token" class="col-form-label">SMS Code</label>
            <input id="token" name="token" type="text" class="form-control {{ $errors->has('token') ? ' is-invalid' : ''}}" required>
            @if ($errors->has('token'))
                <span class="invalid-feedback"><strong>{{ $errors->first('token') }}</strong></span>
            @endif
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Verify</button>
        </div>
    </form>
@endsection
