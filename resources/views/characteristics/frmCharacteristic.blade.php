@extends('layouts.layout')

@php
    $title = 'Characteristics';
    $subTitle = 'Attributes / Characteristics';
    $script = "";
@endphp

@section('content')

<div class="col-span-12">
    <div class="card border-0">
        <div class="card-header">
            <h5 class="text-lg font-semibold mb-0">{{ $title }}</h5>
        </div>
        <div class="card-body">
            <form class="grid grid-cols-12 gap-4" method="POST" action="{{ $action }}" id="characteristicForm">
                @csrf
                @method($method)
                <input type="hidden" id="id" name="id" value="{{ $characteristic->id ?? null }}">

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Code</label>
                    <input type="text" name="code" id="code" class="form-control @error('code') border-red-500 @enderror" value="{{ old('code', $characteristic->code ?? null) }}" >
                    @error('code')
                    <div class="text-red-500 text-sm mt-1" id="code-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" id="description" class="form-control @error('description') border-red-500 @enderror" value="{{ old('description', $characteristic->description ?? null) }}" >
                    @error('description')
                    <div class="text-red-500 text-sm mt-1" id="description-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Type</label>
                    <select name="type" id="type" class="form-control @error('type') border-red-500 @enderror"  onchange="handleTypeChange()">
                        <option value="">Choose one</option>
                        <option value="validation" {{ old('type', $characteristic->type ?? '') == 'validation' ? 'selected' : '' }}>Validation</option>
                        <option value="setup" {{ old('type', $characteristic->type ?? '') == 'setup' ? 'selected' : '' }}>Setup</option>
                    </select>
                    @error('type')
                    <div class="text-red-500 text-sm mt-1" id="type-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Data Type</label>
                    <select name="datetype" id="datetype" class="form-control" onchange="handleDataTypeChange()">
                        <option value="">Choose one</option>
                        <option value="ok/nok" {{ isset($characteristic) && $characteristic->datetype == 'ok/nok' ? 'selected' : '' }}>OK/NOK</option>
                        <option value="text" {{ isset($characteristic) && $characteristic->datetype == 'text' ? 'selected' : '' }}>Text</option>
                    </select>
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">BDLab ID</label>
                    <input type="number" name="id_bdlab" id="id_bdlab" class="form-control" value="{{ $characteristic->id_bdlab ?? null }}">
                </div>

                <div class="md:col-span-6 col-span-12">
                    <label class="form-label">Unit of Measure (UOM)</label>
                    <input type="text" name="uom" id="uom" class="form-control" value="{{ $characteristic->uom ?? null }}">
                </div>

                <!-- Toggle switch in its own row -->
                <div class="col-span-12 flex items-center mb-1 border-b pb-4">
                    <input type="hidden" name="is_active" value="0"> 
                    <label class="inline-flex items-center cursor-pointer gap-2">
                        <input type="checkbox"
                            name="is_active"
                            class="sr-only peer"
                            value="1"
                            {{ old('is_active', $characteristic->is_active ?? 1) == 1 ? 'checked' : '' }}>
                        <span class="relative w-11 h-6 bg-gray-400 peer-focus:outline-none rounded-full peer dark:bg-gray-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-success-600"></span>
                        <span class="ml-3 text-gray-700 dark:text-gray-300">Active</span>
                    </label>
                </div>

                <!-- Buttons in their own row with border top -->
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
            </form>
        </div>
    </div>
</div>

 <!-- CSS to customize the inputs -->
<style>
    .readonly-select {
        pointer-events: none;
        background-color: #e9ecef;
        opacity: 0.7;
    }
    
    .readonly-input {
        background-color: #e9ecef;
        opacity: 0.7;
    }
</style>


<!-- JS to control the inative/ative fields -->
<script>
    document.addEventListener('DOMContentLoaded', function() {

        handleTypeChange();

        validateField(document.getElementById('code'));
        validateField(document.getElementById('description'));
        validateField(document.getElementById('type'));

        document.getElementById('characteristicForm').addEventListener('submit', function(e) {
            const typeSelect = document.getElementById('type');
            const dataTypeSelect = document.getElementById('datetype');

            if (typeSelect.value === 'validation' && !dataTypeSelect.value) {
                e.preventDefault();
                alert('Please select a Data Type for Validation characteristics');
                return false;
            }

            const allFields = document.querySelectorAll('input, select');
            allFields.forEach(field => {
                field.removeAttribute('readonly');
                field.classList.remove('readonly-input', 'readonly-select');
                field.removeAttribute('tabindex');
            });

            return true;
        });
    });

    function handleTypeChange() {
        const typeSelect = document.getElementById('type');
        const dataTypeField = document.getElementById('datetype');
        const bdLabIdField = document.getElementById('id_bdlab');

        clearAllNullFields();
        
        if (typeSelect.value === 'setup') {

            makeSelectReadonly(dataTypeField, true);
            makeInputReadonly(bdLabIdField, true);
 
            dataTypeField.value = '';
            bdLabIdField.value = '';

        } else {

            makeSelectReadonly(dataTypeField, false);
            makeInputReadonly(bdLabIdField, false);

            handleDataTypeChange();
        }
    }

    function handleDataTypeChange() {
        const dataTypeSelect = document.getElementById('datetype');
        const uomField = document.getElementById('uom');
        const typeSelect = document.getElementById('type');

        if (typeSelect.value === 'setup') {
            return;
        }
        
        if (dataTypeSelect.value === 'ok/nok') {
            makeInputReadonly(uomField, true);
            uomField.value = '';
        } else {

            makeInputReadonly(uomField, false);
        }
    }
    
    function makeSelectReadonly(selectElement, isReadonly) {
        if (isReadonly) {
            selectElement.classList.add('readonly-select');
            selectElement.setAttribute('tabindex', '-1'); 
        } else {
            selectElement.classList.remove('readonly-select');
            selectElement.removeAttribute('tabindex');
        }
    }
    
    function makeInputReadonly(inputElement, isReadonly) {
        if (isReadonly) {
            inputElement.setAttribute('readonly', 'readonly');
            inputElement.classList.add('readonly-input');
        } else {
            inputElement.removeAttribute('readonly');
            inputElement.classList.remove('readonly-input');
        }
    }
    
    function clearAllNullFields() {
        const hiddenFields = document.querySelectorAll('[id$="_hidden"]');
        hiddenFields.forEach(field => field.remove());
    }

    function validateField(input) {
        const errorElement = document.getElementById(input.id + '-error');
        
        if (!input.value.trim()) {
            input.classList.add('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            errorElement.style.display = 'block';
        } else {
            input.classList.remove('border-red-500', 'focus:ring-red-500', 'focus:border-red-500');
            errorElement.style.display = 'none';
        }
    }
</script>
@endsection
