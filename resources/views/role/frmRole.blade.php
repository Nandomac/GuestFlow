@extends('layouts.layout')

@php
    $title='Roles';
    $subTitle = 'Security / Roles';
    $script= "";
@endphp

@section('content')
<div class="col-span-12">
    <div class="card border-0">
        <div class="card-header">
            <h5 class="text-lg font-semibold mb-0">{{$title}}</h5>
        </div>
        <div class="card-body">
            <form class="grid grid-cols-12 gap-4" method="POST" action="{{ $action }}" >
                @csrf
                @method ($method)
                <input type="hidden" id="id" name="name" value="{{ $role->id ?? null }}">
                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Role</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ $role->name ?? null }}" required>
                </div>
                <div class="col-span-12">
                    <button class="btn btn-primary-600" type="submit">Submit form</button>
                    <a href="{{ $actionCancel }}" class="btn btn-secondary btn-sm">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
