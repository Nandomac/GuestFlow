<div>
    <div class="form-group text-start mb-6">
        <button type="button" onclick="hideFormShowTable('{{ $workcenter_id }}')"
            class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Back To Template Validation Table
        </button>
    </div>

    <div class="flex flex-wrap -mx-2">
        <div class="w-full md:w-1/2 mb-6">
            <label for="machineSelect" class="block mb-2 text-sm font-semibold text-gray-700">
                Machine to Duplicate:
            </label>
            <select id="machineSelect" name="machineSelect"
                class="w-full border border-gray-300 rounded-lg p-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                <option value="">Select a machine</option>
                @foreach ($hierarchy_machines as $machine)
                    <option value="{{ $machine['ID'] }}">{{ $machine['DESCRIPTION'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="form-group text-start mb-6 ml-8">
        <button type="button"
            onclick="confirmDuplicate('{{ $workcenter_id }}', document.getElementById('machineSelect').value)"
            class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Duplicate Template
        </button>
    </div>

</div>
    
<script>
    if (!window.selectElementInitialized) {
        const selectElement = document.getElementById('machineSelect');
        let selectedMachineId = null;

        selectElement.addEventListener('change', function() {
            if (this.value) {
                selectedMachineId = this.value;
            } else {
                selectedMachineId = null;
            }
        });
        window.selectElementInitialized = true;
    }
</script>