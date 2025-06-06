@extends('layouts.layout')


@php
    $specification_id = request()->get('specification_id'); 
@endphp

@php
    $title = 'BackPrint';
    $subTitle = 'User Area / BackPrint';
    $script = "
        <script>
            $(document).ready(function() {
                var specification_id = '". request()->get('specification_id') ."';
                var table = $('#gridTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url:'/backprint/list',
                             data: function(d) {
                            if (!d.search.value) {
                                d.initialLoad = true;
                            }
                            return d;
                        }
                    },
                    columns: [
                        { data: 'part_number', className: 'text-start', width: '100px' },
                        { data: 'specification_name', className: 'text-start', width: '30px' },
                        {
                            data: 'cut_edges',
                            className: 'text-center',
                            render: function (data) {
                                return data == 1 
                                    ? '<span class=\"text-green-600 font-semibold text-center\">Yes</span>' 
                                    : '<span class=\"text-red-600 font-semibold text-center\">No</span>';
                            }
                        },
                        {
                            data: 'has_backprint',
                            className: 'text-center',
                            render: function (data) {
                                return data == 1 
                                    ? '<span class=\"text-green-600 font-semibold text-center\">Yes</span>' 
                                    : '<span class=\"text-red-600 font-semibold text-center\">No</span>';
                            }
                        },
                        { data: 'bckp_text_to_print', className: 'text-start', width: '200px' },
                        { data: 'bckp_num_vertical_lines', className: 'text-center', width: '30px' },
                        { data: 'bckp_spacing_length', className: 'text-center', width: '30px' },
                        { data: 'bckp_spacing_cross', className: 'text-center', width: '30px' },
                        {
                            data: 'bckp_date',
                            className: 'text-center',
                            render: function (data) {
                                return data == 1 
                                    ? '<span class=\"text-green-600 font-semibold text-center\">Yes</span>' 
                                    : '<span class=\"text-red-600 font-semibold text-center\">No</span>';
                            }
                        },
                        { 
                            data: 'bckp_dpi', 
                            className: 'text-center', 
                            width: '30px',
                            render: function(data, type, row) {
                                if (type === 'display') {
                                    var displayValue = (data !== null && data !== undefined) ? data : '';
                                    
                                    return '<input type=\"number\" class=\"form-control dpi-input\" ' +
                                        'data-id=\"' + row.specification_id + '\" ' +
                                        'data-original=\"' + displayValue + '\" ' +
                                        'style=\"width: 70px; text-align: center;\" ' +
                                        'value=\"' + displayValue + '\">';
                                }
                                return data;
                            }
                        }
                    ],
                    columnDefs: [
                        { targets: '_all', width: '100px', className: 'text-center' }
                    ],
                    language: {
                        emptyTable: 'Enter a search term to display results'
                    },
                    initComplete: function(settings, json) {
                        console.log('DataTables data:', json.data);
                    },
                    drawCallback: function() {
                        $('.dpi-input').each(function() {
                            console.log('DPI input value:', $(this).val());
                            console.log('DPI data-original:', $(this).data('original'));
                        });
                        
                        $('.dpi-input').on('blur', function() {
                            var input = $(this);
                            var newValue = input.val();
                            var row = table.row(input.closest('tr')).data();
                            var partNumber = row.part_number;
                            var specification_id = input.data('id');

                            $.ajax({
                                url: '". route('backprint.updateDpi') ."',
                                type: 'POST',
                                data: {
                                    specification_id: specification_id,
                                    bckp_dpi: newValue,
                                    _token: '". csrf_token() ."'
                                },
                                success: function(response) {
                                    if(response.success) {
                                        row.bckp_dpi = newValue;
                                        input.data('original', newValue);
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success',
                                            text: 'DPI updated successfully',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    } else {
                                        input.val(input.data('original'));
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: response.message || 'Failed to update DPI',
                                            toast: true,
                                            position: 'top-end',
                                            showConfirmButton: false,
                                            timer: 3000
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    input.val(input.data('original'));
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Server error occurred',
                                        toast: true,
                                        position: 'top-end',
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            });
                        });
                        
                        $('.dpi-input').on('keypress', function(e) {
                            if(e.which === 13) {
                                $(this).blur();
                                return false; // Prevent form submission
                            }
                        });
                    }
                });
            });
        </script>
    ";
@endphp

@section('content')
<div class="col-span-12 lg:col-span-6 mt-3">
    <div class="card border-0 overflow-hidden">
    <div class="card-header">
            <h5 class="card-title text-lg mb-0">BackPrint Specifications List</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="gridTable" class="table striped-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Part Number</th>
                            <th scope="col">Spec Description</th>
                            <th scope="col">Cut Edges</th>
                            <th scope="col">Has Backprint</th>
                            <th scope="col">Text to Print</th>
                            <th scope="col">Number of Vertical Lines</th>
                            <th scope="col">Spacing Length</th>
                            <th scope="col">Spacing Cross</th>
                            <th scope="col">Print Date</th>
                            <th scope="col">Print DPI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be loaded here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
