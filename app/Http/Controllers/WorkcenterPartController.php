<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkcenterPart;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\WorkcenterTemplate;
use Illuminate\Support\Facades\DB;
use App\Models\WorkcenterStructure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkcenterPartCharacteristic;
use App\Exceptions\WorkcenterTemplateException;

class WorkcenterPartController extends Controller
{

    public function index()
    {
        $workcenterPart = WorkcenterPart::all();
        return view('workcenterpart.brwWorkcenterPart',
         [
            'workcenterPart' => $workcenterPart,
            'titleCard' => 'Inventory Part Workcenter Association List',
         ]);
    }

    public function create()
    {
        $workcenterPart = null;

        $structure_tree = WorkcenterStructure::whereNull('structure_parent_id')
        ->with('childrenRecursive')
        ->get();

        return view('workcenterpart.frmWorkcenterPart', [
            'workcenterPart' => $workcenterPart,
            'action' => route('workcenter-part.store'),
            'actionCancel' => route('workcenter-part.index'),
            'method' => 'POST',
            'titleCard' => 'New Inventory Part Workcenter Association',
            'tree' => $structure_tree,
        ]);
    }

    public function createFrom($id)
    {
        $workcenter = WorkcenterStructure::findOrFail($id);

        $arrPath = $workcenter->fullHierarchyPath(' << ', true);

        return view('workcenterpart.frmWorkcenterPartInsert', [
            'action' => route('workcenter-part.store'),
            'actionCancel' => route('workcenter-part.index'),
            'method' => 'POST',
            'workcenter' => $workcenter,
            'workcenterPath' => $arrPath['path'],
            'contract' => $arrPath['path_contract']
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'workcenter_structure_id' => 'required|integer',
            'InventoryPart_selected_id' => 'required',
            'InventoryPart_selected_description' => 'required',
        ], [
            'workcenter_structure_id.required' => 'Workcenter Structure ID is mandatory.',
            'workcenter_structure_id.integer' => 'Workcenter Structure ID must be an integer.',
            'InventoryPart_selected_id.required' => 'Inventory Part is mandatory.',
            'InventoryPart_selected_description.required' => 'Inventory Part Description is mandatory.',
        ], [
            'workcenter_structure_id' => 'Workcenter Structure',
            'InventoryPart_selected_id' => 'Inventory Part',
            'InventoryPart_selected_description' => 'Inventory Part Description',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 404);
        }

        $workcenter = WorkcenterStructure::find($request->workcenter_structure_id);
        if (!$workcenter) {
            return response()->json([
                'status' => 'error',
                'message' => 'The Workcenter Structure does not exist.'
            ], 404);
        }

        $existingPart = WorkcenterPart::where('workcenter_structure_id', $request->workcenter_structure_id)
            ->where('partno_id', $request->InventoryPart_selected_id)
            ->first();
        if ($existingPart) {
            return response()->json([
                'status' => 'error',
                'message' => 'The Inventory Part is already associated with the Workcenter.'
            ], 409);
        }


        try {
            $structure = WorkcenterStructure::with('childrenAllRecursive')->findOrFail($request->workcenter_structure_id);

            $data = $this->processStructureRecursively($structure, $request->InventoryPart_selected_id, $request->InventoryPart_selected_description,null);

            return response()->json([
                'success' => true,
                'message' => 'Inventory Part associated with Workcenter and descendants successfully.',
                'redirect' => $data['redirect'],
            ], 200);

        } catch (WorkcenterTemplateException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (store): ' . $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error (store): ' . $e->getMessage(),
            ], 500);
        }

    }

    public function show(WorkcenterPart $workcenterPart)
    {
        return response()->json($workcenterPart);
    }

    public function edit($id)
    {
        $workcenterPart = WorkcenterPart::findOrFail($id);
        $workcenter = WorkcenterStructure::findOrFail($workcenterPart->workcenter_structure_id);
        $arrPath = $workcenter->fullHierarchyPath(' << ', true);
        $workcenter_part_characteristic_group = WorkcenterPartCharacteristic::with('characteristicGroup')
            ->join('workcenter_parts', 'workcenter_parts.id', '=', 'workcenter_part_characteristics.workcenter_part_id')
            // ->join('workcenter_template as wt', function ($join) {
            //     $join->on('wt.characteristic_id', '=', 'workcenter_part_characteristics.characteristic_id')
            //          ->on('wt.workcenter_structure_id', '=', 'workcenter_parts.workcenter_structure_id')
            //          ->whereNull('wt.deleted_at');
            // })
            ->where('workcenter_part_characteristics.workcenter_part_id', $workcenterPart->id)
            ->select('workcenter_part_characteristics.workcenter_part_id', 'workcenter_part_characteristics.characteristic_group_id')
            ->groupBy('workcenter_part_characteristics.workcenter_part_id', 'workcenter_part_characteristics.characteristic_group_id')
            ->orderBy('workcenter_part_characteristics.characteristic_group_order', 'asc')
            ->get();

        $workcenter_part_characteristic = WorkcenterPartCharacteristic::with('characteristic')
            ->where('workcenter_part_id', $workcenterPart->id)
            ->orderBy('order', 'asc')
            ->get();

        return view('workcenterpart.frmWorkcenterPartEdit', [
            'workcenterPart' => $workcenterPart,
            'action' => route('workcenter-part.update', ['id' => $workcenterPart->id]),
            'actionCancel' => route('workcenter-part.index'),
            'method' => 'PUT',
            'titleCard' => 'Edit Inventory Part Workcenter Association',
            'workcenter' => $workcenter,
            'workcenterPath' => $arrPath['path'],
            'contract' => $arrPath['path_contract'],
            'workcenterPartCharacteristicsGroup' => $workcenter_part_characteristic_group,
            'workcenterPartCharacteristics' => $workcenter_part_characteristic
        ]);
    }

    public function update(Request $request, WorkcenterPart $workcenterPart)
    {
        //
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $workcenterPart = WorkcenterPart::findOrFail($id);
            $workcenterPart->user_delete_id = Auth::user()->id;
            $workcenterPart->save();
            $workcenterPart->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Inventory Part Workcenter Association deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error deleting Inventory Part Workcenter Association: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetailWorkcenterPartGroup(Request $request, $workcenter_part_id, $characteristic_group_id)
    {
        $pageLength = intval($request->length ?? 0);
        $skip = intval($request->start ?? 0);

        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = WorkcenterPartCharacteristic::where('workcenter_part_id', $workcenter_part_id)
            ->where('characteristic_group_id', $characteristic_group_id)
            ->join('characteristics', 'characteristics.id', '=', 'workcenter_part_characteristics.characteristic_id')
            ->select([
                'workcenter_part_characteristics.id',
                'workcenter_part_characteristics.workcenter_part_id',
                'workcenter_part_characteristics.characteristic_id',
                'workcenter_part_characteristics.cols',
                'workcenter_part_characteristics.order',
                'workcenter_part_characteristics.nominal_value',
                'workcenter_part_characteristics.tolerance_value',
                'workcenter_part_characteristics.characteristic_group_id',
                'characteristics.description as characteristic_name',
                'characteristics.type as characteristic_type',
                'characteristics.uom as characteristic_unit'
            ]);

        $search_arr = $request->search;
        $search = $search_arr['value'] ?? '';

        $query = $query->where(function($query) use ($search){
            if($search != "") {
                $query->orWhere('workcenter_part_characteristics.order', 'like', "%".$search."%");
                $query->orWhere('characteristics.description', 'like', "%".$search."%");
                $query->orWhere('workcenter_part_characteristics.cols', 'like', "%".$search."%");
                $query->orWhere('workcenter_part_characteristics.nominal_value', 'like', "%".$search."%");
                $query->orWhere('workcenter_part_characteristics.tolerance_value', 'like', "%".$search."%");
            }
        });

        $orderByName = match ($orderColumnIndex) {
            '0' => 'workcenter_part_characteristics.order',
            '1' => 'characteristics.description',
            '2' => 'workcenter_part_characteristics.cols',
            '3' => 'workcenter_part_characteristics.nominal_value',
            '4' => 'workcenter_part_characteristics.tolerance_value',
            default => 'workcenter_part_characteristics.order'
        };

        $query = $query->orderBy($orderByName, $orderBy);

        $recordsFiltered = $recordsTotal = $query->count();

        if ($pageLength > 0) {
            $data = $query->skip($skip)->take($pageLength)->get();
        } else {
            $data = $query->get();
        }

        return response()->json([
            'draw' => intval($request->draw ?? 0),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ], 200);
    }

    public function createWorkcenterPartGroupCharacteristic($workcenter_part_id)
    {
        $workcenterPart = WorkcenterPart::findOrFail($workcenter_part_id);
        $workcenter = WorkcenterStructure::findOrFail($workcenterPart->workcenter_structure_id);
        $arrPath = $workcenter->fullHierarchyPath(' << ', true);

        return view('workcenterpart.frmWorkcenterPartInsertCharacteristic', [
            'action' => route('workcenter-part.group-characteristic-create', ['id' => $workcenterPart->id]),
            'actionCancel' => route('workcenter-part.edit', ['id' => $workcenterPart->id]),
            'method' => 'POST',
            'titleCard' => 'New Inventory Part Workcenter Association',
            'workcenter' => $workcenter,
            'workcenterPath' => $arrPath['path'],
            'contract' => $arrPath['path_contract'],
            'characteristic_group_id' => null,
            'workcenterPart' => $workcenterPart,
        ]);
    }

    public function list(Request $request) {

        $pageNumber = ( $request->start / $request->length )+1;
        $pageLength = $request->length;
        $skip       = ($pageNumber-1) * $pageLength;

        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = DB::table('workcenter_parts')->select([
            'workcenter_parts.id',
            'workcenter_parts.partno_id',
            'workcenter_parts.partno_description',
            'workcenter_parts.created_at',
            'workcenter_parts.updated_at',
            'view_workcenter_structures_path.structure_name',
            'view_workcenter_structures_path.structure_path'
        ])
        ->join('view_workcenter_structures_path', 'view_workcenter_structures_path.id', '=', 'workcenter_parts.workcenter_structure_id')
        ->whereNull('workcenter_parts.deleted_at');

        // Search
        $search_arr = $request->search;
        $search = $search_arr['value'];

        if (!empty($search)) {
            $query->where(function($query) use ($search) {
                $query->orWhere('workcenter_parts.partno_id', 'like', "%".$search."%")
                      ->orWhere('workcenter_parts.partno_description', 'like', "%".$search."%")
                      ->orWhere('view_workcenter_structures_path.structure_code', 'like', "%".$search."%")
                      ->orWhere('view_workcenter_structures_path.structure_name', 'like', "%".$search."%")
                      ->orWhere('view_workcenter_structures_path.structure_path', 'like', "%".$search."%")
                      ->orWhere('workcenter_parts.created_at', 'like', "%".$search."%")
                      ->orWhere('workcenter_parts.updated_at', 'like', "%".$search."%");
            });
        }

        $orderByName = match($orderColumnIndex) {
            '0' => 'workcenter_parts.partno_id',
            '1' => 'workcenter_parts.partno_description',
            '2' => 'view_workcenter_structures_path.structure_code',
            '3' => 'view_workcenter_structures_path.structure_name',
            '4' => 'view_workcenter_structures_path.structure_path',
            '5' => 'workcenter_parts.created_at',
            '6' => 'workcenter_parts.updated_at',
            default => 'workcenter_parts.partno_id',
        };

        $query->orderBy($orderByName, $orderBy);
        $recordsFiltered = $recordsTotal = $query->count();
        $data = $query->skip($skip)->take($pageLength)->get();

        return response()->json([
            'draw' => $request->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ], 200);
    }

    public function generatePDF($id) {

        // go to WorkcenterPart and return the workcenter_part_id
        $workcenterPart = WorkcenterPart::findOrFail($id);

        $workcenterPartId = $workcenterPart->id;

        // with the workcenter_part_id get the info
        $characteristics = DB::table('workcenter_part_characteristics as wpc')
        ->join('workcenter_parts as wp', 'wpc.workcenter_part_id', '=', 'wp.id')
        ->join('characteristics as c', 'wpc.characteristic_id', '=', 'c.id')
        ->join('workcenter_template as wt', function ($join) {
            $join->on('wpc.characteristic_id', '=', 'wt.characteristic_id')
                 ->on('wt.workcenter_structure_id', '=', 'wp.workcenter_structure_id')
                 ->whereNull('wt.deleted_at');
        })
        ->join('characteristic_groups as cg', 'wt.characteristic_group_id', '=', 'cg.id')
        ->join('workcenter_structures as ws', 'wp.workcenter_structure_id', '=', 'ws.id')
        ->where('wpc.workcenter_part_id', $workcenterPartId)
        ->whereNull('wpc.deleted_at')
        ->select(
            'wpc.*',
            'wp.partno_id',
            'wp.partno_description',
            'wpc.nominal_value',
            'wpc.tolerance_value',
            'c.description as characteristic_description',
            'c.uom',
            'cg.name as group_name',
            'wt.characteristic_group_order',
            'wt.order as characteristic_order',
            'ws.structure_code',
            'ws.structure_name',
            'ws.structure_type',
            'ws.structure_contract',
            'ws.structure_parent_id',
            'ws.multibatch'
        )
        ->distinct()
        ->orderBy('wt.characteristic_group_order', 'asc')
        ->orderBy('wt.order', 'asc')
        ->get();

        $workcenter_code = $characteristics->first()->structure_code;
        $workcenter_name = $characteristics->first()->structure_name;

        $characteristic_uom = $characteristics->first()->uom;

        $partnoId = $characteristics->first()->partno_id;
        $partnoDescription = $characteristics->first()->partno_description;

        $data = [
            'characteristics' => $characteristics,
            'partno_id' => $partnoId,
            'partno_description' => $partnoDescription,
            'workcenter_code' => $workcenter_code,
            'workcenter_name' => $workcenter_name,

        ];
        $pdf = Pdf::loadView('workcenterpart.pdfTemplate', $data);

        return $pdf->download('workcenter_part_characteristics.pdf');

    }

    private function processStructureRecursively(WorkcenterStructure $structure, $partno_id, $partnoDescription, $workcenterPartObj)
    {
        $result = null;
        $redirect = null;

        DB::beginTransaction();
        try {

            $workcenterTemplate = WorkcenterTemplate::where('workcenter_template.workcenter_structure_id', $structure->id)
            ->join('characteristics', 'characteristics.id', '=', 'workcenter_template.characteristic_id')
            ->whereNull('characteristics.deleted_at')
            ->whereNull('workcenter_template.deleted_at')
            ->where('type', 'setup')
            ->get();

            if (count($workcenterTemplate) > 0) {

                $workcenterPart = new WorkcenterPart();
                $workcenterPart->partno_id = $partno_id;
                $workcenterPart->partno_description = $partnoDescription;
                $workcenterPart->workcenter_structure_id = $structure->id;
                $workcenterPart->user_create_id = Auth::user()->id;
                $workcenterPart->save();

                if ($workcenterPartObj === null) {
                    $workcenterPartObj = $workcenterPart;
                    $redirect = route('workcenter-part.edit', ['id' => $workcenterPartObj->id]);
                }

                foreach ($workcenterTemplate as $template) {
                    $workcenter_part_characteristic = new WorkcenterPartCharacteristic();
                    $workcenter_part_characteristic->workcenter_part_id = $workcenterPart->id;
                    $workcenter_part_characteristic->characteristic_id = $template->characteristic_id;
                    $workcenter_part_characteristic->cols = $template->cols;
                    $workcenter_part_characteristic->order = $template->order;
                    $workcenter_part_characteristic->characteristic_group_id = $template->characteristic_group_id;
                    $workcenter_part_characteristic->characteristic_group_order = $template->characteristic_group_order;
                    $workcenter_part_characteristic->user_create_id = Auth::user()->id;
                    $workcenter_part_characteristic->save();
                }

            }

            DB::commit();

            $result = [
                'success' => true,
                'message' => 'Inventory Part associated with Workcenter successfully.',
                'data' => $workcenterPartObj,
                'redirect' => $redirect
            ];

        } catch (\Throwable $e) {
            throw new WorkcenterTemplateException("Error while associating (processStructureRecursively): " . $e->getMessage());
        }

        if ($workcenterPartObj) {

            foreach ($structure->childrenRecursive as $child) {
                $this->processStructureRecursively($child, $partno_id, $partnoDescription, $workcenterPartObj);
            }

        } else {
            throw new WorkcenterTemplateException("Error while associating (processStructureRecursively): Workcenter Template Not Found" );
        }

        return $result;
    }

    private function flattenChildren($children)
    {
        $all = collect();

        foreach ($children as $child) {
            $all->push($child);
            if ($child->childrenRecursive->isNotEmpty()) {
                $all = $all->merge($this->flattenChildren($child->childrenRecursive));
            }
        }

        return $all;
    }

    public function updateGroupOrder(Request $request)
    {
        DB::beginTransaction();
        try {

            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($request->workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {

                foreach ($request->novasOrdems as $item) {

                    DB::table('workcenter_part_characteristics')
                    ->join('workcenter_parts', 'workcenter_parts.id', '=', 'workcenter_part_characteristics.workcenter_part_id')
                    ->where('workcenter_parts.workcenter_structure_id', $structure->id)
                    ->where('workcenter_parts.partno_id', $request->partno_id)
                    ->where('workcenter_part_characteristics.characteristic_group_id', $item['id'])
                    ->whereNull('workcenter_part_characteristics.deleted_at')
                    ->update([
                        'workcenter_part_characteristics.characteristic_group_order' => $item['ordemDestino'],
                        'workcenter_part_characteristics.user_update_id' => Auth::id(),
                        'workcenter_part_characteristics.updated_at' => now()
                    ]);

                }

            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Characteristic Group Reorder successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateCharacteristicOrder(Request $request)
    {
        DB::beginTransaction();
        try {

            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($request->workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {

                foreach ($request->novasOrdems as $item) {

                    DB::table('workcenter_part_characteristics')
                    ->join('workcenter_parts', 'workcenter_parts.id', '=', 'workcenter_part_characteristics.workcenter_part_id')
                    ->where('workcenter_parts.workcenter_structure_id', $structure->id)
                    ->where('workcenter_parts.partno_id', $request->partno_id)
                    ->where('workcenter_part_characteristics.characteristic_id', $item['characteristic_id'])
                    ->where('workcenter_part_characteristics.characteristic_group_id', $item['characteristic_group_id'])
                    ->whereNull('workcenter_part_characteristics.deleted_at')
                    ->update([
                        'workcenter_part_characteristics.order' => $item['ordemDestino'],
                        'workcenter_part_characteristics.user_update_id' => Auth::id(),
                        'workcenter_part_characteristics.updated_at' => now()
                    ]);

                }

            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Characteristic Group Reorder successfully.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
