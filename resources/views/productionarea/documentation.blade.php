<div class="w-full mx-auto">

    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400" id="documentation-tab" data-tabs-toggle="#documentation-tab-content" role="tablist">
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Specifications-tab"
                    data-tabs-target="#Specifications"
                    type="button"
                    role="tab"
                    aria-controls="Specifications"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Specifications
                </button>
            </li>
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Work-Instruction-tab"
                    data-tabs-target="#Work-Instruction"
                    type="button"
                    role="tab"
                    aria-controls="Work-Instruction"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Work Instruction
                </button>
            </li>
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Production-History-tab"
                    data-tabs-target="#Production-History"
                    type="button"
                    role="tab"
                    aria-controls="Production-History"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Production History
                </button>
            </li>
        </ul>
    </div>
    <div id="documentation-tab-content">

        <div id="Specifications" role="tabpanel" aria-labelledby="Specifications-tab">
            <div class="rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <iframe src="{{ asset('storage/workcenter_files/ACA/ACA_workinstruction.pdf') }}" width="100%" height="600px" class="w-full" frameborder="0"></iframe>
            </div>
        </div>

        <div id="Work-Instruction" role="tabpanel" aria-labelledby="Work-Instruction-tab">
            @if(isset($path_wi) && !empty($path_wi->path))
                <button onclick="openFullscreenModal()" class="inline-flex items-center mb-2 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition mt-1">
                    See More
                </button>
            @endif

            <div class="rounded-xl shadow-lg overflow-hidden border border-gray-200">
                @if(isset($path_wi) && !empty($path_wi->path))
                    <iframe src="{{ asset('storage/' . $path_wi->path) }}" width="100%" height="600px" class="w-full" frameborder="0"></iframe>
                @endif
            </div>
        </div>

        <div id="Production-History" role="tabpanel" aria-labelledby="Production-History-tab">
            <div class="rounded-xl shadow-lg overflow-hidden border border-gray-200">
                <iframe src="{{ asset('storage/workcenter_files/ACA/ACA_workinstruction.pdf') }}" width="100%" height="600px" class="w-full" frameborder="0" frameborder="0">
                </iframe>
            </div>
        </div>
    </div>

</div>

<dialog id="pdfModal" class="fixed inset-0 m-0 p-4 w-full h-full flex items-center justify-center z-50" style="background: rgba(0, 0, 0, 0);">
    <div class="fixed inset-0 bg-black bg-opacity-70" onclick="document.getElementById('pdfModal').close()"></div>
    <div id="modalContent" class="relative bg-white w-full h-full md:w-[95%] md:h-[95%] max-w-none rounded-lg shadow-2xl overflow-hidden z-10" style="min-height: 85vh;">
        <button onclick="document.getElementById('pdfModal').close()" class="absolute top-2 right-2 z-20 bg-red-600 hover:bg-red-700 transition-colors text-white p-1 rounded-full w-8 h-8 flex items-center justify-center">
            <iconify-icon icon="mdi:close" width="20" height="20"></iconify-icon>
        </button>
        @if(isset($path_wi) && !empty($path_wi->path))
            <iframe src="{{ asset('storage/' . $path_wi->path) }}" width="100%" height="100%" style="border: none;"></iframe>
        @endif
    </div>
</dialog>

<style>
#pdfModal {
    display: none;
}

#pdfModal[open] {
    display: flex;
}
</style>
