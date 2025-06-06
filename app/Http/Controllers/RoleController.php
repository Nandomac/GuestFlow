<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();

        return view('role.brwRole', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role = null;
        return view('role.frmRole', [
            'role' => $role,
            'action' => route('role.store'),
            'actionCancel' => route('role.index'),
            'method' => 'POST',
            'title' => 'New Role'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $valid = validator($request->only('name'), [
            'name' => ['required', 'string', 'max:255']
        ]);

        if ($valid->fails()) {
            return back()
                ->withErrors($valid->errors())
                ->withInput();
        }

        $roles = new Role();
        $roles->name = $request->name;
        $roles->guard_name = 'web';
        $roles->save();

        return redirect(route("role.index"))->with('success', 'New Role Created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $role = Role::find($id);

        return view('role.frmRole', [
            'role' => $role,
            'indexName' => 'Perfis',
            'action' => route('role.update', ['id' => $role->id]),
            'actionCancel' => route('role.index'),
            'method' => 'PUT',
            'title' => 'Editanto Perfil - ' . $role->id
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $valid = validator($request->only('name'), [
            'name' => ['required', 'string', 'max:255']
        ]);

        if ($valid->fails()) {
            return back()
                ->withErrors($valid->errors())
                ->withInput();
        }

        $role = Role::find($id);
        $role->name = $request->name;
        $role->save();

        return redirect(route("role.index"))->with('success', 'New Role Created');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::find($id);
        $role->delete();

        return redirect(route("role.index"))->with('success', 'New Role Created');
    }
}
