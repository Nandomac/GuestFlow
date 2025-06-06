<?php

namespace App\Http\Controllers\Api;

use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\BaseApiController as BaseApiController;

class UserAPIController extends BaseApiController
{
    public function index()
    {
        $user = User::withoutTrashed()->get();

        return $this->sendResponse(UserResource::collection($user),'User retrieved successfully.', 'index');
    }

    public function store(Request $request)
    {
        log::info('Creating user', ['request' => $request->all()]);
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255',
            'email' => 'required|email||unique:users,email',
            'password' => 'required|string|min:6',
            'employee_id' => 'required|unique:users,employee_id'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),'store');
        }

        $user = User::factory()->create([
            'name' => $input['name'],
            'login' => $input['login'],
            'email' => $input['email'],
            'employee_id' => $input['employee_id'],
            'password' => bcrypt($input['password']),
        ]);
        $user->save();

        return $this->sendResponse(new UserResource($user),'User created successfully.', 'store');
    }

    public function show($id)
    {
        log::info('Retrieving user', ['user_id' => $id]);
        $user = User::withTrashed()->where('employee_id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.','', 'show');
        }

        return $this->sendResponse(new UserResource($user), 'User retrieved successfully.', 'show');
    }

    public function update(Request $request, $id)
    {

        log::info('Updating user', ['user_id' => $id, 'request' => $request->all()]);
        $delete_at = null;

        $user = User::withTrashed()->where('employee_id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.', '', 'update');
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'password' => 'required|string|min:6',
            'employee_id' => 'required|unique:users,employee_id,' . $user->id
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),'update');
        }

        if (isset($input['name'])) {
            $user->name = $input['name'];
        }
        if (isset($input['email'])) {
            $user->email = $input['email'];
        }
        if (isset($input['password'])) {
            $user->password = bcrypt($input['password']);
        }
        if (isset($input['employee_id'])) {
            $user->employee_id = $input['employee_id'];
        }

        if (isset($input['deleted_at'])) {
            $user->deleted_at = isset($input['deleted_at']);
        } else {
            $user->deleted_at = $delete_at;
        }

        $user->save();

        return $this->sendResponse(new UserResource($user), 'User updated successfully.', 'update');
    }

    public function destroy($id)
    {
        Log::info('Deleting user', ['user_id' => $id]);
        $user = User::withTrashed()->where('id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.', '', 'destroy');
        }
        $user->delete();
        return $this->sendResponse([], 'User deleted successfully.', 'destroy');
    }

    public function updateUserPassword(Request $request, $id)
    {
        log::info('Updating user password', ['user_id' => $id, 'request' => $request->all()]);

        $user = User::withTrashed()->where('employee_id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.', '', 'update_user_password');
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'password' => 'required|string',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),'update_user_password');
        }

        $user->password = bcrypt($input['password']);
        $user->save();

        return $this->sendResponse(new UserResource($user), 'User password updated successfully.', 'update_user_password');
    }

    public function getUserRoles($id)
    {
        log::info('Getting user roles', ['user_id' => $id]);

        $user = User::withTrashed()->where('employee_id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.', '', 'getUserRoles');
        }

        $roles = $user->getRoleNames();

        return $this->sendResponse($roles, 'User roles retrieved successfully.', 'getUserRoles');
    }

    public function updateUserRoles(Request $request, $id)
    {

        log::info('Updating user roles', ['user_id' => $id, 'request' => $request->all()]);

        $user = User::withTrashed()->where('employee_id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.', '', 'update');
        }

        $input = $request->all();
        $validator = Validator::make($input, [
            'roles' => 'required|array',
            'roles.*' => 'string'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors(),'update');
        }

        foreach ($input['roles'] as $role) {
            Log::info('Assigning role to user', ['user_id' => $user->id, 'role' => $role]);
            $user->assignRole($role);
            $user->save();
        }

        return $this->sendResponse(new UserResource($user), 'User Roles updated successfully.', 'update');
    }

    public function removeUserRoles($id, $role)
    {
        log::info('Removing role from user', ['user_id' => $id, 'role' => $role]);

        $user = User::withTrashed()->where('employee_id', '=', $id)->first();
        if (is_null($user)) {
            return $this->sendError('User not found.', '', 'removeUserRoles');
        }

        if ($user->hasRole($role)) {
            $user->removeRole($role);
            $user->save();
            //return $this->sendError('User does not have this role.', '', 'removeUserRoles');
        }

        return $this->sendResponse(new UserResource($user), 'User Role removed successfully.', 'removeUserRoles');
    }
}
