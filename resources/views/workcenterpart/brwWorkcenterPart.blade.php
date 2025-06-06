@extends('layouts.layout')

@php
    $title = 'PartNo Workcenter Association';
    $subTitle = 'Backoffice / PartNo X Workcenter / List';
    $script = "<script>
            $(document).ready(function() {
                $('#gridTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '". route('workcenter-part.list') ."',
                    columns: [
                        { data: 'partno_id', className: 'text-start',
                            render: function(data, type, row) {
                                return row.partno_id + ' - ' + row.partno_description;
                            }
                        },
                        { data: 'structure_path', className: 'text-start' },
                        {
                            data: null,
                            className: 'text-right',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class='flex items-center justify-center gap-4'>
                                        <a href='". route('workcenter-part.pdf', null)   ."/\${row.id}'
                                            class='rounded-lg bg-blue-600 px-4 py-1 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500'>
                                            <iconify-icon icon=\"heroicons:document\" class=\"text-white\"></iconify-icon>
                                            PDF
                                        </a>
                                        <a href='". route('workcenter-part.edit', null)   ."/\${row.id}'
                                            class='rounded-lg bg-blue-600 px-4 py-1 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500'>
                                            Edit
                                        </a>
                                        <button onclick='deleteWorkcenterPart(\${row.id})'
                                            class='flex items-center gap-2 rounded-lg bg-red-600 px-4 py-1 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500'>
                                            Delete
                                        </button>
                                    </div>
                                `;
                            }
                        }
                    ],
                    columnDefs: [
                        { targets: -1, width: '300px' }
                    ]
                });
            });

            function deleteWorkcenterPart(id) {

                Swal.alert_dialog_confirmation(
                    'Confirm Delete?',
                    'Waiting decision',
                    null,
                    function() {
                        $.ajax({
                            type: 'DELETE',
                            url: '/workcenter-part/destroy/'+id,
                            data: {
                                _token: '". csrf_token() ."'
                            },
                            success: function(response) {
                                Swal.close();

                                if (response.success) {
                                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ $('#gridTable').DataTable().ajax.reload(); });
                                } else {
                                    Swal.alert_auto_close(response.message, 'Please Wait...', 'error', function(){ });
                                }
                            },
                            error: function(response) {
                                Swal.close();
                                Swal.alert('Error', response.message, 'error');
                            }
                        });
                    },
                    function() {

                    },
                    'Yes',
                    'No'
                );
            }
        </script>

    ";
@endphp

@section('content')

<!-- Show the alert of sucess when the add/edit its done  -->
@if(session('success'))
    <div id="success-alert" class="alert alert-success bg-success-100 dark:bg-success-600/25 text-success-600 dark:text-success-400 border-success-600 border-start-width-4-px border-l-[3px] dark:border-neutral-600 px-6 py-[13px] font-semibold text-lg rounded flex items-center justify-between mb-4" role="alert">
        <div class="flex items-center gap-2">
            <iconify-icon icon="akar-icons:double-check" class="icon text-xl"></iconify-icon>
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl line-height-1" onclick="document.getElementById('success-alert').remove()">
            <iconify-icon icon="iconamoon:sign-times-light" class="icon"></iconify-icon>
        </button>
    </div>

    <script>
        setTimeout(() => {
            const alert = document.getElementById('success-alert');
            if (alert) {
                alert.remove();
            }
        }, 3000);
    </script>
@endif

<a href="{{ route('workcenter-part.create') }}" class="btn btn-primary mb-4 gap-1.5">
    <iconify-icon icon="akar-icons:plus" class="text-xl mr-5"></iconify-icon>
    Create New Inventory Part Workcenter Association
</a>

<div class="col-span-12 lg:col-span-6 mt-3">
    <div class="card border-0 overflow-hidden">
        <div class="card-header">
            <h5 class="card-title text-lg mb-0">{{ $titleCard }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="gridTable" class="table striped-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">PartoNo - Description</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Workcenter</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
