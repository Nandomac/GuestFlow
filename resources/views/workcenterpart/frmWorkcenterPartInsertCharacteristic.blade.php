@extends('layouts.layout')

@php
$title = 'PartNo Workcenter Association';
$subTitle = 'Backoffice / PartNo X Workcenter / Form';
$script = "<script>
        function frmSave(){
            Swal.loading();

            let form = $('#workcenterPartCharacteristicForm')[0];
            let formData = new FormData(form);

            $.ajax({
                type: 'POST',
                url: '". $action ."',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')
                },
                success: function(response) {
                    Swal.close();

                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){

                        Swal.alert_dialog_confirmation(
                                'Want to add more characteristics to Group?',
                                'Waiting decision',
                                null,
                                function () {
                                    window.location.href = '". route('workcenter-part-characteristic.create', null) ."/". $workcenterPart->id ."/' + response.group + '/' + response.group_description;
                                },
                                function () {
                                    window.location.href = response.redirect;
                                },
                                'Yes',
                                'No'
                            );

                    });




                },
                error: function(response) {
                    $('.divErro').html('');
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
    ";
    if($group != null) {
        $script .= "
            $('#gridSubTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '" . route('workcenter-part.group-details', ['workcenter_part_id' => $workcenterPart->id, 'characteristic_group_id' => $group]) . "',
                    type: 'GET'
                },
                bJQueryUI: true,
                searching: false,
                paging: false,
                info: false,
                autoWidth: false,
                scrollX: true,
                columns: [
                    { data: 'order', className: 'text-start' },
                    { data: 'characteristic_name', className: 'text-start' },
                    { data: 'characteristic_unit', className: 'text-start' },
                    {
                        data: 'nominal_value',
                        className: 'text-start',
                        render: function(data, type, row) {
                            return `<input type='text' class='edit-input readonly-input' readonly autocomplete='off' data-id='\${row.id}' name='nominal_value' value='\${data ?? ''}'/>`;
                        }
                    },
                    {
                        data: 'tolerance_value',
                        className: 'text-start',
                        render: function(data, type, row) {
                            return `<input type='text' class='edit-input readonly-input' readonly autocomplete='off' data-id='\${row.id}' name='tolerance_value' value='\${data ?? ''}'/>`;
                        }
                    },
                    {
                            data: null,
                            className: 'text-right',
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `
                                    <div class='flex items-center justify-center gap-4' data-group-id='\${row.characteristic_group_id}'>
                                        <button onclick='enableEdit(\${row.id}, \${row.characteristic_group_id})'
                                            class='btn-edit rounded-lg bg-blue-600 px-4 py-1 text-white hover:bg-blue-700'>
                                            Edit
                                        </button>
                                        <button onclick='deleteRow(\${row.id}, \${row.characteristic_group_id})'
                                            class='rounded-lg bg-red-600 px-4 py-1 text-white hover:bg-red-700'>
                                            Delete
                                        </button>
                                        <button onclick='submitRow(\${row.id}, \${row.characteristic_group_id})'
                                            class='btn-save hidden rounded-lg bg-success-600 px-4 py-1 text-white hover:bg-success-700'>
                                            Save
                                        </button>
                                        <button onclick='cancelEdit(\${row.id}, \${row.characteristic_group_id})'
                                            class='btn-cancel hidden rounded-lg bg-gray-800 px-4 py-1 text-white hover:bg-gray-900'>
                                            Cancel
                                        </button>
                                    </div>
                                `;
                            }
                        }
                ],
                columnDefs: [
                    { targets: 0, width: '60px' },   // Order
                    { targets: 1, width: '200px' },  // Characteristic
                    { targets: 2, width: '60px' },   // Cols
                    { targets: 3, width: '80px' },  // Value1
                    { targets: 4, width: '60px' },  // Value2
                    { targets: 5, width: '100px' }   // Actions
                ]
            });

            function enableEdit(id, characteristic_group_id) {
                \$(`[data-id=\"\${id}\"].edit-input`).removeClass('hidden');
                \$(`button[onclick=\"enableEdit(\${id}, \${characteristic_group_id})\"]`).hide();
                \$(`button[onclick=\"deleteRow(\${id}, \${characteristic_group_id})\"]`).hide();
                \$(`button[onclick=\"submitRow(\${id}, \${characteristic_group_id})\"]`).removeClass('hidden');
                \$(`button[onclick=\"cancelEdit(\${id}, \${characteristic_group_id})\"]`).removeClass('hidden');

                \$(`[data-id=\"\${id}\"].edit-input`).removeClass('readonly-input');
                \$(`[data-id=\"\${id}\"].edit-input`).removeAttr('readonly');
            }

            function cancelEdit(id, characteristic_group_id) {
                $(`button[onclick=\"enableEdit(\${id}, \${characteristic_group_id})\"]`).show();
                $(`button[onclick=\"deleteRow(\${id}, \${characteristic_group_id})\"]`).show();
                $(`button[onclick=\"submitRow(\${id}, \${characteristic_group_id})\"]`).addClass('hidden');
                $(`button[onclick=\"cancelEdit(\${id}, \${characteristic_group_id})\"]`).addClass('hidden');

                $(`[data-id=\"\${id}\"].edit-input`).addClass('readonly-input').attr('readonly', true);

                const groupId = characteristic_group_id;
                if (groupId && $.fn.DataTable.isDataTable(`#gridSubTable`)) {
                    $(`#gridSubTable`).DataTable().ajax.reload(null, false); // false = mantém a página atual
                }
            }

            function submitRow(id, characteristic_group_id) {
                Swal.loading();
                const nominal = \$(`input[name=\"nominal_value\"][data-id=\"\${id}\"]`).val();
                const tolerance = \$(`input[name=\"tolerance_value\"][data-id=\"\${id}\"]`).val();

                \$.ajax({
                    url: `".route('workcenter-part-characteristic.update')."/\${id}`,
                    type: 'PUT',
                    data: {
                            nominal_value: nominal,
                            tolerance_value: tolerance,
                            _token: '". csrf_token() ."'
                    },
                    success: function(response) {
                        $(`button[onclick=\"enableEdit(\${id}, \${characteristic_group_id})\"]`).show();
                        $(`button[onclick=\"deleteRow(\${id}, \${characteristic_group_id})\"]`).show();
                        $(`button[onclick=\"submitRow(\${id}, \${characteristic_group_id})\"]`).addClass('hidden');
                        $(`button[onclick=\"cancelEdit(\${id}, \${characteristic_group_id})\"]`).addClass('hidden');

                        $(`[data-id=\"\${id}\"].edit-input`).addClass('readonly-input').attr('readonly', true);

                        const groupId = characteristic_group_id;
                        if (groupId && $.fn.DataTable.isDataTable(`#gridSubTable_\${groupId}`)) {
                            $(`#gridSubTable_\${groupId}`).DataTable().ajax.reload(null, false); // false = mantém a página atual
                        }
                        Swal.close();
                        Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ });
                    },
                    error: function(response) {
                        Swal.close();
                        Swal.alert('Error', response.message, 'error');
                    }
                });
            }

            function deleteRow(id, characteristic_group_id) {
                Swal.close();
                Swal.alert_dialog_confirmation(
                                    'Confirm Delete?',
                                    'Waiting decision',
                                    null,
                                    function() {
                                        $.ajax({
                                            url: `".route('workcenter-part-characteristic.destroy')."/\${id}`,
                                    type: 'DELETE',
                                    data: {
                                        _token: '". csrf_token() ."'
                                    },
                                    success: function(response) {
                                        $(`button[onclick=\"enableEdit(\${id}, \${characteristic_group_id})\"]`).show();
                                        $(`button[onclick=\"deleteRow(\${id}, \${characteristic_group_id})\"]`).show();
                                        $(`button[onclick=\"submitRow(\${id}, \${characteristic_group_id})\"]`).addClass('hidden');
                                        $(`button[onclick=\"cancelEdit(\${id}, \${characteristic_group_id})\"]`).addClass('hidden');

                                        $(`[data-id=\"\${id}\"].edit-input`).addClass('readonly-input').attr('readonly', true);

                                        const groupId = characteristic_group_id;
                                        if (groupId && $.fn.DataTable.isDataTable(`#gridSubTable`)) {
                                            $(`#gridSubTable`).DataTable().ajax.reload(null, false); // false = mantém a página atual
                                        }
                                        Swal.close();
                                        Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ });
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

            function deleteGroupRow(id, characteristic_group_id) {
                Swal.close();
                Swal.alert_dialog_confirmation(
                                    'Confirm Delete?',
                                    'Waiting decision',
                                    null,
                                    function() {
                                        $.ajax({
                                            url: `".route('workcenter-part-characteristic.destroyGroup', null)."/\${id}/\${characteristic_group_id}`,
                                    type: 'DELETE',
                                    data: {
                                        _token: '". csrf_token() ."'
                                    },
                                    success: function(response) {
                                        Swal.close();
                                        if (response.isEmpty) {

                                            Swal.alert_dialog_confirmation(
                                                'There are no more associated groups. Do you want to undo the association?',
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
                                                                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ window.location.replace('{{ $actionCancel }}'); });
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
                                        } else {
                                            Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ window.location.reload(); });
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


        ";
    }

    $script .= "</script>";
@endphp

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-12 2xl:col-span-12">
        <div class="card h-full p-0 border-0">
            <div class="card-header">
                <h5 class="text-lg font-semibold mb-0">{{ $titleCard }}</h5>
            </div>
            <div class="card-body" id="divform">
                <form id="workcenterPartCharacteristicForm" method="POST" action="{{ $action }}" enctype="multipart/form-data">
                    @csrf
                    @method($method)
                    <input type="hidden" id="workcenter_part_id" name="workcenter_part_id" value="{{ $workcenterPart->id }}">
                    <input type="hidden" id="workcenter_id" name="workcenter_id" value="{{ $workcenterPart->workcenter_structure_id }}">
                    <input type="hidden" id="partno_id" name="partno_id" value="{{ $workcenterPart->partno_id }}">
                    <input type="hidden" id="characteristic_group_order" name="characteristic_group_order" value="{{ $group_order ?? $maxGroupOrder ?? '' }}">

                    <div class="col-span-12 mt-3">
                        <div class="md:col-span-12 col-span-12">
                            <label class="form-label">Workcenter</label>
                            <h5 class="text-lg font-semibold mb-0">{{ $workcenterPath }} << {{ $contract }}</h5>
                        </div>
                        <div class="md:col-span-12 col-span-12">
                            <label class="form-label">Inventory Part</label>
                            <h5 class="text-lg font-semibold mb-0">{{ $workcenterPart->partno_id }} - {{ $workcenterPart->partno_description }}</h5>
                        </div>
                    </div>

                    <div class="col-span-12 mt-3">
                        <x-customs.search
                            :api-url="route('characteristic-group.searchCharacteristics', ['searchGroup' => ''])"
                            searchName="Group"
                            :initial="''"
                            :selected-label="$group_description ?? ''"
                            :selected-id="$group ?? ''"
                            :show-code="false"
                            :label="'Search Group'"
                            :isSearchable="!$group"
                            :onSelectCallbackName=null
                        />
                    </div>

                    <div class="col-span-12 mt-3">
                        <x-customs.search
                            :api-url="route('workcenter-part-characteristic.searchAvailableSetupCharacteristics', null) . '/' .$workcenterPart->id"
                            searchName="Characteristic"
                            :initial="''"
                            :selected-label="$characteristic->name ?? ''"
                            :selected-id="$characteristic_id ?? ''"
                            :show-code="false"
                            :label="'Search Characteristics'"
                            :isSearchable="!$characteristic"
                            :onSelectCallbackName=null
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 mt-2">

                        <div class="w-full">
                            <label for="cols" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cols</label>
                            <input type="text" id="cols" name="cols"
                                value="{{ $characteristic_cols ?? '2' }}"
                                class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Number of cols">
                            <div class="text-red-500 text-sm mt-1 divErro" id="cols-error"></div>
                        </div>

                        <div class="w-full">
                            <label for="order" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Order</label>
                            <input type="text" id="order" name="order"
                                value="{{ $characteristic_order ?? $maxGroupCharacteristicOrder  ?? '' }}"
                                class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Order Number">
                            <div class="text-red-500 text-sm mt-1 divErro" id="order-error"></div>
                        </div>

                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 mt-2">

                        <div class="w-full">
                            <label for="nominal_value" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Nominal Value</label>
                            <input type="text" id="nominal_value" name="nominal_value"
                                value=""
                                class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Nominal Value">
                            <div class="text-red-500 text-sm mt-1 divErro" id="nominal_value-error"></div>
                        </div>

                        <div class="w-full">
                            <label for="tolerance_value" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Tolerance</label>
                            <input type="text" id="tolerance_value" name="tolerance_value"
                                value=""
                                class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                                placeholder="Tolerance">
                            <div class="text-red-500 text-sm mt-1 divErro" id="tolerance_value-error"></div>
                        </div>

                    </div>

                    <div class="col-span-12 mt-3">
                        <div class="form-group text-end">
                            <button type="button" class="rounded-lg px-4 py-2 text-white bg-success-600 hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="frmSave()">
                                <iconify-icon icon="icon-park-outline:check" class="text-white text-lg mr-2"></iconify-icon>
                                Save
                            </button>
                            <a href="{{ $actionCancel }}" class="rounded-lg px-4 py-2 bg-neutral-300 text-neutral-700 hover:bg-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <iconify-icon icon="icon-park-outline:close" class="text-neutral-700 text-lg mr-2"></iconify-icon>
                                Cancel
                            </a>
                        </div>
                    </div>

                </form>

                @if ($group != null)
                    <div class="mt-4">
                        <h6 class="text-md font-semibold mb-2">Group {{$group_description ?? ''}} Characteristics</h6>
                        <table id="gridSubTable" name="gridSubTable" class="table striped-table mb-0">
                            <thead>
                                <tr>
                                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Order</th>
                                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Characteristic</th>
                                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Uom</th>
                                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Nominal</th>
                                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Tolerance</th>
                                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                @endif

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
