<?php

namespace App\Http\Controllers;

use App\Services\OracleService;
use Illuminate\Http\JsonResponse;


class OracleController extends Controller
{
    protected OracleService $oracleService;

    public function __construct(OracleService $oracleService)
    {
        $this->oracleService = $oracleService;
    }

    public function getWorkcenterToSync(): JsonResponse
    {
        try {
            $data = $this->oracleService->getWorkcenterToSync();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getDowntimeCauses(): JsonResponse
    {
        try {
            $data = $this->oracleService->getDowntimeCauses();
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWorkcenterLocations(string $workcenterCode): JsonResponse
    {
        try {
            $data = $this->oracleService->getWorkcenterLocations($workcenterCode);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getInventoryParts(string $partnoId, string $contract = 'BTP'): JsonResponse
    {
        try {
            $data = $this->oracleService->getInventoryParts($partnoId, $contract);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getWorkcenterShopOrdersList(string $workcenterCode, string $contract = 'BTP' ): JsonResponse
    {
        try {
            $data = $this->oracleService->getWorkcenterShopOrdersList($workcenterCode, $contract);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    
}
