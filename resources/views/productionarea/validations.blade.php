<div class="w-full mx-auto">

    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500 dark:text-gray-400" id="validation-tab" data-tabs-toggle="#validation-tab-content" role="tablist">
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Release-tab"
                    data-tabs-target="#Release"
                    type="button"
                    role="tab"
                    aria-controls="Release"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Release
                </button>
            </li>
            <li role="presentation">
                <button class="inline-flex items-center px-4 py-2.5 font-semibold border-b-2 rounded-t-lg hover:text-blue-600 hover:border-blue-600 dark:hover:text-blue-500 transition-all"
                    id="Machine-Setup-tab"
                    data-tabs-target="#Machine-Setup"
                    type="button"
                    role="tab"
                    aria-controls="Machine-Setup"
                    aria-selected="false">
                    <!-- <i class="ri-pause-circle-fill text-gray-400 text-xl me-2"></i> -->
                    Machine Setup
                </button>
            </li>
        </ul>
    </div>
    <div id="validation-tab-content">

        <div id="Release" role="tabpanel" aria-labelledby="Release-tab">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($characteristic_validation as $characteristic)
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <!-- OK/NOK -->
                            @if ($characteristic->datetype === 'ok/nok')
                                <input
                                    type="text"
                                    value="{{ $characteristic->characteristic_description }}"
                                    disabled
                                    class="flex-1 px-3 py-1.5 text-sm rounded-md border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <select
                                    name="characteristics[{{ $characteristic->characteristic_id }}][status]"
                                    class="w-24 px-2 py-1.5 text-sm bg-gray-50 border border-gray-300 rounded-md appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="OK">OK</option>
                                    <option value="NOK">NOK</option>
                                </select>
                            @elseif ($characteristic->datetype === 'text')
                                <!-- Text -->
                                <input
                                    type="text"
                                    value="{{ $characteristic->characteristic_description }}"
                                    disabled
                                    class="flex-1 px-3 py-1.5 text-sm rounded-md border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <input
                                    type="text"
                                    name="characteristics[{{ $characteristic->characteristic_id }}][value]"
                                    class="w-12 px-3 py-1.5 text-sm rounded-md border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                                />
                                <input
                                    type="text"
                                    value="{{ $characteristic->uom }}"
                                    disabled
                                    class="w-11 px-2 py-1.5 text-sm bg-gray-50 border border-gray-300 rounded-md appearance-none focus:outline-none focus:ring-1 focus:ring-blue-500"
                                />
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="flex justify-end mt-6">
                <button type="button" class="bg-blue-600 text-white hover:bg-blue-700 rounded-lg px-5 py-[11px] text-sm font-medium flex items-center gap-2 transition">
                    <iconify-icon icon="mdi:content-save" class="text-xl"></iconify-icon> Save Release
                </button>
            </div>
        </div>

        <div id="Machine-Setup" role="tabpanel" aria-labelledby="Machine-Setup-tab">
            <iframe src="{{ asset('storage/workcenter_files/ACA/ACA_workinstruction.pdf') }}" width="100%" height="600px" frameborder="0"></iframe>
        </div>
    </div>

</div>

<dialog id="newReleaseDialog" class="bg-transparent p-0 m-0 max-h-full max-w-full">
  <div class="fixed inset-0 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 shadow-lg w-96 max-w-[90vw]">
      <form method="dialog" class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-800">Nova Release (Teste)</h2>

        <input
          type="text"
          placeholder="Input 1"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
        />
        <input
          type="text"
          placeholder="Input 2"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500"
        />

        <div class="flex justify-end gap-2 pt-4">
          <button
            type="button"
            onclick="document.getElementById('newReleaseDialog').close()"
            class="px-4 py-2 text-sm bg-gray-200 rounded hover:bg-gray-300"
          >
            Cancelar
          </button>
          <button
            type="submit"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
          >
            Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</dialog>
