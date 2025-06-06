<div id="default-home" role="tabpanel" aria-labelledby="default-info-tab">
    <!-- Select Yes / No -->
    <div class="mb-3">
        <label for="multibatch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Multi-Batch</label>
        <select id="multibatch" name="multibatch" class="w-full p-2.5 bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            <option value="" disabled {{$workcenter->multibatch == '' ? 'selected' : ''  }}>Choose an option</option>
            <option value="yes" {{$workcenter->multibatch == 'yes' ? 'selected' : ''  }}>Yes</option>
            <option value="no" {{$workcenter->multibatch == 'no' ? 'selected' : ''  }}>No</option>
        </select>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="w-full">
            <label for="input1" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">IFS Location</label>
            <input type="text" id="input1" readonly disabled
                value="{{ $locations[0]['LOCATION_NO'] ?? 'No location' }}"
                class="block w-full p-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                placeholder="Introduza o valor">
            @if($workcenter->structure_type == 'WC')
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Critical Workcenter</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                            id="criticalCheckbox"
                            class="sr-only peer"
                            disabled
                            onclick="toggleCriticalWorkcenter(this.checked, '{{ $workcenter->id }}')"
                            {{ $workcenter->isCritical == '1' ? 'checked' : '' }}>
                        <span class="relative w-11 h-6 bg-gray-400 peer-focus:outline-none rounded-full peer dark:bg-gray-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-success-600"></span>
                    </label>
                </div>
            @endif
            @if($workcenter->structure_type == 'D')
                <div class="mt-4">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Department Orders</label>
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                            id="criticalCheckbox"
                            class="sr-only peer"
                            onclick="toggleDepartmentOrder(this.checked, '{{ $workcenter->id }}')"
                            {{ $workcenter->departmentOrder == '1' ? 'checked' : '' }}>
                        <span class="relative w-11 h-6 bg-gray-400 peer-focus:outline-none rounded-full peer dark:bg-gray-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-success-600"></span>
                    </label>
                </div>
            @endif
        </div>

        <div class="w-full">
            @if(isset($file_path))
            <div class="mb-2">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Current Work Instruction</label>
                <a href="{{ Storage::url($file_path) }}?v={{ time() }}" target="_blank" class="flex items-center text-blue-600 hover:text-blue-800">
                    <i class="ri-file-pdf-fill text-red-500 text-xl me-2"></i>
                    View Work Instruction
                </a>
            </div>
            @endif

            <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_input">
                {{ isset($file_path) ? 'Replace Work Instruction' : 'Upload Work Instruction' }}
            </label>
            <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_input" name="file_input" type="file">
        </div>
    </div>

    <input hidden name="workcenter_structure_id" id="workcenter_structure_id" value="{{ $workcenter->id }}">
    <input hidden name="structure_code" id="structure_code" value="{{ $workcenter->structure_code }}">

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div class="w-full flex items-end">
            <button type="button" onclick="printLabel('{{ $workcenter->structure_code }}','{{ $workcenter->id }}')" class="w-full flex justify-center items-center px-4 py-2.5 text-white bg-blue-600 hover:bg-blue-700 font-semibold rounded-lg transition duration-200">
                <i class="ri-printer-fill text-white-400 text-xl me-2"></i>
                Print Label
            </button>
        </div>

        <div class="w-full flex items-end">
        <button type="button" onclick="preSaveInfo(document.getElementById('file_input'), {{ $workcenter->id }}, '{{ $workcenter->structure_type }}', '{{ $workcenter->structure_code }}', $('#multibatch').val())" class="w-full flex justify-center items-center px-4 py-2.5 text-white bg-success-600 hover:bg-success-700 font-semibold rounded-lg transition duration-200">
            <i class="ri-save-fill text-white-400 text-xl me-2"></i>
            Save
        </button>
        </div>
    </div>
</div>
