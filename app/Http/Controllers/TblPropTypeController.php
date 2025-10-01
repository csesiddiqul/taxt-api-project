<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Models\TblPropType;
use App\Http\Requests\StoreTblPropTypeRequest;
use App\Http\Requests\UpdateTblPropTypeRequest;
use App\Http\Resources\TblPropTypeResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TblPropTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $tblPropType = TblPropType::when($request->PropTypeID, function ($q, $PropTypeID) {
                $q->where('PropTypeID', $PropTypeID);
            })->when($request->search, function ($q, $search) {
                $q->where('PropTypeID', 'LIKE', '%' . $search . '%');
                $q->orWhere('PropertyType', 'LIKE', '%' . $search . '%');
            })->paginate($request->per_page ?? 15);

            return TblPropTypeResource::collection($tblPropType);
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
    public function store(StoreTblPropTypeRequest $request)
    {

        try {
            DB::beginTransaction();
            $tblPropType = TblPropType::create([
                'PropTypeID' => $request->PropTypeID,
                'PropertyType' => $request->PropertyType,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data created successfully.',
                new TblPropTypeResource($tblPropType)
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
    public function show($tblPropType)
    {
        try {
            $tblPropType = TblPropType::where('PropTypeID', '=', $tblPropType)->firstOrFail();
            return new TblPropTypeResource($tblPropType);
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
    public function edit(TblPropType $tblPropType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTblPropTypeRequest $request,  $tblPropType)
    {
        $tblPropType = TblPropType::where('PropTypeID', '=', $tblPropType)->firstOrFail();
        try {
            DB::beginTransaction();
            $tblPropType->update([
                'PropTypeID' => $request->PropTypeID,
                'PropertyType' => $request->PropertyType,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data updated successfully.',
                new TblPropTypeResource($tblPropType)
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
    public function destroy($tblPropType)
    {
        $tblPropType = TblPropType::where('PropTypeID', '=', $tblPropType)->firstOrFail();
        try {
            DB::beginTransaction();
            $tblPropType->delete();
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
