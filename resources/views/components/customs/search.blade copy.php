{{--
Component: <x-search-dropdown />

Props:
- apiUrl (string, obrigatório): Endpoint da busca (ex: /api/clientes)
- searchName (string, obrigatório): Nome base único para campos e estado
- initial (string, opcional): Valor inicial no campo de busca
- selectedLabel (string, opcional): Texto do campo selecionado (readonly)
- showCode (bool, opcional): Exibe o ID junto da descrição se true
- label (string, opcional): Label acima do campo
- isSearchable (bool, opcional): Desabilita busca se false
- onSelectCallbackName (string, opcional): Nome da função global JS ao selecionar/limpar
--}}

@props([
    'apiUrl',
    'searchName',
    'initial' => '',
    'selectedLabel' => '',
    'showCode' => false,
    'label' => 'Search',
    'isSearchable' => true,
    'onSelectCallbackName' => null,
])

<div
    x-data="searchComponent(
        '{{ $apiUrl }}',
        @js($initial),
        {{ $showCode ? 'true' : 'false' }},
        @js($selectedLabel),
        {{ $isSearchable ? 'true' : 'false' }},
        '{{ $onSelectCallbackName }}',
        '{{ $searchName }}'
    )"
    x-init="init()"
    class="md:col-span-12 col-span-12 relative"
>
    <label for="{{ $searchName }}_search" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">
        {{ $label }}
    </label>

    <div class="grid grid-cols-12 gap-4">

        <div class="md:col-span-3 col-span-12">
            <input
                type="text"
                id="{{ $searchName }}_search"
                x-model="search"
                @input.debounce.300ms="fetchSuggestions"
                @focus="open = true; fetchSuggestions()"
                @blur="close()"
                placeholder="Search..."
                class="w-full border border-gray-300 rounded px-3 py-2"
                :disabled="!isSearchable"
            />
            <div class="text-red-500 text-sm mt-1" id="{{ $searchName }}_selected_id-error"></div>
        </div>


        <div class="md:col-span-8 col-span-12">
            <input
                type="text"
                id="{{ $searchName }}_selected"
                x-model="selectedLabel"
                readonly
                placeholder="Selected Description..."
                class="w-full border border-gray-300 rounded px-3 py-2 bg-gray-100 text-gray-700"
            />
            <div class="text-red-500 text-sm mt-1" id="{{ $searchName }}_selected_description-error"></div>
        </div>


        <div class="flex items-center pt-2">
            <button type="button"
                    @click="clearSelection"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Limpar
            </button>
        </div>
    </div>


    <ul
        x-show="open"
        x-transition
        class="absolute z-10 mt-1 w-3/12 bg-white border border-gray-300 rounded shadow scrollable-dropdown"
        @mousedown.away="open = false"
    >
        <template x-if="suggestions.length > 0">
            <template x-for="item in suggestions" :key="item.ID">
                <li
                    @click="select(item)"
                    class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                    x-text="showCode ? `${item.ID} - ${item.DESCRIPTION}` : item.DESCRIPTION"
                ></li>
            </template>
        </template>

        <template x-if="suggestions.length === 0">
            <li class="px-3 py-2 text-gray-500">No results found</li>
        </template>
    </ul>


    <input type="hidden" id="{{ $searchName }}_selected_id" name="{{ $searchName }}_selected_id" x-model="selectedId" />
    <input type="hidden" id="{{ $searchName }}_selected_description" name="{{ $searchName }}_selected_description" x-model="selectedDescription" />
</div>

<script>
    function searchComponent(apiUrl, initialValue = '', showCode = false, selectedLabelInit = '', isSearchable = true, onSelectCallbackName = null, searchName = '') {
        return {
            search: initialValue,
            open: false,
            suggestions: [],
            selectedId: null,
            selectedDescription: null,
            selectedLabel: selectedLabelInit,
            showCode,
            isSearchable,
            onSelectCallbackName,
            searchName,
            selectFirst: false,

            init() {
                if (this.search.length >= 3 && this.isSearchable) {
                    if (initialValue) {
                        this.fetchSuggestions();
                        this.selectFirst = true;
                    } else {
                        this.fetchSuggestions();
                        this.selectFirst = false;
                    }

                }
            },

            async fetchSuggestions() {
                if (!this.isSearchable || this.search.length < 3) {
                    this.suggestions = [];
                    return;
                }

                try {
                    const response = await fetch(`${apiUrl}/${this.search}`);
                    if (!response.ok) throw new Error("Failed to fetch suggestions");

                    const data = await response.json();
                    this.suggestions = data;

                    if (this.selectFirst && data.length > 0) {
                        this.setSelection(data[0]);
                    }

                    this.selectFirst = false;

                } catch (error) {
                    console.error(error);
                    this.suggestions = [];
                }
            },

            select(item) {
                this.setSelection(item);
                this.open = false;

                if (this.onSelectCallbackName && typeof window[this.onSelectCallbackName] === 'function') {
                    window[this.onSelectCallbackName](item);
                }
            },

            setSelection(item) {
                this.selectedLabel = this.showCode
                    ? `${item.ID} - ${item.DESCRIPTION}`
                    : item.DESCRIPTION;
                this.selectedId = item.ID;
                this.selectedDescription = item.DESCRIPTION;
            },

            clearSelection() {
                this.search = '';
                this.selectedLabel = '';
                this.selectedId = null;
                this.selectedDescription = '';
                this.suggestions = [];
                this.open = false;

                if (this.onSelectCallbackName && typeof window[this.onSelectCallbackName] === 'function') {
                    window[this.onSelectCallbackName](null);
                }
            },

            close() {
                setTimeout(() => this.open = false, 200);
            }
        }
    }
</script>
