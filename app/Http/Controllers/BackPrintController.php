<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BackPrintController extends Controller
{
    public function index()
    {

        return view('backprint.brwBackPrint', []);
    }

    public function list(Request $request)
    {
        $pageNumber = ($request->start / $request->length) + 1;
        $pageLength = $request->length;
        $skip = ($pageNumber - 1) * $pageLength;

        $orderColumnIndex = $request->order[0]['column'] ?? 0;
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $initialLoad = $request->initialLoad ?? false;
        $search = $request->search['value'] ?? '';

        if ($initialLoad && empty($search)) {
            return response()->json([
                'draw' => $request->draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
            ], 200);
        }

        $query = DB::table('laboratory.specifications')
        ->select(
            'specification_id',
            'specification_name',
            'part_number',
            'bckp_text_to_print',
            'bckp_num_vertical_lines',
            'bckp_spacing_length',
            'bckp_spacing_cross',
            'bckp_date',
            'bckp_dpi',
            'cut_edges',
            'has_backprint'
        )
        ->where('specification_status', 'Active')
        ->where('type_int_ext', 'Internal')
        ->where(function ($q) {
            $q->where('part_number', 'like', 'P7%')
              ->where('part_number', 'like', '%'); 
        });

        $search = $request->search['value'] ?? '';
        if ($search != "") {
            $query = $query->where(function ($query) use ($search) {
                $query->where('part_number', 'like', "%$search%")
                      ->orWhere('specification_name', 'like', "%$search%")
                      ->orWhere('cut_edges', 'like', "%$search%")
                      ->orWhere('has_backprint', 'like', "%$search%")
                      ->orWhere('bckp_text_to_print', 'like', "%$search%")
                      ->orWhere('bckp_num_vertical_lines', 'like', "%$search%")
                      ->orWhere('bckp_spacing_length', 'like', "%$search%")
                      ->orWhere('bckp_spacing_cross', 'like', "%$search%")
                      ->orWhere('bckp_date', 'like', "%$search%")
                      ->orWhere('bckp_dpi', 'like', "%$search%");
            });
        }

        $orderByName = 'part_number'; 
        switch ($orderColumnIndex) {
            case 0:
                $orderByName = 'part_number';
                break;
            case 1:
                $orderByName = 'specification_name';
                break;
            case 2:
                $orderByName = 'cut_edges';
                break;
            case 3:
                $orderByName = 'has_backprint';
                break;
            case 4:
                $orderByName = 'bckp_text_to_print';
                break;
            case 5:
                $orderByName = 'bckp_num_vertical_lines';
                break;
            case 6:
                $orderByName = 'bckp_spacing_length';
                break;
            case 7:
                $orderByName = 'bckp_spacing_cross';
                break;
            case 8:
                $orderByName = 'bckp_date';
                break;
            case 9:
                $orderByName = 'bckp_dpi';
                break;
        }

        $query = $query->orderBy($orderByName, $orderBy);

        $recordsTotal = $query->count();

        $data = $query->skip($skip)->take($pageLength)->get();

        $data = array(
            'draw' => $request->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal, 
            'data' => $data,
        );
    
        return response()->json($data, 200);
    }

    public function updateDpi(Request $request)
    {
        $request->validate([
            'specification_id' => 'required|integer',
            'bckp_dpi' => 'required|numeric',
        ]);
    
        try {
            DB::table(DB::raw('`laboratory`.`specifications`'))
                ->where('specification_id', $request->specification_id)
                ->update(['bckp_dpi' => $request->bckp_dpi]);
    
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
}
