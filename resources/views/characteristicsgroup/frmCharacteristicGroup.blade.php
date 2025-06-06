@extends('layouts.layout')

@php
    $title = 'Characteristics Group';
    $subTitle = 'Attributes / Characteristics Group';
    $script = "";
@endphp

@section('content')

<div class="col-span-12">
    <div class="card border-0">
        <div class="card-header">
            <h5 class="text-lg font-semibold mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body">
            <form class="grid grid-cols-12 gap-4" method="POST" action="{{ $action }}" id="characteristicGroupForm">
                @csrf
                @method($method)
                <input type="hidden" id="id" name="id" value="{{ $characteristicGroup->id ?? null }}">

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Group name</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') border-red-500 @enderror" value="{{ old('name', $characteristicGroup->name ?? '') }}" >
                    @error('name')
                    <div class="text-red-500 text-sm mt-1" id="code-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Order</label>
                    <input type="text" name="group_order" id="group_order" class="form-control @error('name') border-red-500 @enderror" value="{{ old('group_order', $characteristicGroup->group_order ?? '') }}" >
                    @error('group_order')
                    <div class="text-red-500 text-sm mt-1" id="code-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-span-12 flex justify-start items-center gap-2">
                    <button class="btn btn-success-700 flex justify-center items-center px-20 py-2.5 text-white bg-success-600 hover:bg-success-700 font-semibold rounded-lg transition duration-200 gap-1.5" type="submit">
                        <i class="ri-save-fill text-white-400 text-xl"></i>
                        Save
                    </button>
                    <a href="{{ $actionCancel }}" class="btn bg-neutral-300 text-neutral-700 hover:bg-neutral-400 btn-sm px-20 py-3 rounded-lg flex items-center justify-center gap-1.5">
                        <iconify-icon icon="icon-park-outline:close" class="text-neutral-700 text-lg mr-2"></iconify-icon>
                        Cancel
                    </a>
                </div>
        </div>
    </div>
</div>
@endsection
