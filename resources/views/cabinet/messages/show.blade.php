@extends('layouts.app')

@section('content')

    @foreach ($dialog->messages()->orderBy('id')->get() as $message)
        <div class="card mb-3">
            <div class="card-header">
                {{ $message->created_at }} by {{ $message->user->name }}
            </div>
            <div class="card-body">
                {!! nl2br(e($message->message)) !!}
            </div>
        </div>
    @endforeach

    <form method="POST" action="{{ route('cabinet.messages.send', $dialog) }}">
        @csrf
        <div class="form-group">
                <textarea class="form-control{{ $errors->has('message') ? ' is-invalid' : '' }}" name="message" rows="3"
                          required>{{ old('message') }}</textarea>
            @if ($errors->has('message'))
                <span class="invalid-feedback"><strong>{{ $errors->first('message') }}</strong></span>
            @endif
        </div>

        <div class="form-group mb-0">
            <button type="submit" class="btn btn-primary">Send Message</button>
        </div>
    </form>

@endsection
