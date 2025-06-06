@extends('layouts.layout')

@php
    $title = 'PartNo Workcenter Association';
    $subTitle = 'Backoffice / PartNo X Workcenter / Form';
    $script = "<script>
        function sync() {

            Swal.loading('Please wait while synchronizing');

            $.ajax({
                type: 'GET',
                url: '". route('workcenter.sync') ."',
                success: function(response) {
                    Swal.close();
                    const message = response.responseJSON?.message || ''

                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ syncDowntime() });

                },
                error: function(response) {
                    const message = response.responseJSON?.message || 'Unknown error.'
                    Swal.close();
                    Swal.alert('Error', message, 'error');
                }
            });
        }

        function syncDowntime() {

            Swal.loading('Please wait updating Global Downtimes');

            $.ajax({
                type: 'GET',
                url: '". route('workcenter.syncDowntimes') ."',
                success: function(response) {
                    Swal.close();
                    const message = response.responseJSON?.message || ''

                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ location.reload(); });

                },
                error: function(response) {
                    const message = response.responseJSON?.message || 'Unknown error.'
                    Swal.close();
                    Swal.alert('Error', message, 'error');
                }
            });
        }

        function workcenter_details(id) {
            Swal.loading();
            $.ajax({
                type: 'GET',
                url: '". route('workcenter-part.create-form', null) ."/' + id,
                success: function(result) {
                    Swal.close();
                    $('#divform').empty();
                    $('#divform').html(result);
                },
                error: function(response) {
                    const message = response.responseJSON?.message || 'Unknown error.'
                    Swal.close();
                    Swal.alert('Error', message, 'error');
                }
            });
        }

        function frmSave(){
            Swal.loading();

            let form = $('#WorkcenterPartForm')[0];
            let formData = new FormData(form);

            $.ajax({
                type: 'POST',
                url: '". route('workcenter-part.store') ."',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                },
                success: function(response) {
                    Swal.close();
                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ window.location.href = response.redirect; });
                },
                error: function(response) {
                    Swal.close();

                    const message = response.responseJSON?.message || 'Unknown error.';
                    const errors = response.responseJSON?.errors || '';
                    let errorMessages = '';

                    if (errors != '') {

                        for (let field in errors) {
                            $('#'+field+'-error').html(errors[field]);
                            errorMessages += errors[field].join('<br>') + '<br>';
                        }
                    } else {
                        errorMessages = message;
                        Swal.alert('Error', errorMessages, 'error');
                    }

                }
            });
        }
    </script>";

    $css = "<style>
            table.dataTable {
                table-layout: fixed;
                width: 100% !important;
            }

            .edit-input {
                width: 30%;
                max-width: 30%;
                box-sizing: border-box;
                padding: 0.25rem;
            }
        </style>";

@endphp

@section('content')


<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-4 2xl:col-span-4">
        <div class="card h-full p-0 border-0 overflow-auto">
            <div class="card-body p-6" id="paneltree">
                <x-customs.treeview :tree="$tree" />
            </div>
        </div>
    </div>

    <div class="col-span-12 lg:col-span-8 2xl:col-span-8">
        <div class="card h-full p-0 border-0">
            <div class="card-header">
                <h5 class="text-lg font-semibold mb-0">{{ $titleCard }}</h5>
            </div>
            <div class="card-body" id="divform">
                <div class="col-span-12 mt-3">
                    <div class="form-group text-end">
                        <a href="{{ $actionCancel }}" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            Back To Inventory Part Workcenter Association List
                        </a>
                    </div>
                </div>
            </div>
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

@endsection
