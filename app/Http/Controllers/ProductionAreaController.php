<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OracleService;
use Illuminate\Support\Facades\DB;
use App\Models\WorkcenterStructure;
use App\Models\ShopOrder;

class ProductionAreaController extends Controller
{

    protected OracleService $oracleService;

    public function __construct(OracleService $oracleService)
    {
        $this->oracleService = $oracleService;
    }

    public function index()
    {
        $structure_tree = WorkcenterStructure::whereNull('structure_parent_id')
        ->with('childrenRecursive')
        ->get();

        return view('productionarea.production-area', [
            'tree' => $structure_tree,
        ]);
    }

    public function productionAreaDetails ($id)
    {

        $workcenter = WorkcenterStructure::find($id);

        if ($workcenter->structure_type == 'WC') {
            $typename = 'Workcenter';
        } else {
            return response()->json([
                'success' => false,
                'message' => "Only Workcenter are allowed"
            ], 404);
        }

        $array[0] = 'Orders list to ' . $typename . ': ' . $workcenter->structure_code . ' - ' .  $workcenter->structure_name;

        
        $array[1] = $workcenter->structure_contract;
        $array[2] = $workcenter->structure_code;
        $array[3] = $workcenter->id;

        return $array;
    }

    public function getWorkcenterShopOrdersList(string $workcenterCode, string $contract = 'BTP' )
    {

        try {
            //GOFO - 22/05/2025
            //execute the querys
            $shoporders = $this->oracleService->getWorkcenterShopOrdersList($workcenterCode, $contract);             
            $Appshoporders = $this->getWorkcenterShopOrderListLocal($workcenterCode, $contract);

            // make the collections
            $oracleOrders = collect($shoporders); 
            $localOrders = collect($Appshoporders); 
          
            // get the op_id from the local orders
            $localOpIds = $localOrders->pluck('op_id')->map(fn($id) => (int) $id)->unique();


            // filter the oracle orders by the op_id from the local orders
            $filteredOracle = $oracleOrders->whereIn('OP_ID', $localOpIds)->values();

            // see how many orders left from the 11 
            $left = 11 - count($filteredOracle);

            // get from oracle orders the left orders
            $ordersleftOracle = $oracleOrders->reject(function ($item) use ($filteredOracle) {
                return $filteredOracle->contains(fn($f) => (int) $f['OP_ID'] === (int) $item['OP_ID']);
            })->take($left);

            // return all merged orders
            $finalshoporders = $filteredOracle->merge($ordersleftOracle)->take(11);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        return view('productionarea.production-area-shoporder', [
            'shoporders' => $finalshoporders,
        ]);

    }

    public function getWorkcenterShopOrderListLocal (string $workcenterCode, string $contract = 'BTP' )
    {
        try {
            
            // get the workcenter id with the code
            $workcenter = DB::table('workcenter_structures')
                ->where('structure_code', $workcenterCode)
                ->where('structure_contract', $contract)
                ->first();

            if (!$workcenter) {
                return response()->json(['error' => 'Workcenter não encontrado.'], 404);
            }

            // get the shop orders with the workcenter id
            $shoporders = DB::table('shop_orders')
                ->where('workcenter_id', $workcenter->id)
                ->get();


            return $shoporders;


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

    }

    public function productionAreaWorkcenterShopOrder(string $workcenterCode, string $operationId)
    {
        $workcenter = WorkcenterStructure::findOrFail($workcenterCode);

        if ($workcenter->structure_type != 'WC') {
            return response()->json([
                'success' => false,
                'message' => "Only Workcenter are allowed"
            ], 404);
        }

        return route('production-area.productionAreaWorkcenterShopOrderDetails',[
            'id' => $workcenterCode,
            'operationId' => $operationId
        ]);

    }

    public function productionAreaWorkcenterShopOrderDetails(OracleService $oracleService, string $workcenterCode, string $operationId)
    {

        $workcenter = WorkcenterStructure::findOrFail($workcenterCode);

        if ($workcenter->structure_type != 'WC') {
            return response()->json([
                'success' => false,
                'message' => "Only Workcenter are allowed"
            ], 404);
        }

        $LocalShopOrder = ShopOrder::where('op_id', $operationId)
            ->where('workcenter_id', $workcenter->id)
            ->where('state', 'in_production')
            ->first();

        try {
            $shoporder = $this->oracleService->getWorkcenterShopOrdersPartNo($operationId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        if (count($shoporder) <= 0)
        {
            return response()->json([
                'success' => false,
                'message' => "PartNo not found"
            ], 404);
        }

         $characteristic_validation = DB::table('workcenter_template as wt')
        ->join('characteristics as c', 'wt.characteristic_id', '=', 'c.id')
        ->select(
            'wt.characteristic_id',
            'c.code as characteristic_code',
            'c.description as characteristic_description',
            'c.datetype',
            'c.uom',
        )
        ->where('c.type', 'validation')
        ->where('wt.workcenter_structure_id',  $workcenter->id)
        ->whereNull('wt.deleted_at')
        ->get();

        $path_wi = DB::table('workcenter_files')
        ->where('workcenter_structure_id', $workcenter->id)
        ->select('path')
        ->first();

        $partnocomponent = $this->oracleService->getShopOrderPartNo(
            $shoporder[0]['ORDER_NO'],
            $shoporder[0]['RELEASE_NO'],
            $shoporder[0]['SEQUENCE_NO'],
            $workcenter->structure_contract
        );


        return view('productionarea.production-area-workcenter-shoporder', [
            'workcenter' => $workcenter,
            'shoporder' => $shoporder[0],
            'localShopOrder' => $LocalShopOrder,
            'shop_order_description' => $shoporder[0]['ORDER_NO'] . ' - ' . $shoporder[0]['RELEASE_NO'] . ' - '. $shoporder[0]['SEQUENCE_NO'],
            'characteristic_validation' => $characteristic_validation,
            'path_wi' => $path_wi ?? null,
            'partnocomponent' => $partnocomponent,
            'actionCancel' => route('production-area.index'),
        ]);

    }

    public function productionAreaWorkcenterFindShopOrder (Request $request) {

        $order_no = $request->input('order_no');
        $release_no = $request->input('release_no');
        $sequence_no = $request->input('sequence_no');
        $workcenter_id = $request->input('workcenter_structure_id');

        $workcenter = WorkcenterStructure::findOrFail($workcenter_id);
        $structure_code = $workcenter->structure_code;

        
        $workcenter_array=$workcenter->getSiblingOrCousinCodes();
        $workcenter_array[] = $structure_code;

        $workcenter_array_code = "'" . implode("','", $workcenter_array) . "'";
        
        try {
            $result = $this->oracleService->findShopOrder($order_no, $release_no, $sequence_no, $workcenter_array_code);

            if (!empty($result) && isset($result[0]['WORK_CENTER_NO'])) {
                if ($result[0]['WORK_CENTER_NO'] != $structure_code) {
                    return response()->json([
                        'success' => true,
                        'message' => "Order related to Workcenter: " . $result[0]['WORK_CENTER_NO'],
                        'data' => $result,
                        'workcenter_code' => $result[0]['WORK_CENTER_NO'],
                        'status' => 2,
                    ], 200);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'Order was successfully found',
                        'data' => $result,
                        'workcenter_code' => $structure_code,
                        'status' => 1,
                    ], 200);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Shop order not found.',
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }

    }

}
