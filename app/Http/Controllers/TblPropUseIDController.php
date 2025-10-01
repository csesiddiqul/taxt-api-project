<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Http\Resources\TblPropUseResource;
use App\Models\TblPropUseID;
use App\Http\Requests\StoreTblPropUseIDRequest;
use App\Http\Requests\UpdateTblPropUseIDRequest;
use Faker\Provider\Base;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TblPropUseIDController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $tblPropUseID = TblPropUseID::when($request->PropUseID, function ($q, $PropUseID) {
                $q->where('PropUseID', $PropUseID);
            })->when($request->search, function ($q, $search) {
                $q->where('PropUseID', 'LIKE', '%' . $search . '%');
                $q->orWhere('PropertyUse', 'LIKE', '%' . $search . '%');
            })->paginate($request->per_page ?? 15);

            return TblPropUseResource::collection($tblPropUseID);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('data Fetch failed.', $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->sendError('data Fetch failed.', $exception->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTblPropUseIDRequest $request)
    {
        try {
            DB::beginTransaction();
            $tblPropUseType = TblPropUseID::create([
                'PropUseID' => $request->PropUseID,
                'PropertyUse' => $request->PropertyUse,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data created successfully.',
                new TblPropUseResource($tblPropUseType)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(
                message: 'data creation failed !',
                errors: $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($tblPropUseID)
    {
        try {
            $tblPropUseID = TblPropUseID::where('PropUseID', '=', $tblPropUseID)->firstOrFail();
            return new TblPropUseResource($tblPropUseID);
        } catch (ModelNotFoundException $e) {
            return $this->sendError(
                message: 'data not found!',
                errors: 'No data found with the provided ID.',
                status: 404
            );
        } catch (\Exception $e) {
            return $this->sendError(
                message: 'data show failed!',
                errors: $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TblPropUseID $tblPropUseID)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTblPropUseIDRequest $request, $tblPropUseID)
    {
        $tblPropType = TblPropUseID::where('PropUseID', '=', $tblPropUseID)->firstOrFail();
        try {
            DB::beginTransaction();
            $tblPropType->update([
                'PropUseID' => $request->PropUseID,
                'PropertyUse' => $request->PropertyUse,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data updated successfully.',
                new TblPropUseResource($tblPropType)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(
                message: 'data update failed!',
                errors: $e->getMessage(),
                status: 500
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tblPropUseID)
    {
        $tblPropUseID = TblPropUseID::where('PropUseID', '=', $tblPropUseID)->firstOrFail();
        try {
            DB::beginTransaction();
            $tblPropUseID->delete();
            DB::commit();
            return $this->sendResponse(
                'data deleted successfully.'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(
                message: 'data deletion failed!',
                errors: $e->getMessage(),
                status: 500
            );
        }
    }
}
