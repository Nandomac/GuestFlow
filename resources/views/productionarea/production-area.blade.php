@extends('layouts.layout')

@php
$title = 'Workcenter';
$subTitle = 'Backoffice / Workcenters List';
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

                if (result[1]) {
                    $('#headerDonwtimeDetail').removeClass('hidden');
                }

               
                workcenterCode = result[2];

                workcenter_structure_id = result[3];
                getWorkcenterShopOrdersList(workcenterCode, result[1]);

                $('#workcenterCodePlaceholder').text(workcenterCode);
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

    function findShopOrder(event) {

        var orderNo = document.querySelector('input[name=\"order_no\"]').value;
        var releaseNo = document.querySelector('input[name=\"release_no\"]').value;
        var sequenceNo = document.querySelector('input[name=\"sequence_no\"]').value;

        event.preventDefault();
        Swal.loading();

        $.ajax({
            type: 'POST',
            url: '/production-area/find-shop-order',
            data: {
                workcenter_structure_id: workcenter_structure_id,
                order_no: orderNo,
                release_no: releaseNo,
                sequence_no: sequenceNo,
                _token: '". csrf_token() ."'
            },
            success: function(response) {
                Swal.close();
                resetModal();

                // STATUS 2 → order related to present workcenter
                if (response.status === 1) {
                    document.getElementById('shopOrderDialog').close();

                    Swal.fire({
                        icon: 'success',
                        title: 'Shop Order founded',
                        text: 'The shop order was successfully found for the workcenter.' + response.workcenter_code + '.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        workcenterShopOrder(workcenter_structure_id, response.data[0].OP_ID);
                    });

                // STATUS 1 → order related to another workcenter
                } else if (response.status === 2) {
                    document.getElementById('shopOrderDialog').close();
                    Swal.alert_dialog(
                        'Order do not belongs to this Workcenter:',
                        response.message + '. Do you want to switch to this Workcenter?',
                        'warning',
                        true, 
                        function() {
                            workcenterShopOrder(workcenter_structure_id, response.data[0].OP_ID);
                        }
                    );
                }
            },
            error: function(response) {
                resetModal();    
                const message = response.responseJSON?.message || 'Unknown error.';
                Swal.close();
                Swal.alert('Error', message, 'error');
            }
        });
    }

    function resetModal () {
        document.querySelector('input[name=\"order_no\"]').value = '';
        document.querySelector('input[name=\"release_no\"]').value = '';
        document.querySelector('input[name=\"sequence_no\"]').value = '';

        document.getElementById('shopOrderDialog').close();
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


</script>";
@endphp

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 min-h-[600px]">
    <!-- Painel da árvore (esquerda) -->
    <div class="col-span-12 lg:col-span-4 2xl:col-span-4 h-full" id="panelTreeWrapper">
        <div class="card h-full p-0 border-0 overflow-auto">
            <div class="card-body p-6" id="paneltree">
                <x-customs.treeview :tree="$tree" :showAllDetails=false />
            </div>
        </div>
    </div>

    <!-- Painel de abas (centro) -->
    <div class="col-span-12 lg:col-span-8 2xl:col-span-8 h-full" id="panelTabsWrapper">
        <div class="card h-full flex flex-col p-0 border-0">
            <div class="card-header py-4 px-6 bg-white dark:bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600 flex justify-between items-center">
                <h6 class="text-lg mb-0" id="headerWorcenterDetail">Select Workcenter</h6>
                <div class="hidden flex row gap-2" id = "headerDonwtimeDetail">
                    <button 
                        id="btn-start-downtime"
                        type="button"
                        class="flex items-center justify-start p-3 rounded-lg bg-red-100 hover:bg-red-200 transition-all border border-red-300 w-1/4 gap-2"
                        onclick="openDowntimeModal(workcenter_structure_id)"
                    >
                        <iconify-icon icon="mdi:alert-octagon-outline" class="text-2xl text-red-600 mr-3"></iconify-icon>
                        <span class="text-sm font-medium text-red-800">Start Downtime</span>                       
                    </button>

                    <button 
                        id="btn-finish-downtime"
                        type="button"
                        class="flex items-center justify-start p-3 rounded-lg bg-green-100 hover:bg-green-200 transition-all border border-green-300 w-1/4 gap-2"
                        onclick="openModalStopDowntime(workcenter_structure_id)"
                    >
                        <iconify-icon icon="mdi:calendar-check-outline" class="text-2xl text-green-800 mr-3"></iconify-icon>
                        <span class="text-sm font-medium text-green-800">Finish Downtime</span>
                    </button>
                </div>
            </div>

            <div class="overflow-y-auto flex-grow p-6" style="max-height: calc(80vh - 120px);">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 m-4 overflow-auto" id="listShopOrders">

                </div>
            </div>
        </div>
    </div>


    <dialog id="shopOrderDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full" onsubmit="findShopOrder(event)">
        <div class="fixed inset-0 flex items-center justify-center bg-black/30 z-50">
            <div class="bg-white rounded-xl p-6 shadow-xl w-96 max-w-[90vw]">
            <form method="dialog" class="space-y-4">
                <h2 class="text-lg font-semibold text-gray-800">Search Shop Order</h2>

                <input
                    type="text"
                    name="order_no"
                    placeholder="Order Number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                />
                <input
                    type="text"
                    name="release_no"
                    placeholder="Release Number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                />

                <input
                    type="text"
                    name="sequence_no"
                    placeholder="Sequence Number"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
                />

                <div class="flex justify-end gap-2 pt-4">
                    <button
                        type="button"
                        onclick="document.getElementById('shopOrderDialog').close()"
                        class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                        Confirm
                    </button>
                </div>
            </form>
            </div>
        </div>
    </dialog>

    <!-- start downtime dialog -->

    <dialog id="downtimeDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full">
        <div class="fixed inset-0 flex items-center justify-center bg-black/30 z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg w-[600px] max-w-[95vw] overflow-hidden">
                <div class="bg-red-50 border-b border-red-100 p-4">
                    <h2 class="text-xl font-semibold text-red-800">Register Downtime</h2>
                    <p class="text-sm text-red-600 mt-1">Do you want start stop in this workcenter (<span id="workcenterCodePlaceholder"></span>) ?</p>
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
                            onclick="submitDowntime(workcenter_structure_id , document.querySelector('select[name=\'downtime_reason\']').value, document.querySelector('textarea[name=\'downtime_comment\']').value)"
                        >
                            Confirm Downtime
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>

    <!-- finish downtime dialog -->

    <dialog id="finishdowntimeDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full">
        <div class="fixed inset-0 flex items-center justify-center bg-black/30 z-50 p-4">
            <div class="bg-white rounded-xl shadow-lg w-[600px] max-w-[95vw] overflow-hidden">
                <!-- Header -->
                <div class="bg-red-50 border-b border-red-100 p-4">
                    <h2 class="text-xl font-semibold text-red-800">Finish Downtime</h2>
                    <p class="text-sm text-red-600 mt-1">Do you want to finish downtime for workcenter (<span id="workcenterCodePlaceholder"></span>)?</p>
                </div>

                <form method="dialog">
                    <div class="grid grid-cols-1 gap-5 p-4">
                        
                        <!-- Fixed Date (readonly) -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Downtime Start Date</label>
                            <input
                                id="downtime_start_date"
                                type="text"
                                readonly
                                class="w-full px-4 py-2.5 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed"
                            />
                        </div>

                        <!-- Fixed Text Input (readonly) -->
                        <div class="w-full">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Downtime Reason</label>
                            <input
                                id="finish_downtime_reason"
                                type="text"
                                readonly
                                class="w-full px-4 py-2.5 text-sm bg-gray-100 border border-gray-300 rounded-lg text-gray-600 cursor-not-allowed"
                            />
                        </div>

                        <!-- Editable Textarea -->
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

                    <!-- Footer buttons -->
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
                            onclick="submitFinishDowntime(workcenter_structure_id)"
                        >
                            Finish Downtime
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </dialog>    

</div>
@endsection

<style>
    @media (max-width: 1669px) {
    #listShopOrders {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
  }
</style>

