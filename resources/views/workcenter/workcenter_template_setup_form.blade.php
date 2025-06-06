
        <div class="form-group text-end">
            <button type="button" onclick="showTableSetup('{{ $workcenter->id }}')"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                Back To Template Setup Table
            </button>
        </div>

        <div id="searchCharacteristicGroupWrapper" class="">
        <x-customs.search
            :api-url="route('workcenter.searchGroupCharacteristics', ['id' => $workcenter->id, 'search' => ''])"
            searchName="Groupcharacteristic"
            :initial="''"
            :selected-label="$group?->name ?? ''"
            :selected-id="$group_id ?? ''"
            :show-code="false"
            :label="'Search Group Characteristics'"
            :isSearchable="!$group"
            :onSelectCallbackName="'getGroupOrder'"
        />
        </div>

        <div id="groupOrderWrapper" class="mb-2 {{ !$group_id ? 'hidden' : '' }}">
            <div class="w-1/2">
                <label for="group_order_input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Group Order</label>
                <input type="text" id="group_order_input"
                    value="{{ $groupOrder ?? '' }}"
                    class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Group Order">
            </div>
        </div>

        <div id="searchCharacteristicWrapper" class="">
            <x-customs.search
                :api-url="route('workcenter.searchSetupCharacteristics', ['id' => $workcenter->id, 'search' => ''])"
                searchName="characteristicSetup"
                :initial="''"
                :selected-label="$characteristic_description ?? ''"
                :selected-id="$characteristic_id ?? ''"
                :show-code="false"
                :label="'Search Setup Characteristics'"
                :isSearchable="!isset($characteristic_id)"
                :onSelectCallbackName="'getCharacteristicUOM'"
            />
        </div>

        <input type="hidden" id="workcenter_structure_setup_id" value="{{ $workcenter->id }}">
        <input type="hidden" id="template_id_hidden"  value="{{ $template_id ?? '' }}">
        

        <!-- Wrapper of Characteristic UOM -->
        <!-- <div id="charUOMWrapper" class="mb-2 {{ !isset($characteristic_id) || !$characteristic_id ? 'hidden' : '' }}">
            <div class="w-1/2">
                <label for="char_uom_input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Characteristic UOM</label>
                <input type="text" id="char_uom_input"
                    value="{{ $characteristics_uom ?? '' }}"
                    class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Group Order"
                    disabled
                    >
                    
            </div>
        </div> -->

        <div class="grid grid-cols-2 gap-4 mb-4 mt-2">
            <div class="w-full">
                <label for="cols_input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Cols</label>
                <input type="text" id="cols_input_setup"
                    value="{{ $characteristic_cols ?? '2' }}"
                    class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Number of cols">
            </div>
            <div class="w-full">
                <label for="order_input" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Order</label>
                <input type="text" id="order_input_setup"
                    value="{{ $characteristic_order ?? $maxGroupCharacteristicOrder ?? '' }}"
                    class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                    placeholder="Order Number">
            </div>
        </div>
        <input type="hidden" id="characteristic_id">
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="col-span-2 flex items-end gap-2">
                <button type="button" class="w-full flex justify-center items-center px-4 py-2.5 text-white bg-success-600 hover:bg-success-700 font-semibold rounded-lg transition duration-200"     onclick="saveCharacteristicSetup(
                    document.getElementById('characteristicSetup_selected_id').value,
                    document.getElementById('Groupcharacteristic_selected_id').value,
                    document.getElementById('cols_input_setup').value,
                    document.getElementById('order_input_setup').value,
                    document.getElementById('workcenter_structure_setup_id').value,
                    document.getElementById('template_id_hidden').value,
                    document.getElementById('group_order_input').value,
                )">
                    <i class="ri-save-fill text-black-400 text-xl me-2"></i>
                    Save
                </button>

                <button type="button" onclick="showTableSetup('{{ $workcenter->id }}')" class="w-full bg-neutral-300 text-neutral-700 hover:bg-neutral-400 btn-sm px-4 py-2.5 rounded-lg flex items-center justify-center gap-1.5">
                    <iconify-icon icon="icon-park-outline:close" class="text-neutral-700 text-lg mr-2"></iconify-icon>
                    Cancel
                </button>
            </div>
        </div>
    </div>

    @if(is_iterable($characteristics) && count($characteristics))
        <div class="mt-4">
            <h6 class="text-md font-semibold mb-2">Group {{ $group->name }} Characteristics</h6>
            <table class="table striped-table mb-0">
                <thead>
                    <tr>
                        <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Characteristic</th>
                        <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Cols</th>
                        <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Order</th>
                        <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($characteristics as $char)
                        @if(!$group_id || ($char->type != 'validation'))
                        <tr class="odd:bg-neutral-100 dark:odd:bg-neutral-600">
                            <td>{{ $char->description }}</td>
                            <td>{{ $char->cols }}</td>
                            <td>{{ $char->order }}</td>
                            <td>
                                <button type="button"
                                        class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        onclick="addTemplateSetupCharacteristic('{{ $workcenter->id }}', '{{ $group->id }}', '{{$char->description}}', '{{$char->cols}}','{{$char->order}}', '{{$char->id}}', '{{ $char->template_id }}')" >
                                        Edit
                                </button>
                                <button type="button"
                                        class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                        onclick="deleteSetupTemplateCharacteristic('{{ $workcenter->id }}', '{{ $group->id }}', '{{$char->description}}', '{{$char->cols}}','{{$char->order}}', '{{$char->id}}', '{{ $char->template_id }}')">
                                        Remove
                                </button>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <script>
        document.getElementById('Groupcharacteristic_selected_id').value = "{{ $group_id }}";
    </script>




