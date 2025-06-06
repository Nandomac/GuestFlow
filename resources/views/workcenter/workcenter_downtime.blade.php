<div class="table-responsive" id="divDowntimeList">
    <table class="table striped-table mb-0">
        <thead>
            <tr>
                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Downtime</th>
                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Description</th>
                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Status</th>
                <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($downtimes as $downtime)
                @php
                    $checked = '';
                    $disabled = 'disabled';
                    $btnClass = 'btn text-white px-4 py-2 rounded bg-gray-400 cursor-not-allowed';
                    $btnToggleDowntime = '';
                    $prefixDescription = '';
                @endphp

                @if (strtoupper($downtime['GLOBAL_DOWNTIME']) == 'YES')

                    @php
                        $checked = 'checked="checked"';
                        $btnToggleDowntime = 'disabled';
                        $disabled = '';
                        $btnClass = 'btn bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded';
                        $prefixDescription = '[G] - ';
                    @endphp

                @else

                    @foreach ($workcenter->allAncestorDowntimes() as $selectedDowntime)
                        @if ($selectedDowntime->downtime_cause_id == $downtime['DOWNTIME_CAUSE_ID'])
                            @php
                                $checked = 'checked="checked"';
                                $disabled = '';
                                $btnClass = 'btn bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded'
                            @endphp
                        @endif
                    @endforeach

                @endif

                <tr class="odd:bg-neutral-100 dark:odd:bg-neutral-600">
                    <td>{{$prefixDescription}} {{ $downtime['DOWNTIME_CAUSE_ID'] }}</td>
                    <td>{{$prefixDescription}}{{ $downtime['DESCRIPTION'] }}</td>
                    <td class="text-center">
                        <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox"
                            class="sr-only peer"
                            {{ $checked }}
                            {{$btnToggleDowntime}}
                            onclick="toggleDowntime('{{ $downtime['DOWNTIME_CAUSE_ID'] }}', '{{ $workcenter->id }}', this.checked)">
                            <span class="relative w-11 h-6 bg-gray-400 peer-focus:outline-none rounded-full peer dark:bg-gray-500 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-success-600"></span>
                            <span class="line-height-1 font-medium ms-3 peer-checked:text-success-600 text-md text-gray-600 dark:text-gray-300"></span>
                        </label>
                    </td>
                    <td>
                        <button type="button" id="btnEmailListModal_{{$downtime['DOWNTIME_CAUSE_ID']}}" name="btnEmailListModal_{{$downtime['DOWNTIME_CAUSE_ID']}}" data-type="{{$downtime['DOWNTIME_CAUSE_ID']}}" class="{{$btnClass}}" {{$disabled}} onclick="openEmailListModal('{{ $downtime['DOWNTIME_CAUSE_ID'] }}', '{{ $workcenter->id }}');">Mail List</button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div id="divMailList">

</div>



