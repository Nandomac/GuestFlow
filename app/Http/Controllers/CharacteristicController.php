<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Characteristic;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CharacteristicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $characteristics = Characteristic::paginate(20);

        return view('characteristics.brwCharacteristic', [
            'characteristics' => $characteristics,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $characteristic = null;
        return view('characteristics.frmCharacteristic', [
            'characteristic' => $characteristic,
            'action' => route('characteristic.store'),
            'actionCancel' => route('characteristic.index'),
            'method' => 'POST',
            'title' => 'New Characteristic'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $valid = validator($request->only(['code', 'description', 'type', 'uom', 'datetype', 'id_bdlab']), [
            'code' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:validation,setup'],
            'uom' => ['nullable', 'string', 'max:50'],
            'datetype' => ['nullable', 'string',],
            'id_bdlab' => ['nullable', 'integer'],
        ]);

        if ($valid->fails()) {
            return back()
                ->withErrors($valid->errors())
                ->withInput();
        }

        $characteristic = new Characteristic();
        $characteristic->code = $request->code;
        $characteristic->description = $request->description;
        $characteristic->type = $request->type;
        $characteristic->uom = $request->uom;
        $characteristic->datetype = $request->datetype;
        $characteristic->id_bdlab = $request->id_bdlab;
        $characteristic->is_active = $request->is_active ?? 0;
        $characteristic->user_create_id = Auth::user()->id;
        $characteristic->save();

        return redirect(route("characteristic.index"))->with('success', 'Characteristic Saved');
    }

    /**
     * Display the specified resource.
     */
    public function show(Characteristic $characteristics)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit( string $id)
    {
        $characteristic = Characteristic::find($id);

        return view('characteristics.frmCharacteristic', [
            'characteristic' => $characteristic,
            'action' => route('characteristic.update', $characteristic->id),
            'actionCancel' => route('characteristic.index'),
            'method' => 'PUT',
            'title' => 'Edit Characteristic'
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $valid = validator($request->only(['code', 'description', 'type', 'uom', 'datetype', 'id_bdlab']), [
            'code' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:validation,setup'],
            'uom' => ['nullable', 'string', 'max:50'],
            'datetype' => ['nullable', 'string',],
            'id_bdlab' => ['nullable', 'integer'],
        ]);

        if ($valid->fails()) {
            return back()
                ->withErrors($valid->errors())
                ->withInput();
        }

        $characteristic = Characteristic::find($id);
        $characteristic->code = $request->code;
        $characteristic->description = $request->description;
        $characteristic->type = $request->type;
        $characteristic->uom = $request->uom;
        $characteristic->datetype = $request->datetype;
        $characteristic->id_bdlab = $request->id_bdlab;
        $characteristic->is_active = $request->is_active ?? 0;
        $characteristic->user_update_id = Auth::user()->id;

        $characteristic->save();
        return redirect(route("characteristic.index"))->with('success', 'Characteristic Updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        try {
            $characteristic = Characteristic::find($id);
            $characteristic->delete();

            return response()->json([
                'status' => true,
                'message' => 'Characteristic removed successfully.'
            ],200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing Characteristic. Details: ' . $e->getMessage()
            ], 404);
        }

    }


    public function list(Request $request) {

        $pageNumber = ( $request->start / $request->length )+1;
        $pageLength = $request->length;
        $skip       = ($pageNumber-1) * $pageLength;

        $orderColumnIndex = $request->order[0]['column'] ?? '0';
        $orderBy = $request->order[0]['dir'] ?? 'desc';

        $query = DB::table('characteristics')->select('*')->whereNull('deleted_at');

        // Search
        $search_arr = $request->search;
        $search = $search_arr['value'];

        $query = $query->where(function($query) use ($search){
            if($search != "") {
                $query->orWhere('code', 'like', "%".$search."%");
                $query->orWhere('description', 'like', "%".$search."%");
                $query->orWhere('uom', 'like', "%".$search."%");
                $query->orWhere('datetype', 'like', "%".$search."%");
                $query->orWhere('id_bdlab', 'like', "%".$search."%");
            }
        });

        $orderByName = 'code';
        switch($orderColumnIndex){
            case '0':
                $orderByName = 'code';
                break;
            case '1':
                $orderByName = 'description';
                break;
            case '2':
                $orderByName = 'uom';
                break;
            case '3':
                $orderByName = 'datetype';
                break;
            case '4':
                $orderByName = 'id_bdlab';
                break;
        }

        $query = $query->orderBy($orderByName, $orderBy);
        $recordsFiltered = $recordsTotal = $query->count();
        $data = $query->skip($skip)->take($pageLength)->get();

        $data = $query->skip($skip)->take($pageLength)->get();

        $data = $data->map(function ($item) {
            $item->active = $item->is_active ? true : false;
            return $item;
        });

        $data = array(
            'draw' => $request->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        );

        return response()->json($data, 200);

    }

}
