@php
    $groupedCharacteristics = $characteristics_group->groupBy('group_id');
@endphp

<div class="container mx-auto">
    <div class="relative w-full max-w-xs mx-auto mt-4 hidden" id="frmTemplateSetup">
        <!-- Form hidden by default -->
    </div>

    <div class="relative w-full max-w-xs mx-auto mt-4 hidden" id="duplicateTemplateSetup">
        <!-- Form hidden by default -->
    </div>

    <!-- Table section -->
    <div class="col-span-12 lg:col-span-6 block" id="brwTemplateSetup">
        <div class="card border-0 overflow-hidden">
            <div class="card-header flex justify-between items-center !border-b-0">
                <h5 class="card-title text-lg mb-0">Template Setup</h5>
                <div class="flex row gap-2">
                    <button type="button" onclick="duplicateTemplateSetup('{{ $workcenter->id }}')" class="flex justify-center items-center px-3 py-2 text-white bg-primary-600 hover:bg-primary-700 font-medium rounded-lg transition duration-200">
                        <iconify-icon icon="mdi:content-copy" class="text-white text-lg mr-1"></iconify-icon>
                        Duplicate Template
                    </button>
                    <button
                        type="button"
                        onclick="addTemplateSetupCharacteristic('{{ $workcenter->id }}', '')"
                        class="flex justify-center items-center px-3 py-2 text-white bg-primary-600 hover:bg-primary-700 font-medium rounded-lg transition duration-200"
                    >
                        <iconify-icon icon="mdi:plus" class="text-white text-lg mr-1"></iconify-icon>
                        Add Characteristic
                    </button>
                </div>
            </div>

            <!-- Card body with table -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="gridTable" class="table display mb-0">
                        <thead>
                            <tr>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">groupID</th>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Group</th>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Order</th>
                                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groupedCharacteristics as $group_id => $characteristics)
                                <tr data-id="{{ $characteristics->first()->group_id }}" class="bg-neutral-50 dark:bg-neutral-700 font-medium">
                                    <td>{{ $characteristics->first()->group_id }}</td>
                                    <td>{{ $characteristics->first()->group_name }}</td>
                                    <td>{{ $characteristics->first()->characteristic_group_order }} </td>
                                    <td>
                                        <div class="flex gap-2 items-center">
                                            <button
                                                type="button"
                                                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" 
                                                onclick="addTemplateSetupCharacteristic('{{ $workcenter->id }}', '{{ $group_id }}')"
                                            >
                                            <!-- <iconify-icon icon="mdi:plus" class="text-white text-lg mr-1"></iconify-icon> -->
                                                Edit
                                            </button>

                                            <button type="button"
                                                class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                                onclick="deleteTemplateSetupCharacteristic('{{ $workcenter->id }}', '{{ $group_id }}')">
                                                Remove
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
