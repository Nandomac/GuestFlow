<div class="mb-6">
    <div class="col-span-12 mt-3">
        <div class="form-group text-end">
            <button type="button" onclick="backDowntimeList()"
                class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="ri-arrow-left-line text-white text-xl"></i>
                Back To Downtime List
            </button>
        </div>
    </div>
    <form id="form_mail_list" action="{{route('workcenter.mail-list-save')}}" method="POST" class="space-y-4">
        @csrf
        @method("POST")
        <input type="hidden" id="workcenter_downtime_id" name="workcenter_downtime_id" value="{{$workcenterDowntime->id}}">
        <input type="hidden" id="id" name="id" value="">
        <input type="hidden" id="emailOld" name="emailOld" value="">

        <div class="px-4">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="w-full md:w-2/3">
                    <label for="Email" class="block text-sm font-medium text-gray-700 dark:text-white mb-1">Email</label>
                    <input type="email" id="email" name="email" required
                        class="w-full rounded-lg border border-gray-300 p-2.5 text-sm text-gray-900 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600 dark:focus:ring-blue-500 dark:focus:border-blue-500">
                </div>
            </div>
            <div class="col-span-12 mt-3">
                <div class="form-group text-end">
                    <button type="button" onclick="mailListSave({{$workcenterDowntime->workcenter_structure_id}}, '{{$workcenterDowntime->downtime_cause_id}}')"
                        class="rounded-lg px-4 py-2 text-white bg-success-600 hover:bg-success-700 focus:outline-none focus:ring-2 focus:ring-blue-500">Save</button>
                    <button type="button" onclick="mailListClear({{$workcenterDowntime->workcenter_structure_id}}, '{{$workcenterDowntime->downtime_cause_id}}')"
                        class="rounded-lg px-4 py-2 bg-neutral-300 text-neutral-700 hover:bg-neutral-400 focus:outline-none focus:ring-2 focus:ring-blue-500">Clear</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="mb-6" id="table_part_no_plains">
    <div class="col-span-12 mt-3 mb-3 p-1">
        <h5 class="text-lg mb-0">Mail List</h5>
    </div>
    <div class="table-responsive">
        <table class="table striped-table mb-0">
            <thead>
                <tr>
                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Email</th>
                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600">Active Date</th>
                    <th scope="col" class="!bg-white dark:!bg-neutral-700 border-b border-neutral-200 dark:border-neutral-600 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($listMails as $listMail)
                    <tr class="odd:bg-neutral-100 dark:odd:bg-neutral-600">
                        <td>
                            {{$listMail->email}}
                        </td>
                        <td>{{$listMail->getCreatedAtFormatted()}}</td>
                        <td class="text-center">
                            <button type="button" id="btnEmail_{{$listMail->id}}" name="btnEmail_{{$listMail->id}}" data-type="{{$listMail->id}}" class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" onclick="mailListEdit({{$listMail->id}}, {{$workcenterDowntime->workcenter_structure_id}}, '{{$workcenterDowntime->downtime_cause_id}}', '{{$listMail->email}}')">Edit</button>
                            <button type="button" id="btnEmail_{{$listMail->id}}" name="btnEmail_{{$listMail->id}}" data-type="{{$listMail->id}}" class="rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500" onclick="mailListRemove({{$listMail->id}}, {{$workcenterDowntime->workcenter_structure_id}}, '{{$workcenterDowntime->downtime_cause_id}}')">Remove</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500 dark:text-gray-300 ">Mail List Not found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
