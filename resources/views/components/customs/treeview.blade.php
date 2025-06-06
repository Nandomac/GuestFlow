@props([
    'tree' => collect(),
    'treeId' => 'treeview_' . uniqid(),
    'showAllDetails' => true
])

<div x-data="{
    search: '',
    toggleAll(open = true) {
        this.$refs.treeWrapper.querySelectorAll('details:not([data-root])').forEach(d => d.open = open);
    },
    matchesSearch(text) {
        return text.toLowerCase().includes(this.search.toLowerCase());
    }
}" class="space-y-4">

    <!-- Botões -->
    <div class="flex justify-between items-center gap-3">
        <div class="flex gap-1">
            <button @click="toggleAll(true)" class="px-2 py-1 bg-success-500 text-white rounded text-sm">Expand all</button>
            <button @click="toggleAll(false)" class="px-2 py-1 bg-danger-500 text-white rounded text-sm">Minimize all</button>
            <button @click="sync()" type="button" class="px-2 py-1 bg-info-500 text-white rounded text-xl">
                <i class="ri-loop-right-line"></i>
            </button>
        </div>
    </div>

    <!-- Árvore -->
    <div x-ref="treeWrapper" class="max-h-[568px] overflow-y-auto pr-2">
        @php
            $renderedContracts = [];
        @endphp

        @foreach ($tree as $contract)
            @if (!in_array($contract->structure_contract, $renderedContracts))
                @php
                    $renderedContracts[] = $contract->structure_contract;
                @endphp

                <ul id="{{ $treeId . '_' . Str::slug($contract->structure_contract, '_') }}" class="tree text-lg leading-6 font-bold text-neutral-800 dark:text-white">
                    <li>
                        <details open data-root class="mb-2">
                            <summary>CONTRACT: {{ $contract->structure_contract }}</summary>
                            <ul>
                                @foreach ($tree->where('structure_contract', $contract->structure_contract) as $departament)
                                    <li class="mb-2" data-node-id="{{ $treeId . '_dept_' . $departament->structure_code }}">
                                        <details class="mb-2">
                                            <summary>
                                                @if ($showAllDetails)
                                                    <a @click="workcenter_details({{ $departament->id }})">
                                                        {{ $departament->structure_code }} - {{ $departament->structure_name }}
                                                    </a>
                                                @else
                                                    <a>
                                                        {{ $departament->structure_code }} - {{ $departament->structure_name }}
                                                    </a>
                                                @endif
                                            </summary>
                                            @if ($departament->children)
                                                <ul>
                                                    @foreach ($departament->children as $prod_line)
                                                        <li class="mb-2" data-node-id="{{ $treeId . '_line_' . $prod_line->structure_code }}">
                                                            <details class="mb-2">
                                                                <summary class="mb-2">
                                                                    @if ($showAllDetails)
                                                                        <a @click="workcenter_details({{ $prod_line->id }})">
                                                                            {{ $prod_line->structure_code }} - {{ $prod_line->structure_name }}
                                                                        </a>
                                                                    @else
                                                                        <a>
                                                                            {{ $prod_line->structure_code }} - {{ $prod_line->structure_name }}
                                                                        </a>
                                                                    @endif
                                                                </summary>
                                                                @if ($prod_line->children)
                                                                    <ul>
                                                                        @foreach ($prod_line->children as $workcenter)
                                                                            <li class="mb-3" @click="workcenter_details({{ $workcenter->id }})">
                                                                                <a href="javascript:void(0)">
                                                                                    {{ $workcenter->structure_code }} - {{ $workcenter->structure_name }}
                                                                                </a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @endif
                                                            </details>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </details>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </li>
                </ul>
            @endif
        @endforeach
    </div>
</div>
