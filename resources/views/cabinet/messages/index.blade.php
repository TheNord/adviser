@extends('layouts.app')

@section('content')
    @include('cabinet.messages._nav')

    <table class="table table-striped">
        <thead>
        <tr>
            <th>Объявление</th>
            <th>Отправитель</th>
            <th>Дата</th>
            <th>Непрочитанных сообщений</th>
            <th>Действие</th>
        </tr>
        </thead>
        <tbody>

        @foreach ($dialogs as $dialog)
            <tr>
                <td><a href="{{ route('adverts.show', $dialog->advert) }}">{{ $dialog->advert->title }}</a></td>
                <td>
                    @if ($dialog->client->name === $user->name)
                        Вы
                    @else
                    {{ $dialog->client->name }}
                    @endif
                </td>
                <td>{{ $dialog->updated_at }}</td>
                <td>
                    @if (Auth::id() == $dialog->client_id)
                        {{ $dialog->user_new_messages }}
                    @elseif (Auth::id() == $dialog->user_id)
                        {{ $dialog->client_new_messages }}
                    @endif
                </td>

                <td><a href="{{ route('cabinet.messages.show', $dialog) }}">Ответить</a></td>
            </tr>
        @endforeach

        </tbody>
    </table>

    {{ $dialogs->links() }}
@endsection