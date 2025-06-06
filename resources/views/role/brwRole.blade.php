@extends('layouts.layout')

@php
    $title='Roles';
    $subTitle = 'Security / Roles';
    $script= "";
@endphp

@section('content')
<div class="col-span-12 lg:col-span-6">
    <div class="card border-0 overflow-hidden">
        <div class="card-header">
            <h5 class="card-title text-lg mb-0">Striped Rows</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table striped-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">id</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Role</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr class="odd:bg-neutral-100 dark:odd:bg-neutral-600">
                                <td>{{$role->id}}</td>
                                <td>{{$role->name}}</td>
                                <td>
                                    @if ($role->id != 1)
                                        <a href="{{ route('role.edit', ['id' => $role->id]) }}" class="btn btn-light btn-sm">Edit</a>

                                        <form method="DELETE" action="{{ route('role.destroy', ['id' => $role->id]) }}" >
                                            @csrf
                                            <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                        </form>

                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- card end -->
</div>
@endsection
