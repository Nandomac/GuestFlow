<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Characteristic;
use App\Models\WorkcenterPart;
use Illuminate\Support\Facades\DB;
use App\Models\WorkcenterStructure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\WorkcenterPartCharacteristic;

class WorkcenterPartCharacteristicController extends Controller
{

    public function index()
    {
        //
    }

    public function create($workcenter_part_id, $partno_id, $characteristic_group_id = null, $characteristic_group = null )
    {
        $workcenterPart = WorkcenterPart::findOrFail($workcenter_part_id);
        $workcenter = WorkcenterStructure::findOrFail($workcenterPart->workcenter_structure_id);
        $arrPath = $workcenter->fullHierarchyPath(' << ', true);

        if ($characteristic_group_id) {
            $workcenterPartCharacteristicList = WorkcenterPartCharacteristic::where('workcenter_part_id', $workcenter_part_id)
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
                'workcenter_part_characteristics.characteristic_group_order',
                'characteristics.description as characteristic_name',
                'characteristics.type as characteristic_type',
                'characteristics.uom as characteristic_unit'
            ])
            ->get();
        } else {
            $workcenterPartCharacteristicList = null;
        }

        $GroupOrder =  DB::table('workcenter_part_characteristics')
        ->join('workcenter_parts', 'workcenter_part_characteristics.workcenter_part_id', '=', 'workcenter_parts.id')
        ->where('workcenter_parts.workcenter_structure_id', $workcenter->id)
        ->where('workcenter_parts.partno_id', $partno_id)
        ->where('workcenter_part_characteristics.characteristic_group_id', $characteristic_group_id)
        ->whereNull('workcenter_part_characteristics.deleted_at')
        ->max('characteristic_group_order');

        $maxGroupOrder = DB::table('workcenter_part_characteristics')
        ->join('workcenter_parts', 'workcenter_part_characteristics.workcenter_part_id', '=', 'workcenter_parts.id')
        ->where('workcenter_parts.workcenter_structure_id', $workcenter->id)
        ->where('workcenter_parts.partno_id', $partno_id)
        ->whereNull('workcenter_part_characteristics.deleted_at')
        ->max('characteristic_group_order');

        $maxGroupCharacteristicOrder = DB::table('workcenter_part_characteristics')
        ->join('workcenter_parts', 'workcenter_part_characteristics.workcenter_part_id', '=', 'workcenter_parts.id')
        ->where('workcenter_parts.workcenter_structure_id', $workcenter->id)
        ->where('workcenter_parts.partno_id', $partno_id)
        ->where('workcenter_part_characteristics.characteristic_group_id', $characteristic_group_id)
        ->whereNull('workcenter_part_characteristics.deleted_at')
        ->max('order');


        return view('workcenterpart.frmWorkcenterPartInsertCharacteristic', [
            'action' => route('workcenter-part-characteristic.store'),
            'actionCancel' => route('workcenter-part.edit', ['id' => $workcenterPart->id]),
            'method' => 'POST',
            'titleCard' => 'New Inventory Part Workcenter Association',
            'workcenter' => $workcenter,
            'workcenterPath' => $arrPath['path'],
            'contract' => $arrPath['path_contract'],
            'workcenterPart' => $workcenterPart,
            'group' => $characteristic_group_id,
            'group_description' => $characteristic_group,
            'group_order' => $GroupOrder ?? $maxGroupOrder + 1,
            'characteristic' => null,
            'workcenterPartCharacteristicList' => $workcenterPartCharacteristicList,
            'maxGroupOrder' => $maxGroupOrder + 1,
            'maxGroupCharacteristicOrder' => $maxGroupCharacteristicOrder + 1,
        ]);
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'workcenter_part_id' => 'required|integer',
            'Group_selected_id' => 'required',
            'Group_selected_description' => 'required',
            'Characteristic_selected_id' => 'required',
            'Characteristic_selected_description' => 'required',
            'cols' => 'required|integer',
            'order' => 'required|integer',
            'nominal_value' => 'required',
            'tolerance_value' => 'required',
        ], [
            'workcenter_part_id.required' => 'Workcenter Part ID is mandatory.',
            'workcenter_part_id.integer' => 'Workcenter Part ID must be an integer.',
            'Group_selected_id.required' => 'Group is mandatory.',
            'Characteristic_selected_id.required' => 'Characteristic is mandatory.',
            'cols.required' => 'Cols is mandatory.',
            'cols.integer' => 'Cols must be an integer.',
            'order.required' => 'Order is mandatory.',
            'order.integer' => 'Order must be an integer.',
            'nominal_value.required' => 'Nominal Value is mandatory.',
            'tolerance_value.required' => 'Tolerance Value is mandatory.',
        ], [
            'workcenter_part_id' => 'Workcenter Part',
            'Group_selected_id' => 'Group',
            'Characteristic_selected_id' => 'Characteristic',
            'cols' => 'Cols',
            'order' => 'Order',
            'nominal_value' => 'Nominal Value',
            'tolerance_value' => 'Tolerance Value',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error.',
                'errors' => $validator->errors()
            ], 422);
        }

        $workcenterPart = WorkcenterPart::findOrFail($request->workcenter_part_id);

        DB::beginTransaction();
        try {
            $workcenterPartCharacteristic = new WorkcenterPartCharacteristic();
            $workcenterPartCharacteristic->workcenter_part_id = $request->workcenter_part_id;
            $workcenterPartCharacteristic->characteristic_id = $request->Characteristic_selected_id;
            $workcenterPartCharacteristic->cols = $request->cols;
            $workcenterPartCharacteristic->order = $request->order;
            $workcenterPartCharacteristic->characteristic_group_id = $request->Group_selected_id;
            $workcenterPartCharacteristic->characteristic_group_order = $request->characteristic_group_order;
            $workcenterPartCharacteristic->nominal_value = $request->nominal_value;
            $workcenterPartCharacteristic->tolerance_value = $request->tolerance_value;
            $workcenterPartCharacteristic->user_create_id = Auth::user()->id;
            $workcenterPartCharacteristic->save();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Inventory Part Characteristic inserted successfully.',
                'group' => $request->Group_selected_id,
                'group_description' => $request->Group_selected_description,
                'redirect' => route('workcenter-part.edit', ['id' => $workcenterPart->id])
                ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error inserting Inventory Part Characteristic: ' . $e->getMessage()
                ], 500);
        }
    }

    public function show(WorkcenterPartCharacteristic $workcenterPartCharacteristic)
    {
        //
    }

    public function edit(WorkcenterPartCharacteristic $workcenterPartCharacteristic)
    {
        //
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $workcenterPartCharacteristicData = WorkcenterPartCharacteristic::whereNull('workcenter_part_characteristics.deleted_at')
            ->findOrFail($id);
            $workcenterPart = WorkcenterPart::findOrFail($workcenterPartCharacteristicData->workcenter_part_id);

            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenterPart->workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {
                $workcenterPartCharacteristicID = WorkcenterPartCharacteristic::whereNull('workcenter_part_characteristics.deleted_at')
                ->join('workcenter_parts', 'workcenter_part_characteristics.workcenter_part_id', '=', 'workcenter_parts.id')
                ->where('workcenter_parts.workcenter_structure_id', $structure->id)
                ->where('workcenter_parts.partno_id', $workcenterPart->partno_id)
                ->where('workcenter_part_characteristics.characteristic_group_id', $workcenterPartCharacteristicData->characteristic_group_id)
                ->where('workcenter_part_characteristics.characteristic_id', $workcenterPartCharacteristicData->characteristic_id)
                ->select('workcenter_part_characteristics.id')
                ->first();

                if ($workcenterPartCharacteristicID) {
                    $workcenterPartCharacteristic = WorkcenterPartCharacteristic::findOrFail($workcenterPartCharacteristicID->id);
                    $workcenterPartCharacteristic->nominal_value = $request->nominal_value;
                    $workcenterPartCharacteristic->tolerance_value = $request->tolerance_value;
                    $workcenterPartCharacteristic->user_update_id = Auth::user()->id;
                    $workcenterPartCharacteristic->save();
                }

            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Inventory Inventory Part Characteristic update successfully.',
                ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Error updating Inventory Inventory Part with Workcenter: ' . $e->getMessage()
                ], 500);
        }

    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $workcenterPartCharacteristicData = WorkcenterPartCharacteristic::whereNull('workcenter_part_characteristics.deleted_at')
            ->findOrFail($id);
            $workcenterPart = WorkcenterPart::findOrFail($workcenterPartCharacteristicData->workcenter_part_id);

            $rootStructure = WorkcenterStructure::with('childrenRecursive')->findOrFail($workcenterPart->workcenter_structure_id);
            $structures = collect([$rootStructure])->merge($this->flattenChildren($rootStructure->childrenRecursive));

            foreach ($structures as $structure) {
                $workcenterPartCharacteristicID = WorkcenterPartCharacteristic::whereNull('workcenter_part_characteristics.deleted_at')
                ->join('workcenter_parts', 'workcenter_part_characteristics.workcenter_part_id', '=', 'workcenter_parts.id')
                ->where('workcenter_parts.workcenter_structure_id', $structure->id)
                ->where('workcenter_parts.partno_id', $workcenterPart->partno_id)
                ->where('workcenter_part_characteristics.characteristic_group_id', $workcenterPartCharacteristicData->characteristic_group_id)
                ->where('workcenter_part_characteristics.characteristic_id', $workcenterPartCharacteristicData->characteristic_id)
                ->select('workcenter_part_characteristics.id')
                ->first();

                if ($workcenterPartCharacteristicID) {
                    $now = Carbon::now();
                    DB::table('workcenter_part_characteristics')
                        ->where('id', $workcenterPartCharacteristicID->id)
                        ->update([
                            'user_delete_id' => Auth::id(),
                            'deleted_at' => $now,
                        ]);
                }

            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Workcenter Part Characteristic removed successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error removing Workcenter Part Characteristic. Details: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroyGrup($workcenter_part_id, $characteristic_group_id )
    {
        try {

            $workcenterPartCharacteristics = WorkcenterPartCharacteristic::where('workcenter_part_id', $workcenter_part_id)
            ->where('characteristic_group_id', $characteristic_group_id)->get();

            if ($workcenterPartCharacteristics->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No Workcenter Part Characteristics found for the given group ID.'
                ], 404);
            }

            $ids = $workcenterPartCharacteristics->pluck('id')->toArray();

            $now = Carbon::now();

            DB::beginTransaction();
            DB::table('workcenter_part_characteristics')
                ->whereIn('id', $ids)
                ->update([
                    'user_delete_id' => Auth::id(),
                    'deleted_at' => $now,
                ]);
            DB::commit();

            $workcenterPartCharacteristics = WorkcenterPartCharacteristic::where('workcenter_part_id', $workcenter_part_id)
            ->where('characteristic_group_id', '<>', $characteristic_group_id)->get();

            if ($workcenterPartCharacteristics->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'message' => 'Workcenter Part Characteristics Group removed successfully.',
                    'isEmpty' => true
                ], 200);
            } else {
                return response()->json([
                    'status' => true,
                    'message' => 'Workcenter Part Characteristics Group removed successfully.',
                    'isEmpty' => false
                ], 200);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error removing Workcenter Part Characteristics Group. Details: ' . $e->getMessage()
            ], 404);
        }
    }

    public function searchAvailableSetupCharacteristics($workcenterPartId, $searchCharacteristic)
    {
        $results = Characteristic::where('type', 'setup')
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->where('description', 'like', '%' . $searchCharacteristic . '%')
            ->whereDoesntHave('workcenterPartCharacteristics', function ($query) use ($workcenterPartId) {
                $query->whereNull('deleted_at')
                      ->where('workcenter_part_id', $workcenterPartId);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'DESCRIPTION' => $item->description,
                ];
            });

        return response()->json($results);
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

}
