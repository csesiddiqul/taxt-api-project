<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Http\Resources\Auth\RoleResource;
use App\Http\Resources\Auth\RolesResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission as ModelsPermission;
use App\Http\Resources\Auth\PermissionResource;
use Illuminate\Support\Facades\Validator;

class CustomizeRoleController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $roles = Role::select('id', 'name', 'created_at')
                ->with('permissions:id,name')
                ->when($request->id, fn($q, $id) => $q->where('id', $id))
                ->when(
                    $request->search,
                    fn($q, $search) =>
                    $q->where('name', 'LIKE', "%{$search}%")
                )
                ->orderBy('created_at', 'ASC')
                ->paginate($request->per_page ?? 15);


            return RolesResource::collection($roles);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError(
                'Role retrieval failed.',
                $exception->getMessage(),
                404
            );
        } catch (\Exception $exception) {
            // Return a general error response for other exceptions
            return $this->sendError('Role retrieval failed.', $exception->getMessage());
        }
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }


    public function storePermission(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        $permission = Permission::create([
            'name' => $request->name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully.',
            'data' => $permission,
        ], 201);
    }



    public function store(Request $request)
    {
        try {
            // 🔹 Validation
            $validator = Validator::make($request->all(), [
                'name'        => 'required|string|max:255|unique:roles,name',
                'permissions' => 'nullable|array',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors(),
                ], 422);
            }

            // 🔹 Role তৈরি
            $role = Role::create([
                'name'       => $request->name,
                'guard_name' => 'web',
            ]);

            // 🔹 Permission assign করা (যদি থাকে)
            if (!empty($request->permissions)) {
                $permissions = ModelsPermission::whereIn('id', $request->permissions)->get();
                $role->syncPermissions($permissions);
            }

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully.',
                'data'    => $role->load('permissions'),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Store a newly created resource in storage.
     */



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $role = Role::findOrFail($id);

            return $this->sendResponse(
                __('Role show successfully.'),
                new RoleResource($role)
            );
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('role  Show failed.', $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->sendError('role Show failed.', $exception->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */


    public function permissionsAll(Request $request)
    {
        try {
            $permissions = ModelsPermission::where('guard_name', 'web')
                ->orderBy('created_at', 'ASC')
                ->paginate($request->per_page ?? 15);

            // Return the resource collection for permissions
            return PermissionResource::collection($permissions);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('Data retrieval failed. No permissions found.', $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->sendError('Data retrieval failed.', $exception->getMessage());
        }
    }


    public function update(Request $request, string $id)
    {
        try {
            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|unique:roles,name,' . $id,
                'permissions' => 'required|array|min:1',
                'permissions.*' => 'exists:permissions,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // ✅ Role খোঁজা
            $role = Role::findOrFail($id);

            // ✅ Role update
            $role->update([
                'name' => $request->name,
                'guard_name' => 'web',
            ]);

            // ✅ Permissions sync
            $role->permissions()->sync($request->permissions);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully.',
                'data' => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions' => $role->permissions()->get(['id', 'name']),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong while updating the role.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        //
    }
}
