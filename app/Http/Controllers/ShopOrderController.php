<?php

namespace App\Http\Controllers;

use App\Models\ShopOrder;
use App\Models\WorkcenterDowntime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\OracleService;
use App\Models\WorkcenterStructure;

class ShopOrderController extends Controller
{

    protected OracleService $oracleService;

    public function __construct(OracleService $oracleService)
    {
        $this->oracleService = $oracleService;
    }
    /**
     * Display a listing of the resource.
     */
    public function startProduction(Request $request)
    {
        $validated = $request->validate([
            'op_id' => 'required|string',
            'order_no' => 'required|string',
            'release_no' => 'required|string',
            'sequence_no' => 'required|string',
            'workcenter_structure_id' => 'required|integer',
        ]);

        $shopOrder = ShopOrder::where('op_id', $validated['op_id'])
            ->where('workcenter_id', $validated['workcenter_structure_id'])
            ->first();

        $workcenter = WorkcenterStructure::find($validated['workcenter_structure_id']);

        $startProductionIFS = $this->oracleService->startProductionIFS(
            $workcenter->structure_code,
            $workcenter->structure_contract,
            $validated['op_id'],
        );

        //call IFS function to start production
        if($startProductionIFS['message'] == 'ok') {

            if ($shopOrder) {
                // if exist, update the existing record - MySQL
                $shopOrder->update([
                    'order_no' => $validated['order_no'],
                    'release_no' => $validated['release_no'],
                    'sequence_no' => $validated['sequence_no'],
                    'user_id' => Auth::id(),
                    'state' => 'in_production',
                ]);
            } else {
                // if not exist, create a new record - MySQL
                $shopOrder = ShopOrder::create([
                    'op_id' => $validated['op_id'],
                    'order_no' => $validated['order_no'],
                    'release_no' => $validated['release_no'],
                    'sequence_no' => $validated['sequence_no'],
                    'workcenter_id' => $validated['workcenter_structure_id'],
                    'user_id' => Auth::id(),
                    'state' => 'in_production',
                    'created_at' => now(),
                ]);
            }
        // else if the IFS function returns an error    
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error starting production: ' . $startProductionIFS['message'],
            ], 500);
        }  

        return response()->json([
            'success' => true,
            'message' => 'Production started with success.',
            'data' => $shopOrder,
        ]);
    }

    public function finishProduction (Request $request) {
        $validated = $request->validate([
            'op_id' => 'required|string',
            'order_no' => 'required|string',
            'release_no' => 'required|string',
            'sequence_no' => 'required|string',
            'workcenter_structure_id' => 'required|integer',
            'operation_no' => 'required|string',
        ]);

        $shopOrder = ShopOrder::where('op_id', $validated['op_id'])
            ->where('workcenter_id', $validated['workcenter_structure_id'])
            ->first();

        $workcenter = WorkcenterStructure::find($validated['workcenter_structure_id']);


        
        $finishProductionIFS = $this->oracleService->finishProductionIFS(
            $validated['order_no'],
            $validated['release_no'],
            $validated['sequence_no'],
            $workcenter->structure_code,
            $workcenter->structure_contract,
            $validated['operation_no'],
        );
        // finish production in IFS
        if ($finishProductionIFS ['message'] == "ok finish") {
            // if the shop order already exists, update it
            if ($shopOrder) {      
                $shopOrder->update([
                    'state' => 'finished',
                ]);
            // if not exist, create a new record    
            } else {
                $shopOrder = ShopOrder::create([
                    'op_id' => $validated['op_id'],
                    'order_no' => $validated['order_no'],
                    'release_no' => $validated['release_no'],
                    'sequence_no' => $validated['sequence_no'],
                    'workcenter_id' => $validated['workcenter_structure_id'],
                    'user_id' => Auth::id(),
                    'state' => 'finished',
                    'created_at' => now(),
                ]);
            }

        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error finishing production: ' . $finishProductionIFS['message'],
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Production finished successfully.',
            'data' => $shopOrder,
        ]);
    }
    
    public function getDowntimeReasons(Request $request) {
       
        $request->validate([
            'workcenter_id' => 'required|integer',
        ]);

        $downtime = WorkcenterDowntime::where('workcenter_structure_id', $request->workcenter_id)->get();

        $downtimeCauses = $downtime->pluck('downtime_cause_id');

        return response()->json([
            'success' => true,
            'data' => $downtimeCauses
        ]);
    }

    public function recordDowntime(Request $request) {
        $validated = $request->validate([
            'workcenter_structure_id' => 'required|integer',
            'reason' => 'required|string',
            'comment' => 'nullable|string',
         ]);


        $workcenterStructure = WorkcenterStructure::find($validated['workcenter_structure_id']);

        $startDowntimeIFS = $this->oracleService->startDowntimeIFS(
            $workcenterStructure->structure_contract,
            $workcenterStructure->structure_code,
            $request['reason'],
            $request['comment'] ?? null
        );


        return response()->json([
            'success' => true,
            'message' => 'Downtime recorded successfully.',
        ]);
    }
    
    public function getFinishDowntime (Request $request) {
        $workcenterStructure = WorkcenterStructure::find($request->workcenter_structure_id);

        $finishDowntimeIFS = $this->oracleService->getfinishDowntimeIFS(
            $workcenterStructure->structure_contract,
            $workcenterStructure->structure_code
        );

        return response()->json([
            'success' => true,
            'data' => $finishDowntimeIFS,
        ]);
    }

    public function recordFinishDowntime (Request $request) {
        $validated = $request->validate([
            'workcenter_structure_id' => 'required|integer',
            'comment' => 'nullable|string',
        ]);

        $workcenterStructure = WorkcenterStructure::find($validated['workcenter_structure_id']);

        $finishDowntimeIFS = $this->oracleService->finishDowntimeIFS(
            $workcenterStructure->structure_contract,
            $workcenterStructure->structure_code,
            $validated['comment'] ?? null
        );

        return response()->json([
            'success' => true,
            'message' => 'Downtime finished successfully.',
        ]);
    }

    public function getPnoComponentHistory(Request $request) {

        $order_no = $request->input('orderNo');
        $release_no = $request->input('releaseNo');
        $sequence_no = $request->input('sequenceNo');

        $workcenter_Id = $request->input('workcenterId');
        $workcenter = WorkcenterStructure::findOrFail($workcenter_Id);

        $part_no = $request->input('partNo');
        $line_no = $request->input('lineId');

        $getPnoComponentHistory = $this->oracleService->getPnoComponentHistory(
            $order_no,
            $release_no,
            $sequence_no,
            $workcenter->structure_contract,
            $part_no,
            $line_no
        );
        

        if (empty($getPnoComponentHistory)) {
            return response()->json([
                'success' => false,
                'message' => 'No component history found for the given part number.',
            ], 404);
        } 
        return response()->json([
            'success' => true,
            'data' => $getPnoComponentHistory,
        ]);
    }

    public function issueMaterial(Request $request) { 

        $workcenter_Id = $request->input('workcenter_id');
        $workcenter = WorkcenterStructure::findOrFail($workcenter_Id);

        //get the LineNo
        $getLineItemNo = $this->oracleService->getLineItemNo(
            $request->input('order_no'),
            $request->input('release_no'),
            $request->input('sequence_no'),
            $workcenter->structure_contract,
            $request->input('partNo'),
        );


        // get data values to issue material
        $getDataValues = $this->oracleService->getDataValues(
            $request->input('order_no'),
            $request->input('release_no'),
            $request->input('sequence_no'),
            $getLineItemNo[0]['LINE_ITEM_NO'],
            $request->input('lot'),
            $request->input('partNo'),
            $workcenter->structure_contract,
        );

        // manual issue 
        $manual_issue = $this->oracleService->manualIssue(
            $request->input('order_no'),                                
            $request->input('release_no'),                               
            $request->input('sequence_no'),                             
            $getLineItemNo[0]['LINE_ITEM_NO'],                           
            $workcenter->structure_contract,                             
            $request->input('partNo'),                                   
            $getDataValues[0]['LOCATION_NO'],                            
            $request->input('lot'),                                      
            $getDataValues[0]['SERIAL_NO'] ?? '*',                              
            $getDataValues[0]['ENG_CHG_LEVEL'],                          
            $getDataValues[0]['WAIV_DEV_REJ_NO'],                      
            (int) $getDataValues[0]['ACTIVITY_SEQ'],                     
            (int) $getDataValues[0]['HANDLING_UNIT_ID'],                 
            (float) $getDataValues[0]['CATCH_QTY_ONHAND'],              
            (float) $request->input('quantity'),                       
            (float) $getDataValues[0]['RESERVED_INPUT_QTY'],            
            (string) ($getDataValues[0]['RESERVED_INPUT_UOM'] ?? ''),                     
            (string) ($getDataValues[0]['RESERVED_INPUT_VAR_VALUES'] ?? ''),             
            (int) ($getDataValues[0]['PART_TRACKING_SESSION_ID'] ?? 0)     
        );      
    }

    public function getScrapCauses(Request $request)
    {

        $scrapCauses = $this->oracleService->getScrapCauses(
            $request->search,
        );

        if (empty($scrapCauses)) {
            return response()->json([
                'success' => false,
                'message' => 'No scrapping causes found.',
            ], 404);
        }

        $data = array_map(function ($item) {
            return [
                'ID' => $item['ID'], 
                'DESCRIPTION' => $item['DESCRIPTION'], 
            ];
        }, $scrapCauses);

        return response()->json($data);
    }

    public function reportScrapOperation(Request $request) {
       
        $reportScrapOperation = $this->oracleService->reportScrapOperation (
            $request->input('order_no'),
            $request->input('release_no'),
            $request->input('sequence_no'),
            $request->input('operation_no'),
            $request->input('quantity'),
            $request->input('scrap_cause_id'),
            $request->input('notes')
        );
        if ($reportScrapOperation['message'] == 'ok') {
            return response()->json([
                'success' => true,
                'message' => 'Scrap operation reported successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error reporting scrap operation: ' . $reportScrapOperation['message'],
            ], 500);
        }
    }
    
    public function reportScrapComponent (Request $request) {
        
        $reportScrapComponent = $this->oracleService->reportScrapComponent (
            $request->input('material_history_id'),
            $request->input('operation_no'),
            $request->input('quantity'),
            $request->input('cause_id'),
            $request->input('notes')
        );
        if ($reportScrapComponent['message'] == 'ok') {
            return response()->json([
                'success' => true,
                'message' => 'Scrap component reported successfully.',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Error reporting scrap component: ' . $reportScrapComponent['message'],
            ], 500);
        }

    }
}
