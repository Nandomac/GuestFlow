@extends('layouts.layoutsimple')

@php
// $title = 'Workcenter';
// $subTitle = 'Backoffice / Workcenters List';
$script = "<script>

    var workcenterCode = null;
    var workcenter_structure_id = null;

    function workcenter_details(id)
    {
        Swal.loading();
        $.ajax({
            type: 'GET',
            url: '/production-area/details/' + id,
            success: function(result) {
                Swal.close();

                $('#headerWorcenterDetail').empty();
                $('#headerWorcenterDetail').html(result[0]);

                workcenterCode = result[2];
                workcenter_structure_id = result[3];
                getWorkcenterShopOrdersList(workcenterCode, result[1]);
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function getWorkcenterShopOrdersList(workcenterCode, contract)
    {
        Swal.loading();
        $.ajax({
            type: 'GET',
            url: '/production-area/getWorkcenterShopOrdersList/' + workcenterCode +'/' + contract,
            success: function(response) {
                Swal.close();

                $('#listShopOrders').empty();
                $('#listShopOrders').html(response);

            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function workcenterShopOrder(workcenter_structure_id, contract)
    {
        Swal.loading();
        $.ajax({
            type: 'GET',
            url: '/production-area/workcenter-shoporder/'+workcenter_structure_id+'/'+contract,
            success: function(response) {
                Swal.close();

                window.open(response,'_self');

            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function openFullscreenModal() {
        const modal = document.getElementById('pdfModal');

        if (modal.hasAttribute('open')) {
            modal.close();
        }

        modal.showModal();

        const modalContent = document.getElementById('modalContent');
        if (modalContent) {
            modalContent.style.width = '95vw';
            modalContent.style.height = '90vh';
            modalContent.style.minHeight = '90vh';
        }

        const iframe = modalContent.querySelector('iframe');
        if (iframe) {
            iframe.style.height = 'calc(90vh - 52px)';
            iframe.style.minHeight = 'calc(90vh - 52px)';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('pdfModal');
        if (modal && modal.hasAttribute('open')) {
            modal.removeAttribute('open');
        }
    });

    window.addEventListener('resize', function() {
        const modalContent = document.getElementById('modalContent');
        if (modalContent && document.getElementById('pdfModal').hasAttribute('open')) {
            modalContent.style.minHeight = '90vh';
            const iframe = modalContent.querySelector('iframe');
            if (iframe) {
                iframe.style.minHeight = 'calc(90vh - 52px)';
            }
        }
    });

    function toggleModal(show) {
        const modal = document.getElementById('customModal');
        if (show) {
            modal.classList.remove('hidden');
        } else {
        modal.classList.add('hidden');
        }
    }

    function startProduction(op_id, order_no, release_no, sequence_no, workcenter_structure_id) {
        Swal.fire({
            title: 'Start Production?',
            text: 'Do you want to start production for this order?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280', 
            confirmButtonText: 'Yes, start production',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.loading();
                $.ajax({
                    type: 'POST',
                    url: '/shop-order/start-production',
                    data: {
                        op_id: op_id,
                        order_no: order_no,
                        release_no: release_no,
                        sequence_no: sequence_no,
                        workcenter_structure_id: workcenter_structure_id,
                        state: 'production',
                        _token: '". csrf_token() ."'
                    },
                    success: function(response) {
                        Swal.close();
                        toggleProductionButtons();
                        workcenterShopOrder(workcenter_structure_id, response.data.op_id);                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Start Production!',
                            text: response.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    },
                    error: function(response) {
                        const message = response.responseJSON?.message || 'Erro desconhecido.';
                        Swal.close();
                        Swal.alert('Erro', message, 'error');
                    }
                });
            }
        });
    }

    function finishProduction (op_id, order_no, release_no, sequence_no, workcenter_structure_id, operation_no) {



            Swal.fire({
            title: 'Finish Production?',
            text: 'Do you want to finish production for this order?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10B981',
            cancelButtonColor: '#6B7280', 
            confirmButtonText: 'Yes, finish production',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.loading();
                $.ajax({
                    type: 'POST',
                    url: '/shop-order/finish-production',
                    data: {
                        op_id: op_id,
                        order_no: order_no,
                        release_no: release_no,
                        sequence_no: sequence_no,
                        workcenter_structure_id: workcenter_structure_id,
                        state: 'finished',
                        operation_no: operation_no,
                        _token: '". csrf_token() ."'
                    },
                    success: function(response) {
                        Swal.close();
                        toggleProductionButtons();
                        workcenterShopOrder(workcenter_structure_id, response.data.op_id); 
                        Swal.fire({
                            icon: 'success',
                            title: 'Start Production!',
                            text: response.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    },
                    error: function(response) {
                        const message = response.responseJSON?.message || 'Erro desconhecido.';
                        Swal.close();
                        Swal.alert('Erro', message, 'error');
                    }
                });
            }
        });
    }

    function toggleProductionButtons() {
        const btnStart = document.getElementById('btn-start-production');
        const btnFinish = document.getElementById('btn-finish-production');

        if (btnStart) {
            btnStart.classList.add('hidden');
        }

        if (btnFinish) {
            btnFinish.classList.remove('hidden');
        }
    }


    function openDowntimeModal (workcenter_id) {
        const modal = document.getElementById('downtimeDialog');
        if (modal) {
            modal.showModal();
        }

        $.ajax({ 
            type: 'GET',
            url: '/shop-order/downtime-reasons',
            data: {
                workcenter_id: workcenter_id,
                _token: '{{ csrf_token() }}'
            },  
            success: function(response) {
                const select = document.querySelector('select[name=\'downtime_reason\']');
                select.innerHTML = '';

                if (response.data && Array.isArray(response.data)) {
                    response.data.forEach(function(reason) {
                        const option = document.createElement('option');
                        
                        option.value = reason;      
                        option.textContent = reason; 
                        select.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'Nenhuma razão disponível';
                    select.appendChild(option);

                    console.error('Wrong response formmat:', response);
                }
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function submitDowntime (workcenter_structure_id) {
        const comment = document.getElementById('downtime_comment').value;
        const reason = document.getElementById('downtime_reason').value;

        $.ajax({
            type: 'POST',
            url: '". route('shoporder.recordDowntime') ."',
            data: {
                workcenter_structure_id: workcenter_structure_id,
                reason: reason,
                comment: comment,
                _token: '". csrf_token() ."'
            },
            success: function(response) {
                Swal.close();
                document.getElementById('downtimeDialog').close();

                comment.value = '';
                reason.value = '';

                Swal.fire({
                    icon: 'success',
                    title: 'Downtime Registered!',
                    text: response.message,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        })
    }

    function openModalStopDowntime(workcenter_structure_id) {
        const modal = document.getElementById('finishdowntimeDialog');
        if (modal) {
            modal.showModal();
        }

        $.ajax ({ 
            type: 'GET',
            url: '". route('shoporder.getFinishDowntime') ."',
            data: {
                workcenter_structure_id: workcenter_structure_id,
                _token: '". csrf_token() ."'
            },  
            success: function(response) {
                if (response && response.data && response.data.length > 0) {
                    const result = response.data[0];

                    document.getElementById('downtime_start_date').value = result.START_TIME || '';
                    document.getElementById('finish_downtime_reason').value = result.DOWNTIME_CAUSE_ID || '';
                    document.getElementById('finish_downtime_comment').value = result.NOTE_TEXT || '';
                }
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function submitFinishDowntime(workcenter_structure_id) {
        const comment = document.getElementById('finish_downtime_comment').value;
        const reasonField = document.getElementById('finish_downtime_reason');
        const startDateField = document.getElementById('downtime_start_date');

        $.ajax({
            type: 'POST',
            url: '". route('shoporder.recordFinishDowntime') ."',
            data: {
                workcenter_structure_id: workcenter_structure_id,
                comment: comment,
                _token: '". csrf_token() ."'
            },
            success: function(response) {
                Swal.close();
                document.getElementById('finishdowntimeDialog').close();

                comment.value = '';
                reasonField.value = '';
                startDateField.value = '';

                Swal.fire({
                    icon: 'success',
                    title: 'Downtime Registered!',
                    text: response.message,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        })
        
    }

    function openIssueDialog(partNo = null, description = '') {
        const dialog = document.getElementById('issueDialog');
        const partNoInput = document.getElementById('partNo');
        const lotInput = document.getElementById('lot');
        const qtyInput = document.getElementById('quantity');

        lotInput.value = '';
        qtyInput.value = '';

        if (partNo && description) {
            partNoInput.value = partNo + ' - ' + description;
            partNoInput.readOnly = true;
            partNoInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        } else {
            partNoInput.value = '';
            partNoInput.readOnly = false;
            partNoInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        }
        
        dialog.showModal();
    }

    function closeIssueDialog() {
        const dialog = document.getElementById('issueDialog');
        dialog.close();
    }

    function openModalApprove () {
        const modal = document.getElementById('ApproveOperationDialog');
        if (modal) {
            modal.showModal();
        }

        document.getElementById('approveOperationComment').value = '';
    }

    function toggleAdvancedFields() {
        const advanced = document.getElementById('advancedFields');
        advanced.classList.toggle('hidden');
    }

    function toggleRow(element, partNo, workcenterNo, orderNo, releaseNo, sequenceNo, lineId, operationNo) {

        const tr = element.closest('tr');
        const rowId = tr.dataset.id;
        const icon = element;  

        if ($(tr).next().hasClass('details-row')) {
            $(tr).next().remove();

            icon.setAttribute('icon', 'mdi:chevron-down');
        } else {
            var html = '<tr class=\"details-row\">' +
                    '<td colspan=\"100%\">' +
                    fnFormatDetailsSoPno(rowId) +
                    '</td>' +
                    '</tr>';
            $(tr).after(html);

            icon.setAttribute('icon', 'mdi:chevron-up');

            fetchSoPnoDetails(rowId, partNo, workcenterNo, orderNo, releaseNo, sequenceNo, lineId, operationNo);
        }
    }

    function fetchSoPnoDetails(rowId, partNo, workcenterId, orderNo, releaseNo, sequenceNo, lineId, operationNo) {

        $.ajax({
            url: '". route('shoporder.getPnoComponentHistory') ."',
            method: 'GET',
            data: {
                partNo: partNo,
                workcenterId: workcenterId,
                orderNo: orderNo,
                releaseNo: releaseNo,   
                sequenceNo: sequenceNo,
                lineId: lineId,
                _token: '". csrf_token() ."'
            },
            beforeSend: function() {
                var tbody = $('#subSoPnoTable_' + rowId + ' tbody');
                tbody.html('<tr><td colspan=\"4\" class=\"text-center\">Loading...</td></tr>');
            },
            success: function(response) {                
                var tbody = $('#subSoPnoTable_' + rowId + ' tbody');
                tbody.empty(); 

                if (response.data && response.data.length > 0) {
                    $.each(response.data, function(index, item) {
                        var row = '<tr>' +
                            '<td>' + (item.LOT_BATCH_NO || '-') + '</td>' +
                            '<td>' + (item.INVENTORY_QTY || '0') + '</td>' +
                            '<td>' + (item.LOCATION_NO || '-') + '</td>' +
                            '<td>' + (item.DATE_TIME || '-') + '</td>' +
                            '<td class=\"flex items-center justify-center gap-2\">' +
                                '<button type=\"button\"' +
                                ' class=\"flex items-center gap-2 rounded-lg border border-danger-600 text-danger-600 bg-white hover:bg-danger-50 transition px-4 py-2 text-sm font-medium\"' + 
                                ' onclick=\"openScrapComponentDialog(' + item.MATERIAL_HISTORY_ID + ', ' + operationNo + ')\">' +
                                '<iconify-icon icon=\"mdi:trash-can\" class=\"text-lg text-danger-600\"></iconify-icon>' + 
                                ' Report Scrap Component' +
                                '</button>' +
                            '</td>' +
                        '</tr>';
                        tbody.append(row);
                    });
                } else {
                    tbody.html('<tr><td colspan=\"5\" class=\"text-center text-muted\">No registers</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                
                var tbody = $('#subSoPnoTable_' + rowId + ' tbody');
                tbody.html('<tr><td colspan=\"4\" class=\"text-center text-danger\">Erro to find data</td></tr>');
                
                alert('Erro ao carregar detalhes: ' + error);
            }
        });
    }

    function fnFormatDetailsSoPno(id) {
        return '<table id=\"subSoPnoTable_' + id + '\" class=\"table table-bordered\" style=\"width:100%;\">' +
            '<thead><tr><th>LOT</th><th>Quantity Issued</th><th>Location</th><th>DateTime</th><th>Actions</th></tr></thead>' +
            '<tbody></tbody></table>';
    }

    function issueMaterial(button) {

        const partNo = document.getElementById('partNo').value;
        const lot = document.getElementById('lot').value;
        const quantity = document.getElementById('quantity_issue').value;

        console.log(quantity);

        const data = {
            workcenter_id: button.dataset.workcenter,
            partNo,
            lot,
            quantity,
            order_no: button.dataset.order,
            release_no: button.dataset.release,
            sequence_no: button.dataset.sequence,
        };

        $.ajax({
            type: 'POST',
            url: '". route('shoporder.issueMaterial') ."',
            data: {
                ...data,
                _token: '". csrf_token() ."'
            },
            success: function(response) {
                Swal.close();
                closeIssueDialog();
                Swal.fire({
                    icon: 'success',
                    title: 'Material Issued!',
                    text: response.message,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function openScrapOperationDialog(button) {
        const dialog = document.getElementById('ScrapOperationDialog');
        dialog.showModal();
    }

    function reportScrapOperation(button) {

        const scrapCause = document.getElementById('scrapcauses_selected_id').value;
        button.dataset.scrapcause = scrapCause;

        const data = {
            workcenter_id: button.dataset.workcenter,
            quantity: document.getElementById('scrap_qty').value,
            notes: document.getElementById('scrap_notes').value,
            scrap_cause_id: scrapCause,
            order_no: button.dataset.order,
            release_no: button.dataset.release,
            sequence_no: button.dataset.sequence,
            operation_no: button.dataset.operation_no,
        };

        console.log(document.getElementById('scrap_qty').value);

        $.ajax({
            type: 'POST',
            url: '". route('shoporder.reportScrapOperation') ."',
            data: {
                ...data,
                _token: '". csrf_token() ."'
            },
            success: function(response) {
                Swal.close();
                document.getElementById('ScrapOperationDialog').close();

                document.getElementById('scrap_qty').value = '';
                document.getElementById('scrap_notes').value = '';
                document.getElementById('scrapcauses_selected_id').value = '';

                Swal.fire({
                    icon: 'success',
                    title: 'Scrap Operation Reported!',
                    text: response.message,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function openScrapComponentDialog(material_history_id, operation_no) {
        const dialog = document.getElementById('ScrapComponentDialog');
        dialog.showModal();

        const submitBtn = document.getElementById('submitScrapComponentButton');
        submitBtn.setAttribute('data-material_id', material_history_id);
        submitBtn.setAttribute('data-operation_no', operation_no);
    }

    function reportScrapComponent (button) {     
        const materialId = button.dataset.material_id;
        const operationNo = button.dataset.operation_no;
        const quantity = document.getElementById('scrap_component_qty').value;
        const causeId = document.getElementById('scrapcomponentcauses_selected_id').value;
        const notes = document.getElementById('scrap_component_notes').value;

        $.ajax({
            type: 'POST',
            url: '". route('shoporder.reportScrapComponent') ."',
            data: {
                material_history_id: materialId,
                operation_no: operationNo,
                quantity: quantity,
                cause_id: causeId,
                notes: notes,
                _token: '". csrf_token() ."'
            },
            success: function(response) {
                Swal.close();
                document.getElementById('ScrapComponentDialog').close();

                document.getElementById('scrap_component_qty').value = '';
                document.getElementById('scrap_component_notes').value = '';
                document.getElementById('scrapcomponentcauses_selected_id').value = '';

                Swal.fire({
                    icon: 'success',
                    title: 'Scrap Component Reported!',
                    text: response.message,
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            error: function(response) {
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }
</script>";
@endphp

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    <div class="col-span-12 lg:col-span-10 2xl:col-span-10 h-full">
        <div class="card h-full shadow-lg p-0 border-0">

            <div class="card-header py-4 px-6 bg-white dark:bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">
                <div class="col-span-12 mt-3">
                    <div class="grid grid-cols-12">
                        <div class="col-span-12 md:col-span-8 text-sm">
                            <label class="form-label">Workcenter</label>
                            <h6 class="font-semibold mb-0">{{$workcenter->structure_name}}</h6>
                        </div>

                        <div class="col-span-12 md:col-span-4 text-sm text-right">
                            <label class="form-label">Order</label>
                            <h6 class="font-semibold mb-0">{{$shop_order_description}}</h6>
                        </div>
                    </div>

                    <div class="md:col-span-12 col-span-12 mt-2 text-sm">
                        <label class="form-label">Inventory Part</label>
                        <h6 class="font-semibold mb-0">{{$shoporder['PART_NO'] . ' - ' . $shoporder['DESCRIPTION']}}</h6>
                    </div>
                </div>
            </div>

            <div class="card-body" id="divform">

                <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400" id="default-tab" data-tabs-toggle="#default-tab-content" role="tablist">
                        <li role="presentation">
                            <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                                id="manager-production-tab"
                                data-tabs-target="#manager-production"
                                type="button"
                                role="tab"
                                aria-controls="manager-production"
                                aria-selected="false">
                                <i class="ri-tools-fill text-gray-400 text-xl me-2"></i>
                                Manage Production
                            </button>
                        </li>
                        <li role="presentation">
                            <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                                id="validations-tab"
                                data-tabs-target="#validations"
                                type="button"
                                role="tab"
                                aria-controls="validations"
                                aria-selected="false">
                                <i class="ri-check-double-fill text-gray-400 text-xl me-2"></i>
                                Validations
                            </button>
                        </li>
                        <li role="presentation">
                            <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                                id="shoporders-tab"
                                data-tabs-target="#shoporders"
                                type="button"
                                role="tab"
                                aria-controls="shoporders"
                                aria-selected="false">
                               <i class="ri-box-3-line text-gray-400 text-xl me-2"></i>
                                Shop Order
                            </button>
                        </li>
                        <li role="presentation">
                            <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                                id="documentation-tab"
                                data-tabs-target="#documentation"
                                type="button"
                                role="tab"
                                aria-controls="documentation"
                                aria-selected="false">
                                <i class="ri-file-text-fill text-gray-400 text-xl me-2"></i>
                                Documentation
                            </button>
                        </li>
                    </ul>
                </div>
                <div id="default-tab-content">
                    <div id="manager-production" role="tabpanel" aria-labelledby="manager-production-tab" class="">
                         @include('productionarea.manageproduction', [
                            'workcenter' => $workcenter
                        ])
                    </div>
                    <div id="validations" role="tabpanel" aria-labelledby="validations-tab" class="hidden">
                        @include('productionarea.validations', [
                            'workcenter' => $workcenter,
                            'characteristic_validation' => $characteristic_validation,
                        ])
                    </div>
                    <div id="shoporders" role="tabpanel" aria-labelledby="shoporders-tab" class="hidden">
                        @include('productionarea.shoporder', [
                            'workcenter' => $workcenter,
                            'partnocomponent' => $partnocomponent, 
                            'shoporder' => $shoporder,
                        ])
                    </div>
                    <div id="documentation" role="tabpanel" aria-labelledby="documentation-tab" class="hidden">
                        @include('productionarea.documentation', [
                            'workcenter' => $workcenter,
                            'path_wi' => $path_wi ?? null,
                        ])
                    </div>
                </div>

                <div class="col-span-12 flex justify-end items-center gap-2 mt-4">
                    <button type="button" onclick="window.history.back();" class="btn bg-neutral-300 text-neutral-700 hover:bg-neutral-400 btn-sm px-20 py-3 rounded-lg flex items-center justify-center gap-1.5">
                        <iconify-icon icon="mdi:arrow-left" class="text-neutral-700 text-lg mr-2"></iconify-icon>
                        Back to Orders List
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Painel lateral direito (botões) -->
    <div class="col-span-12 lg:col-span-2 2xl:col-span-2 h-full" id="buttonsPanelWrapper">
        <div class="card h-full p-4 py-6 bg-white shadow-lg rounded-xl flex flex-col justify-between border-0">
            <!-- Cabeçalho com ordem e status -->
            <div class="mb-3 col-span-12 md:col-span-12 text-sm">
                <label class="form-label">Workcenter Code</label>
                <h6 class="font-semibold mb-0"">{{$workcenter->structure_code}}</h6>
            </div>

            <!-- Ações principais -->
            <div class="flex flex-col gap-3 mt-3" id="production-buttons">
                <!-- Start Setup -->           
                <button 
                    id="btn-start-setup"
                    type="button"
                    class="flex items-center justify-start p-3 rounded-lg bg-blue-100 hover:bg-blue-200 transition-all border border-blue-300 w-full gap-2"
                    onclick="startSetup()"
                >
                    <iconify-icon icon="mdi:tools" class="text-2xl text-blue-600 mr-3"></iconify-icon>
                    <span class="text-sm font-medium text-blue-800">Start Setup</span>
                </button>  

                @if ($localShopOrder)
                    <!-- Finish Production -->
                    <button 
                        id="btn-finish-production"
                        type="button"
                        class="flex items-center justify-start p-3 rounded-lg bg-yellow-100 hover:bg-yellow-200 transition-all border border-yellow-300 w-full gap-2"
                        onclick="finishProduction('{{ $shoporder['OP_ID'] }}', '{{ $shoporder['ORDER_NO'] }}', '{{ $shoporder['RELEASE_NO'] }}', '{{ $shoporder['SEQUENCE_NO'] }}', '{{ $workcenter->id }}', '{{ $shoporder['OPERATION_NO'] }}' )"
                    >
                        <iconify-icon icon="mdi:stop-circle-outline" class="text-2xl text-yellow-600 mr-3"></iconify-icon>
                        <span class="text-sm font-medium text-yellow-800">Finish Production</span>
                    </button>
                @else
                    <!-- Start Production -->
                    <button 
                        id="btn-start-production"
                        type="button"
                        class="flex items-center justify-start p-3 rounded-lg bg-green-100 hover:bg-green-200 transition-all border border-green-300 w-full gap-2"
                        onclick="startProduction('{{ $shoporder['OP_ID'] }}', '{{ $shoporder['ORDER_NO'] }}', '{{ $shoporder['RELEASE_NO'] }}', '{{ $shoporder['SEQUENCE_NO'] }}', '{{ $workcenter->id }}')"
                    >
                        <iconify-icon icon="mdi:play-circle-outline" class="text-2xl text-green-600 mr-3"></iconify-icon>
                        <span class="text-sm font-medium text-green-800">Start Production</span>
                    </button>
                @endif

                <!-- Start Downtime -->           
                <button 
                    id="btn-start-downtime"
                    type="button"
                    class="flex items-center justify-start p-3 rounded-lg bg-red-100 hover:bg-red-200 transition-all border border-red-300 w-full gap-2"
                    onclick="openDowntimeModal('{{ $workcenter->id }}')"
                >
                    <iconify-icon icon="mdi:alert-octagon-outline" class="text-2xl text-red-600 mr-3"></iconify-icon>
                    <span class="text-sm font-medium text-red-800">Start Downtime</span>
                </button>


                <!-- Finish Downtime -->    
                <button 
                    id="btn-finish-downtime"
                    type="button"
                    class="flex items-center justify-start p-3 rounded-lg bg-green-100 hover:bg-green-200 transition-all border border-green-300 w-full gap-2"
                    onclick="openModalStopDowntime('{{ $workcenter->id }}')"
                >
                    <iconify-icon icon="mdi:clock-check-outline" class="text-2xl text-green-800 mr-3"></iconify-icon>
                    <span class="text-sm font-medium text-green-800">Finish Downtime</span>
                </button>


                <!-- Approve Operation -->
                <button 
                    id="btn-approve-operation"
                    type="button"
                    class="flex items-center justify-start p-3 rounded-lg bg-blue-100 hover:bg-blue-200 transition-all border border-blue-300 w-full gap-2 shadow-sm"
                    onclick="openModalApprove('{{ $workcenter->id }}')"
                >
                    <iconify-icon icon="mdi:check-circle-outline" class="text-2xl text-blue-800 mr-3"></iconify-icon>
                    <span class="text-sm font-medium text-blue-800">Approve Operation</span>
                </button>

            </div>

            <!-- Informações detalhadas -->
            <div class="mt-5">
                <h6 class="text-sm font-semibold text-gray-700 mb-2">Detalhes da Operação</h6>
                <div class="space-y-3">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Iniciado:</span>
                        <span class="font-medium">Hoje, 14:30</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Duração:</span>
                        <span class="font-medium">1h 45min</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Operador:</span>
                        <span class="font-medium">João Silva</span>
                    </div>
                </div>
            </div>

            <!-- Estatísticas rápidas -->
            <div class="mt-5">
                <h6 class="text-sm font-semibold text-gray-700 mb-2">Estatísticas</h6>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <div class="text-xs text-gray-600">Processados</div>
                        <div class="text-lg font-bold text-blue-700">1,254</div>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <div class="text-xs text-gray-600">Taxa</div>
                        <div class="text-lg font-bold text-green-700">98.5%</div>
                    </div>
                </div>
            </div>

            <!-- Green Bar in the right side -->
            @if ($localShopOrder)
            <div class="mt-auto bg-green-500 text-white py-2 px-4 rounded-b-md flex items-center justify-between w-full space-between">
                <span class="text-sm font-medium">In production</span>
                <iconify-icon icon="mdi:cogs" class="text-2xl text-blue-500"></iconify-icon>
            </div>
            @else
            <div class="mt-auto bg-gray-300 text-gray-700 py-2 px-4 rounded-b-md flex items-center justify-between w-full space-between">
                <span class="text-sm font-medium">No active production</span>
                <iconify-icon icon="mdi:information-outline" class="text-2xl text-gray-500"></iconify-icon>
            </div>
            @endif

            <!-- Modal to manage downtimes - start downtime -->
            <dialog id="downtimeDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full">
                <div class="fixed inset-0 flex items-center justify-center bg-black/30 z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg w-[600px] max-w-[95vw] overflow-hidden">
                        <div class="bg-red-50 border-b border-red-100 p-4">
                            <h2 class="text-xl font-semibold text-red-800">Register Downtime</h2>
                            <p class="text-sm text-red-600 mt-1">Do you want start stop in this workcenter ({{$workcenter->structure_code}}) ?</p>
                        </div>
                        
                        <form method="dialog">
                            <div class="grid grid-cols-1 gap-5 p-3">
                                <div class="w-full">
                                    <label for="downtime_reason" class="block text-sm font-medium text-gray-700 mb-2">Downtime Cause</label>
                                    <select
                                        id="downtime_reason"
                                        name="downtime_reason"
                                        required
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    >
                                        <option value="" disabled selected>Selecione the downtime cause</option>
                                    </select>
                                </div>
                                
                                <div class="w-full">
                                    <label for="downtime_comment" class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                                    <textarea
                                        id="downtime_comment"
                                        name="downtime_comment"
                                        placeholder="Please provide additional details about this downtime.."
                                        rows="4"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 p-3">
                                <button
                                    type="button"
                                    onclick="document.getElementById('downtimeDialog').close()"
                                    class="px-5 py-2 text-sm font-medium bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="px-6 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                    onclick="submitDowntime('{{ $workcenter->id }}', document.querySelector('select[name=\'downtime_reason\']').value, document.querySelector('textarea[name=\'downtime_comment\']').value)"
                                >
                                    Confirm Downtime
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </dialog>


            <!-- Modal to manage downtimes - finish downtime -->
            <dialog id="finishdowntimeDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full">
                <div class="fixed inset-0 flex items-center justify-center bg-black/30 z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg w-[600px] max-w-[95vw] overflow-hidden">

                        <div class="bg-red-50 border-b border-red-100 p-4">
                            <h2 class="text-xl font-semibold text-red-800">Finish Downtime</h2>
                            <p class="text-sm text-red-600 mt-1">Do you want to finish downtime for workcenter ({{ $workcenter->structure_code }})?</p>
                        </div>

                        <form method="dialog">
                            <div class="grid grid-cols-1 gap-5 p-4">

                                <div class="w-full">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Downtime Start Date</label>
                                    <input
                                        id="downtime_start_date"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-2.5 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed"
                                    />
                                </div>

                                <div class="w-full">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Downtime Reason</label>
                                    <input
                                        id="finish_downtime_reason"
                                        type="text"
                                        readonly
                                        class="w-full px-4 py-2.5 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed"
                                    />
                                </div>

                                <div class="w-full">
                                    <label for="downtime_comment" class="block text-sm font-medium text-gray-700 mb-2">Comments</label>
                                    <textarea
                                        id="finish_downtime_comment"
                                        name="downtime_comment"
                                        placeholder="Add comments about the downtime..."
                                        rows="4"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 p-3">
                                <button
                                    type="button"
                                    onclick="document.getElementById('finishdowntimeDialog').close()"
                                    class="px-5 py-2 text-sm font-medium bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="px-6 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors"
                                    onclick="submitFinishDowntime('{{ $workcenter->id }}', document.getElementById('finish_downtime_comment').value)"
                                >
                                    Finish Downtime
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </dialog>

            <!-- Modal to approve operation  -->
            <dialog id="ApproveOperationDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full" style="width: 800px;">
                <div class="fixed inset-0 flex items-center justify-center bg-black/30 z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg w-[600px] max-w-[95vw] overflow-hidden">
                        
                        <div class="bg-red-50 border-b border-red-100 p-4">
                            <h2 class="text-xl font-semibold text-red-800">Approve Operation</h2>
                        </div>

                        <form method="dialog">
                            <div class="grid grid-cols-1 gap-5 p-4">

                                
                                <div class="w-full">
                                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <input
                                        id="quantity"
                                        name="quantity"
                                        type="number"
                                        class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg"
                                        placeholder="Quantity produced"
                                        required
                                    />
                                </div>

                                <div class="flex items-center gap-2">
                                    <input type="checkbox" id="showAdvancedFields" onchange="toggleAdvancedFields()" />
                                    <label for="showAdvancedFields" class="text-sm text-gray-700">Use Trestle</label>
                                </div>

                                <div id="advancedFields" class="hidden space-y-4">
                                    <div class="w-full">
                                        <label for="lote" class="block text-sm font-medium text-gray-700 mb-2">Lot</label>
                                        <input
                                            id="lote"
                                            name="lote"
                                            type="text"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg"
                                            placeholder="Lot Number"
                                        />
                                    </div>

                                    <div class="w-full">
                                        <label for="trestle" class="block text-sm font-medium text-gray-700 mb-2">Trestle</label>
                                        <input
                                            id="trestle"
                                            name="trestle"
                                            type="text"
                                            class="w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg"
                                            placeholder="Trestle Code"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 border-t border-gray-100 p-3">
                                <button
                                    type="button"
                                    onclick="document.getElementById('ApproveOperationDialog').close()"
                                    class="px-5 py-2 text-sm font-medium bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    class="px-6 py-2 text-sm font-medium bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors"
                                >
                                    Approve Operation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </dialog> 

            <!-- Modal issue -->
            <dialog id="issueDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full backdrop:bg-black/30">
                <div class="fixed inset-0 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg max-w-[95vw] overflow-hidden p-6" style="width: 500px;">
                        <h3 class="text-lg font-semibold mb-4">Issue Material</h3>

                        <div class="mb-4">
                            <label for="partNo" class="block text-sm font-medium mb-1">Part Number</label>
                            <input 
                                type="text" 
                                id="partNo" 
                                name="part_no" 
                                class="w-full border border-gray-300 rounded-md p-2"
                                placeholder="Part number">
                        </div>

                        <div class="mb-4">
                            <label for="lot" class="block text-sm font-medium mb-1">LOT</label>
                            <input type="text" id="lot" name="lot" class="w-full border border-gray-300 rounded-md p-2" required>
                        </div>

                        <div class="mb-4">
                            <label for="quantity_issue" class="block text-sm font-medium mb-1">Quantity to issue</label>
                            <input type="number" id="quantity_issue" name="quantity_issue" class="w-full border border-gray-300 rounded-md p-2" required min="1">
                        </div>
                        

                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
                                onclick="document.getElementById('issueDialog').close()">
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                data-workcenter="{{ $workcenter['id'] }}"
                                data-part=""
                                data-lot=""
                                data-quantity=""
                                data-order="{{ $shoporder['ORDER_NO'] }}"
                                data-release="{{ $shoporder['RELEASE_NO'] }}"
                                data-sequence="{{ $shoporder['SEQUENCE_NO'] }}"
                                onclick="issueMaterial(this)">
                                Issue
                            </button>
                        </div>
                    </div>
                </div>
            </dialog>

            <!-- Modal Report Scrap Operation -->
            <dialog id="ScrapOperationDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full backdrop:bg-black/30">
                <div class="fixed inset-0 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg max-w-[95vw] overflow-hidden p-6" style="width: 500px;">
                        <h3 class="text-lg font-semibold mb-4">Report Scrap Operation</h3>

                        <div class="mb-4 w-100">
                            <label for="scrap_qty" class="block text-sm font-medium mb-1">Quantity</label>
                            <input type="number" id="scrap_qty" name="lot" class="w-full border border-gray-300 rounded-md p-2" required min="1">
                        </div>

                        <div style = "width: 90%;" class="relative">
                            <div id="searchScrapCausesWrapper" class="">
                                <x-customs.search
                                :api-url="route('shoporder.getScrapCauses', ['id' => $workcenter->id, 'search' => ''])"
                                searchName="scrapcauses"
                                :initial="''"
                                :selected-label="''"
                                :show-code="false"
                                :label="'Search Scrap Causes'"
                                isSearchable="true"
                                :onSelectCallbackName=null
                                />
                            </div>
                        </div>
                        
                        <div class="mb-4 w-100">
                            <label for="scrap_notes" class="block text-sm font-medium mb-1">Notes</label>
                            <textarea id="scrap_notes" name="scrap_notes" class="w-full border border-gray-300 rounded-md p-2" rows="4"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
                                onclick="document.getElementById('ScrapOperationDialog').close()">
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                data-workcenter="{{ $workcenter['id'] }}"
                                data-quantity=""
                                data-scrapCause=""
                                data-scrap_notes=""
                                data-order="{{ $shoporder['ORDER_NO'] }}"
                                data-release="{{ $shoporder['RELEASE_NO'] }}"
                                data-sequence="{{ $shoporder['SEQUENCE_NO'] }}"
                                data-operation_no ="{{ $shoporder['OPERATION_NO'] }}"
                                onclick="reportScrapOperation(this)">
                                Report
                            </button>
                        </div>
                    </div>
                </div>
            </dialog>    

            <!-- Modal Report Scrap Component -->
            <dialog id="ScrapComponentDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full backdrop:bg-black/30">
                <div class="fixed inset-0 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-xl shadow-lg max-w-[95vw] overflow-hidden p-6" style="width: 500px;">
                        <h3 class="text-lg font-semibold mb-4">Report Component Operation</h3>

                        <div class="mb-4 w-100">
                            <label for="scrap_component_qty" class="block text-sm font-medium mb-1">Quantity</label>
                            <input type="number" id="scrap_component_qty" name="scrap_component_qty" class="w-full border border-gray-300 rounded-md p-2" required min="1">
                        </div>

                        <!-- <input type="hidden" id="material_history_id" name="material_history_id"> -->

                        <div style = "width: 90%;" class="relative">
                            <div id="searchScrapCausesWrapper" class="">
                                <x-customs.search
                                :api-url="route('shoporder.getScrapCauses', ['id' => $workcenter->id, 'search' => ''])"
                                searchName="scrapcomponentcauses"
                                :initial="''"
                                :selected-label="''"
                                :show-code="false"
                                :label="'Search Scrap Causes'"
                                isSearchable="true"
                                :onSelectCallbackName=null
                                />
                            </div>
                        </div>
                        
                        <div class="mb-4 w-100">
                            <label for="scrap_component_notes" class="block text-sm font-medium mb-1">Notes</label>
                            <textarea id="scrap_component_notes" name="scrap_component_notes" class="w-full border border-gray-300 rounded-md p-2" rows="4"></textarea>
                        </div>

                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition"
                                onclick="document.getElementById('ScrapComponentDialog').close()">
                                Cancel
                            </button>
                            <button
                                id="submitScrapComponentButton"
                                type="button"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                                onclick="reportScrapComponent(this)">
                                Report
                            </button>
                        </div>
                    </div>
                </div>
            </dialog>  
        </div>
    </div>
</div>

@endsection
