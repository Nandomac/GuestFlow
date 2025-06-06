<div class="w-full mx-auto">
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400" id="shoporder-tab" data-tabs-toggle="#shoporder-tab-content" role="tablist">
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Raw-Material-tab"
                    data-tabs-target="#Raw-Material"
                    type="button"
                    role="tab"
                    aria-controls="Raw-Material"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Raw Material
                </button>
            </li>
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Trestle-tab"
                    data-tabs-target="#Trestle"
                    type="button"
                    role="tab"
                    aria-controls="Trestle"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Trestle
                </button>
            </li>
        </ul>
    </div>
    <div id="shoporder-tab-content">

        <div id="Raw-Material" role="tabpanel" aria-labelledby="Raw-Material-tab">
            <div class="flex justify-between mb-3">
                <h2 class="text-lg font-semibold mb-4">Material Issued List</h2>
                    <div class="flex justify-end gap-2">

                        <button type="button"
                            class="flex items-center gap-2 rounded-lg border border-danger-600 text-danger-600 bg-white hover:bg-danger-50 transition px-4 py-2 text-sm font-medium"
                            onclick="openScrapOperationDialog()">
                            <iconify-icon icon="mdi:trash-can" class="text-lg"></iconify-icon>
                            Report Scrap Operation
                        </button>

                        <button
                            type="button"
                            class="bg-white border border-blue-600 text-blue-600 hover:bg-blue-50 rounded-lg px-4 py-2 text-sm font-medium flex items-center gap-2 transition"
                            onclick="openIssueDialog()">
                            <iconify-icon icon="mdi:export-variant" class="text-lg"></iconify-icon> 
                            New Issue
                        </button>
                    </div>
            </div>

            <div class="overflow-x-auto">
                <table id="SoPnoTable" class="table striped-table mb-0">
                    <thead>
                        <tr>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600"></th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Part Number</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Description</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Quantity Issued</th>
                            <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Quantity Required</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($partnocomponent as $part)
                            <tr data-id="{{ $part['LINE_ITEM_NO'] }}" class="odd:bg-neutral-100 dark:odd:bg-neutral-600">
                                <td class="text-center">
                                    <iconify-icon 
                                        icon="mdi:chevron-down"
                                        class="expand-icon text-xl cursor-pointer text-neutral-500 hover:text-black transition"
                                        onclick="toggleRow(this, '{{ $part['PART_NO'] }}', '{{ $workcenter['id'] }}', '{{ $shoporder['ORDER_NO'] }}', '{{ $shoporder['RELEASE_NO'] }}', '{{ $shoporder['SEQUENCE_NO'] }}', '{{ $part['LINE_ITEM_NO'] }}', '{{ $shoporder['OPERATION_NO'] }}' )">
                                    </iconify-icon>
                                </td>
                                <td>{{ $part['PART_NO'] }}</td>
                                <td>{{ $part['DESCRIPTION'] }}</td>
                                <td>{{ $part['QTY_REQUIRED'] }}</td>
                                <td>{{ $part['QTY_ISSUED'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="Trestle" role="tabpanel" aria-labelledby="Trestle-tab">
            <div class="max-w-3xl mx-auto">
                <h2 class="text-lg font-semibold mb-4">Trestle</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                @foreach ([
                    ['id' => '001', 'pieces' => 10, 'time' => '14:30', 'items' => ['Item A', 'Item B']],
                    ['id' => '002', 'pieces' => 5, 'time' => '15:45', 'items' => ['Item C']],
                    ['id' => '003', 'pieces' => 8, 'time' => '16:20', 'items' => ['Item D', 'Item E', 'Item F']],
                ] as $cart)
                <div x-data="{ open: false }" class="border border-gray-300 rounded-lg p-5 cursor-pointer select-none" @click="open = !open" role="button" tabindex="0" @keydown.enter="open = !open" @keydown.space.prevent="open = !open" aria-expanded="false" :aria-expanded="open.toString()">
                    <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 tracking-tight">
                        Trestle {{ $cart['id'] }}
                    </h3>
                    <svg :class="{'rotate-90': open}" class="transform transition-transform duration-300 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                    </div>

                    <p class="text-sm text-gray-600 mt-1">Pieces: <span class="font-medium text-gray-700">{{ $cart['pieces'] }}</span></p>
                    <p class="text-xs text-gray-400 mt-0.5">Hour: {{ $cart['time'] }}</p>

                    <div x-show="open" x-transition class="mt-4 border-t border-gray-200 pt-3">
                    <h4 class="text-xs font-semibold text-gray-700 mb-1 mt-2">Material</h4>
                    <ul class="text-xs text-gray-600 list-disc list-inside space-y-0.5">
                        @foreach ($cart['items'] as $item)
                        <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    </div>
                </div>
                @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

