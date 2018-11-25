@extends('layouts.app')

@section('content')
    @include('admin._nav')

    <div class="d-flex flex-row mb-3">
        <a href="{{ route('admin.regions.create', ['parent' => $region->id]) }}" class="btn btn-primary mr-3">Add Sub-Region</a>
        <a href="{{ route('admin.regions.edit', $region) }}" class="btn btn-primary mr-3">Edit</a>
        <form method="POST" action="{{route('admin.regions.update', $region)}}" class="mr-1">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger">Delete</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <tbody>
        <tr>
            <th>ID</th>
            <td>{{ $region->id }}</td>
        </tr>
        <tr>
            <th>Name</th>
            <td>{{ $region->name }}</td>
        </tr>
        <tr>
            <th>Slug</th>
            <td>{{ $region->slug }}</td>
        </tr>
        </tbody>
    </table>

    @if ($region->haveRegions())
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Slug</th>
            </tr>
            </thead>
            <tbody>
            <br />
            <h3>Regions list:</h3>
            @foreach($regions as $region)
                <tr>
                    <td>{{ $region->id }}</td>
                    <td><a href="{{ route('admin.regions.show', $region)}}">{{ $region->name }}</a></td>
                    <td>{{ $region->slug }}</td>
                </tr>
            @endforeach

            </tbody>
        </table>
    @endif


@endsection