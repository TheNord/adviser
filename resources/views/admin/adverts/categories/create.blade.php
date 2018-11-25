@extends('layouts.app')

@section('content')
    @include('admin._nav')

    <form method="POST" action="{{ route('admin.adverts.categories.store') }}">
        @csrf

        <div class="form-group">
            <label for="name" class="col-form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                   value="{{ old('name') }}" required>
            @if($errors->has('name'))
                <span class="invalid-feedback"><strong>{{ $errors->first('name') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <label for="slug" class="col-form-label">Slug</label>
            <input type="text" id="slug" name="slug"
                   class="form-control {{ $errors->has('slug') ? 'is-invalid' : '' }}" value="{{ old('slug') }}"
                   required>
            @if($errors->has('slug'))
                <span class="invalid-feedback"><strong>{{ $errors->first('slug') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <label for="parent" class="col-form-label">Parent</label>
            <select name="parent" id="parent"  class="form-control {{ $errors->has('parent') ? 'is-invalid' : '' }}">
                <option value=""></option>
                @foreach ($parents as $parent)
                    <option value="{{ $parent->id }}" {{ $parent->id == old('parent') ? ' selected' : '' }}>
                        @for($i = 0; $i < $parent->depth; $i++) &mdash; @endfor
                        {{ $parent->name }}
                    </option>
                @endforeach
            </select>
            @if($errors->has('parent'))
                <span class="invalid-feedback"><strong>{{ $errors->first('parent') }}</strong></span>
            @endif
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Create</button>
        </div>
    </form>

@endsection