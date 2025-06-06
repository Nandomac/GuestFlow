<form class="grid grid-cols-12 gap-4" method="POST" action="" id="WorkcenterPartForm">
    @csrf
    @method($method)
    <input type="hidden" id="workcenter_structure_id" name="workcenter_structure_id" value="{{ $workcenter->id }}">
    <input type="hidden" id="id" name="id" value="{{ old('code', $workcenterPart->id ?? null) }}">


    <div class="md:col-span-12 col-span-12">
        <label class="form-label">Workcenter</label>
        <h5 class="text-lg font-semibold mb-0">{{ $workcenterPath }} << {{ $contract }}</h5>
    </div>

        <x-customs.search
            apiUrl="{{ route('ifs.inventory-parts', null) }}"
            searchName="InventoryPart"
            initial=""
            selectedLabel=""
            showCode="true"
            label="Search Inventory Part"
            isSearchable="true"
            :onSelectCallbackName=null
            apiURLParam="/{{ $contract }}"
        />


    <!-- Buttons in their own row with border top -->
    <div class="col-span-12 flex justify-start items-center gap-2">
        <button type="button" onclick="frmSave()"
            class="btn btn-success-700 flex justify-center items-center px-20 py-2.5 text-white bg-success-600 hover:bg-success-700 font-semibold rounded-lg transition duration-200 gap-1.5">
            <i class="ri-save-fill text-white-400 text-xl"></i>
            Save
        </button>
        <a href="{{ $actionCancel }}" class="btn bg-neutral-300 text-neutral-700 hover:bg-neutral-400 btn-sm px-20 py-3 rounded-lg flex items-center justify-center gap-1.5">
            <iconify-icon icon="icon-park-outline:close" class="text-neutral-700 text-lg mr-2"></iconify-icon>
            Cancel
        </a>
    </div>
</form>
