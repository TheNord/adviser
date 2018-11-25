@extends('layouts.app')

@section('content')
    @include('admin._nav')

    <form method="POST" action="{{ route('admin.regions.update', $region) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name" class="col-form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   value="{{ $region->name }}" required>
            @if($errors->has('name'))
                <span class="invalid-feedback"><strong>{{ $errors->first('name') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <label for="Slug" class="col-form-label">Slug</label>
            <input type="text" id="slug" name="slug"
                   class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" value="{{ $region->slug }}"
                   required>
            @if($errors->has('slug'))
                <span class="invalid-feedback"><strong>{{ $errors->first('slug') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
    </form>

@endsection