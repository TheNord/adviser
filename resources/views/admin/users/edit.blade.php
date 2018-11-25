@extends('layouts.app')

@section('content')
    @include('admin._nav')

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="col-form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   value="{{ $user->name }}" required>
            @if($errors->has('name'))
                <span class="invalid-feedback"><strong>{{ $errors->first('name') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <label for="email" class="col-form-label">Email</label>
            <input type="text" id="email" name="email"
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ $user->email }}"
                   required>
            @if($errors->has('email'))
                <span class="invalid-feedback"><strong>{{ $errors->first('email') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <label for="password" class="col-form-label">Password</label>
            <input type="password" id="password" name="password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}" value="">
            @if($errors->has('password'))
                <span class="invalid-feedback"><strong>{{ $errors->first('password') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <label for="role" class="col-form-label">Role</label>
            <select name="role" id="role" class="form-control {{ $errors->has('role') ? 'is-invalid' : '' }}">
                @foreach ($roles as $value => $label)
                    <option value="{{ $value }}" {{ $value === $user->role ? ' selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @if($errors->has('role'))
                <span class="invalid-feedback"><strong>{{ $errors->first('role') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>

@endsection