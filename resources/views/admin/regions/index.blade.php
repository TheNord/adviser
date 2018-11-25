@extends('layouts.app')

@section('content')
    @include('admin._nav')


    <p><a href="{{ route('admin.regions.create') }}" class="btn btn-primary">Add Region</a></p>

    <table class="table table-bordered table-striped">
        <thead>
        <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Slug</th>
        </tr>
        </thead>
        <tbody>

        @foreach($regions as $region)
            <tr>
                <td>{{ $region->id }}</td>
                <td><a href="{{ route('admin.regions.show', $region)}}">{{ $region->name }}</a></td>
                <td>{{ $region->slug }}</td>
            </tr>
        @endforeach

        </tbody>
    </table>

    {{ $regions->links() }}
@endsection