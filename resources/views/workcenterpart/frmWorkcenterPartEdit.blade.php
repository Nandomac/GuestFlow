@extends('layouts.layout')

@php
$title = 'PartNo Workcenter Association';
$subTitle = 'Backoffice / PartNo X Workcenter / Form';
$script = "
<script>
    var iTableCounter = 1;
    var oTable;
    var TableHtml;
    var oInnerTable;
    var objGridTable;
    var objInnerTable;
    var gridTableHtml;

    function fnFormatDetails(characteristic_group_id) {
        var sOut = '';
        sOut += '<table id=\"gridSubTable_' + characteristic_group_id + '\" class=\"table display mb-0\" style=\"width:100%\" cellspacing=\"0\">';
        sOut += '<thead>';
        sOut += '<tr>';
        sOut += '<th>Order</th>';
        sOut += '<th>Characteristic</th>';
        sOut += '<th>Uom</th>';
        sOut += '<th>Nominal</th>';
        sOut += '<th>Tolerance</th>';
        sOut += '<th>Actions</th>';
        sOut += '</tr>';
        sOut += '</thead>';
        sOut += '</table>';
        return sOut;
    }

    $(document).ready(function () {
        TableHtml = $('#exampleTable').html();
        gridTableHtml = $('#gridTable').html();

        var nTh = document.createElement('th');
        var nTd = document.createElement('td');
        nTd.innerHTML = '<img src=\"". asset('assets/images/gridOpen.png') ."\" alt=\"Expand/Collapse\">';
        nTd.className = 'center';

        $('#gridTable thead tr').each(function () {
            this.insertBefore(nTh, this.childNodes[0]);
        });

        $('#gridTable tbody tr').each(function () {
            this.insertBefore(nTd.cloneNode(true), this.childNodes[0]);
        });

        objGridTable = $('#gridTable').DataTable({
            processing: true,
            serverSide: false,
            bJQueryUI: true,
            searching: true,
            paging: false,
            scrollX: true,
            rowReorder: {
                    selector: 'td.reorder',
                    snapX: true
                },
            columnDefs: [
                {
                    targets: 0,
                    searchable: false,
                    orderable: false,
                    width: '15px'
                },
                {
                    targets: 1,
                    visible: false,
                    searchable: false,
                    orderable: false,

                },
                {
                    targets: 2,
                    orderable: false,
                    className: 'reorder',
                },
                {
                    targets: -1,
                    width: '450px',
                    orderable: false,
                }
            ]
        });

        $('#gridTable tbody').on('click', 'td img', function () {
            var nTr = $(this).closest('tr')[0];
            var characteristic_group_id = objGridTable.row(nTr).data()[1];

            objGridTable.rows().every(function () {
                if (this.node() !== nTr && this.child.isShown()) {
                    $('img', this.node()).attr('src', '". asset('assets/images/gridOpen.png') ."'); // reseta o ícone
                    this.child.hide(); // fecha a linha
                }
            });

            if (objGridTable.row(nTr).child.isShown()) {
                this.src = '". asset('assets/images/gridOpen.png') ."';
                objGridTable.row(nTr).child.hide();
            } else {
                this.src = '". asset('assets/images/gridClose.png') ."';
                objGridTable.row(nTr).child(fnFormatDetails(characteristic_group_id)).show();
                objGridSubTable = $('#gridSubTable_' + characteristic_group_id).DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '" . route('workcenter-part.group-details', ['workcenter_part_id' => $workcenterPart->id, 'characteristic_group_id' => '']) . "/' + characteristic_group_id,
                        type: 'GET'
                    },
                    bJQueryUI: true,
                    searching: false,
                    paging: false,
                    info: false,
                    autoWidth: false,
                    scrollX: true,
                    rowReorder: {
                        selector: 'td.reorder',
                        snapX: true
                    },
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
                        { targets: 0, width: '60px', className: 'reorder'},   // Order
                        { targets: 1, width: '200px' },  // Characteristic
                        { targets: 2, width: '60px' },   // Cols
                        { targets: 3, width: '80px' },  // Value1
                        { targets: 4, width: '60px' },  // Value2
                        { targets: 5, width: '100px' }   // Actions
                    ],
                    createdRow: function(row, data, dataIndex) {
                        $(row).attr('data-id', data.id);
                        $(row).attr('data-characteristic_id', data.characteristic_id);
                        $(row).attr('data-characteristic_group_id', data.characteristic_group_id);
                    }
                });

                objGridSubTable.on('row-reorder', function (e, diff, edit) {
                    if (diff.length === 0) {
                        return;
                    }

                    let novasOrdems = [];

                    for (let i = 0; i < diff.length; i++) {
                        let row = diff[i].node;
                        let id = $(row).data('id');
                        let characteristic_id = $(row).data('characteristic_id');
                        let characteristic_group_id = $(row).data('characteristic_group_id');
                        let novaOrdem = (diff[i].newPosition)+1;
                        novasOrdems.push({
                            id: id,
                            characteristic_id: characteristic_id,
                            characteristic_group_id: characteristic_group_id,
                            ordemDestino: novaOrdem
                        });
                    }

                    $.ajax({
                        url: '/workcenter-part/update-characteristic-order',
                        method: 'POST',
                        data: {
                            _token: '". csrf_token() ."',
                            workcenter_structure_id: '$workcenterPart->workcenter_structure_id',
                            partno_id: '$workcenterPart->partno_id',
                            novasOrdems: novasOrdems
                        },
                        success: function(response) {
                            Swal.close();
                                if (response.success) {
                                    Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ objGridSubTable.ajax.reload(null, false); });
                                } else {
                                    Swal.alert_auto_close(response.message, 'Please Wait...', 'error', function(){ objGridSubTable.ajax.reload(null, false); });
                                }
                        },
                        error: function(response) {
                            Swal.close();
                                Swal.alert('Error', response.message, 'error');
                        }
                    });
                });

            }
        });

        objGridTable.on('row-reorder', function (e, diff, edit) {
            if (diff.length === 0) {
                return;
            }

            let novasOrdems = [];

            for (let i = 0; i < diff.length; i++) {
                let row = diff[i].node;
                let id = $(row).data('id');
                let novaOrdem = (diff[i].newPosition)+1;
                novasOrdems.push({
                    id: id,
                    ordemDestino: novaOrdem
                });
            }

            $.ajax({
                url: '/workcenter-part/update-group-order',
                method: 'POST',
                data: {
                    _token: '". csrf_token() ."',
                    workcenter_structure_id: '$workcenterPart->workcenter_structure_id',
                    partno_id: '$workcenterPart->partno_id',
                    novasOrdems: novasOrdems
                },
                success: function(response) {
                    Swal.close();
                        if (response.success) {
                            Swal.alert_auto_close(response.message, 'Please Wait...', 'success', function(){ window.location.reload(); });
                        } else {
                            Swal.alert_auto_close(response.message, 'Please Wait...', 'error', function(){ window.location.reload(); });
                        }
                },
                error: function(response) {
                    Swal.close();
                        Swal.alert('Error', response.message, 'error');
                }
            });
        });
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
        if (groupId && $.fn.DataTable.isDataTable(`#gridSubTable_\${groupId}`)) {
            $(`#gridSubTable_\${groupId}`).DataTable().ajax.reload(null, false); // false = mantém a página atual
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
</script>
";
@endphp


@section('content')


<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-12 2xl:col-span-12">
        <div class="card h-full p-0 border-0">
            <div class="card-header">
                <h5 class="text-lg font-semibold mb-0">{{ $titleCard }}</h5>
            </div>
            <div class="card-body" id="divform">
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


                <div class="table-responsive mt-3">
                    <label class="form-label">Characteristic List</label>

                    <div class="col-span-12 flex justify-end mb-4">
                        <a href="{{ route('workcenter-part-characteristic.create', ['workcenter_part_id' => $workcenterPart->id, 'partno_id' => $workcenterPart->partno_id]) }}"
                           class="btn bg-blue-600 text-white hover:bg-blue-700 px-6 py-2 rounded-lg flex items-center gap-2">
                           <iconify-icon icon="mdi:plus" class="text-white text-lg mr-1"></iconify-icon>
                           Add Group / Characteristic
                        </a>
                    </div>
                    <table id="gridTable" class="table display mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">GroupID</th>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Group</th>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($workcenterPartCharacteristicsGroup as $itemCharacteristicGrup)
                                <tr data-id="{{ $itemCharacteristicGrup->characteristic_group_id }}" class="bg-neutral-50 dark:bg-neutral-700 font-medium">
                                    <td>{{ $itemCharacteristicGrup->characteristic_group_id ?? null }}</td>
                                    <td>{{ $itemCharacteristicGrup->characteristicGroup->name ?? null }}</td>
                                    <td>
                                        <div class='flex items-center justify-center gap-2'>
                                            <a href="{{ route('workcenter-part-characteristic.create', ['workcenter_part_id' => $workcenterPart->id, 'partno_id' => $workcenterPart->partno_id, 'characteristic_group_id' => $itemCharacteristicGrup->characteristic_group_id, 'characteristic_group' => $itemCharacteristicGrup->characteristicGroup->name ?? null]) }}"
                                                class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-1 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:bg-blue-500">
                                                <iconify-icon icon="mdi:plus" class="text-white text-lg mr-1"></iconify-icon>
                                                Add Characteristic
                                             </a>
                                            <button onclick='deleteGroupRow({{ $itemCharacteristicGrup->workcenter_part_id }}, {{ $itemCharacteristicGrup->characteristic_group_id }})'
                                                class='flex items-center gap-2 rounded-lg bg-red-600 px-4 py-1 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500'>
                                                <iconify-icon icon='heroicons:mini-trash' class='text-white'></iconify-icon>
                                                Remove Group
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="col-span-12 flex justify-end items-center gap-2">
                    <a href="{{ $actionCancel }}" class="btn bg-neutral-300 text-neutral-700 hover:bg-neutral-400 btn-sm px-20 py-3 rounded-lg flex items-center justify-center gap-1.5">
                        <iconify-icon icon="icon-park-outline:close" class="text-neutral-700 text-lg mr-2"></iconify-icon>
                        Cancel
                    </a>
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
