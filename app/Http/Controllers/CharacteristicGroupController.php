<?php

namespace App\Http\Controllers;

use App\Models\CharacteristicGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CharacteristicGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $characteristicGroups = CharacteristicGroup::paginate(20);

        return view('characteristicsgroup.brwCharacteristicGroup', [
            'characteristicGroups' => $characteristicGroups,
        ]);
    }

    public function list (Request $request) {

        $pageNumber = ( $request->start / $request->length )+1;
        $pageLength = $request->length;
        $skip       = ($pageNumber-1) * $pageLength;

        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = DB::table('characteristic_groups')->select('*');
        $query = $query->where('deleted_at', null);

        // Search
        $search_arr = $request->search;
        $search = $search_arr['value'];

        $query = $query->where(function($query) use ($search){
            if($search != "") {
                $query->orWhere('id', 'like', "%".$search."%");
                $query->orWhere('name', 'like', "%".$search."%");
            }
        });

        $orderByName = 'name';
        switch($orderColumnIndex){
            case '0':
                $orderByName = 'id';
                break;
            case '1':
                $orderByName = 'name';
                break;
        }

        $query = $query->orderBy($orderByName, $orderBy);
        $recordsFiltered = $recordsTotal = $query->count();
        $data = $query->skip($skip)->take($pageLength)->get();

        $data = $query->skip($skip)->take($pageLength)->get();

        $data = array(
            'draw' => $request->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        );

        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $characteristicGroup = null;
        return view('characteristicsgroup.frmCharacteristicGroup', [
            'characteristicGroup' => $characteristicGroup,
            'action' => route('characteristic-group.store'),
            'actionCancel' => route('characteristic-group.index'),
            'method' => 'POST',
            'title' => 'New Characteristic'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $valid = validator($request->only(['name', 'group_order']), [
            'name' => ['required', 'string', 'max:255'],
            'group_order' => ['required', 'integer'],
        ]);

        if ($valid->fails()) {
            return back()
                ->withErrors($valid->errors())
                ->withInput();
        }

        $characteristicGroup = new CharacteristicGroup();
        $characteristicGroup->name = $request->name;
        $characteristicGroup->group_order = $request->group_order;
        $characteristicGroup->user_create_id = Auth::user()->id;
        $characteristicGroup -> save();

        return redirect(route("characteristic-group.index"))->with('success', 'Characteristic Group Saved');
    }

    /**
     * Display the specified resource.
     */
    public function show(CharacteristicGroup $characteristicGroup)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $characteristicGroup = CharacteristicGroup::find($id);

        return view('characteristicsgroup.frmCharacteristicGroup', [
            'characteristicGroup' => $characteristicGroup,
            'action' => route('characteristic-group.update', $characteristicGroup->id),
            'actionCancel' => route('characteristic-group.index'),
            'method' => 'PUT',
            'title' => 'Edit Characteristic Group'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $valid = validator($request->only(['name', 'group_order']), [
            'name' => ['required', 'string', 'max:255'],
            'group_order' => ['required', 'integer'],
        ]);

        if ($valid->fails()) {
            return back()
                ->withErrors($valid->errors())
                ->withInput();
        }

        $characteristicGroup = CharacteristicGroup::find($id);
        $characteristicGroup->name = $request->name;
        $characteristicGroup->group_order = $request->group_order;

        $characteristicGroup->save();
        return redirect(route("characteristic-group.index"))->with('success', 'Characteristic Group Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $characteristicGroup = CharacteristicGroup::find($id);
            $characteristicGroup->delete();

            return response()->json([
                'status' => true,
                'message' => 'Characteristic Group removed successfully.'
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing Characteristic Group. Details: ' . $e->getMessage()
            ], 404);
        }
    }


    public function searchGroupCharacteristics($searchGroup)
    {
        $characteristics_groups = CharacteristicGroup::query()
            ->when($searchGroup, function ($query) use ($searchGroup) {
                $query->where('name', 'like', '%' . $searchGroup . '%');
            })
            ->get()
            ->map(function ($item) {
                return [
                    'ID' => $item->id,
                    'DESCRIPTION' => $item->name,
                ];
            });

        return response()->json($characteristics_groups);
    }

}
