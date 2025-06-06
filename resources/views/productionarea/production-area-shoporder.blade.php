    @php
        $colorOrderState = 'text-gray-800';
    @endphp


    <div
        onclick="document.getElementById('shopOrderDialog').showModal()"
        class="bg-gray-100 rounded-xl p-8 shadow-inner flex items-center justify-center hover:bg-gray-200 transition cursor-pointer"
        style="width: 338px;height: 265px;">
            <div class="flex flex-row items-center space-y-2 text-gray-600 gap-2">
                <iconify-icon icon="mdi:plus" class="text-black" width="35" height="35"></iconify-icon>
                <span class="text-md font-semibold">Find new shop order</span>
            </div>
    </div>

    @foreach ($shoporders as $shoporder)

        <div class="bg-gray-100 rounded-xl p-5 shadow-inner space-y-4 hover:bg-gray-200 transition cursor-pointer"
            onclick="workcenterShopOrder(workcenter_structure_id,{{$shoporder['OP_ID']}})"
            style="width: 338px;height: 265px;">
            <h2 class="text-lg font-semibold text-gray-700">ORD: {{$shoporder['ORDER_NO']}} - {{$shoporder['RELEASE_NO']}} - {{$shoporder['SEQUENCE_NO']}} </h2>
            <div class="bg-white border border-gray-300 rounded-lg p-4 shadow-sm">

                @if ($shoporder['SHOP_ORDER_STATE'] == 'Started')
                    @php $colorOrderState = 'text-green-600'; @endphp
                @else
                    @php $colorOrderState = 'text-gray-800'; @endphp
                @endif

                <table>
                    <tbody>
                        <tr>
                            <td colspan="2">
                                <p class="text-lg text-gray-800 font-semibold"><span class=" {{$colorOrderState}} font-bold">
                                        {{ $shoporder['SHOP_ORDER_STATE'] == $shoporder['OPERATION_STATE'] ? $shoporder['SHOP_ORDER_STATE'] : $shoporder['SHOP_ORDER_STATE'] .' - '. $shoporder['OPERATION_STATE']  }}
                                    </span>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="text-md text-gray-500">Planned Start</p>
                            </td>
                            <td>
                                <p class="text-md text-gray-500"> : {{ \Carbon\Carbon::parse($shoporder['PLANNED_START_DATE'])->format('d/m/Y') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p class="text-md text-gray-500">Start In</p>
                            </td>
                            <td>
                                <p class="text-md text-gray-500"> : {{ $shoporder['START_TIME'] ? \Carbon\Carbon::parse($shoporder['START_TIME'])->format('d/m/Y') : '' }}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                @php
                    $due     = $shoporder['REVISED_QTY_DUE'] ?? 0;
                    $done    = $shoporder['QTY_COMPLETE'] ?? 0;
                    $percent = $due > 0 ? ($done / $due) * 100 : 0;

                    switch (true) {
                        case $percent >= 100:
                            $colorClass = 'text-green-600 dark:text-green-600';
                            break;
                        case $percent >= 80:
                            $colorClass = 'text-lime-600 dark:text-lime-400';
                            break;
                        case $percent >= 50:
                            $colorClass = 'text-orange-600 dark:text-orange-400';
                            break;
                        case $percent > 0:
                            $colorClass = 'text-red-600 dark:text-red-400';
                            break;
                        default:                    // 0 %
                            $colorClass = 'text-gray-500 dark:text-gray-400';
                    }
                @endphp

                <table class="w-full">
                    <thead>
                        <tr>
                            <th>
                                QTY DUE
                            </th>
                            <th>
                                COMPLETE
                            </th>
                            <th>
                                SCRAPPED
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">
                                <p class="text-md font-bold text-blue-600">{{ $shoporder['REVISED_QTY_DUE']}} {{ $shoporder['UNIT_MEAS']}}</p>
                            </td>
                            <td class="text-center">
                                <p class="text-md font-bold {{ $colorClass }}">{{ $shoporder['QTY_COMPLETE']}} {{ $shoporder['UNIT_MEAS']}}</p>
                            </td>
                            <td class="text-center">
                                <p class="text-md font-bold text-gray-500">{{ $shoporder['QTY_SCRAPPED']}} {{ $shoporder['UNIT_MEAS']}}</p>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-600 mt-3">
                    <div class="bg-green-500 h-2.5 rounded-full" style="width: {{$percent <= 100 ? $percent : 100 }}%"></div>
                </div>

            </div>

        </div>

    @endforeach




