<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Http\Resources\TaxpayerTypeResource;
use App\Models\TaxpayerType;
use App\Http\Requests\StoreTaxpayerTypeRequest;
use App\Http\Requests\UpdateTaxpayerTypeRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxpayerTypeController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $taxpayerType = TaxpayerType::when($request->TaxpayerTypeID, function ($q, $TaxpayerTypeID) {
                $q->where('TaxpayerTypeID', $TaxpayerTypeID);
            })->when($request->search, function ($q, $search) {
                $q->where('TaxpayerTypeID', 'LIKE', '%' . $search . '%');
                $q->orWhere('TaxpayerType', 'LIKE', '%' . $search . '%');
            })->paginate($request->per_page ?? 15);

            return TaxpayerTypeResource::collection($taxpayerType);
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
    public function store(StoreTaxpayerTypeRequest $request)
    {
        try {
            DB::beginTransaction();
            $tblPropUseType = TaxpayerType::create([
                'TaxpayerTypeID' => $request->TaxpayerTypeID,
                'TaxpayerType' => $request->TaxpayerType,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data created successfully.',
                new TaxpayerTypeResource($tblPropUseType)
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
    public function show($taxpayerType)
    {
        try {
            $taxpayerType = TaxpayerType::where('TaxpayerTypeID', '=', $taxpayerType)->firstOrFail();
            return new TaxpayerTypeResource($taxpayerType);
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
    public function edit(TaxpayerType $taxpayerType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxpayerTypeRequest $request,  $taxpayerType)
    {

        $tblPropType = TaxpayerType::where('TaxpayerTypeID', '=', $taxpayerType)->firstOrFail();
        try {
            DB::beginTransaction();
            $tblPropType->update([
                'TaxpayerTypeID' => $request->TaxpayerTypeID,
                'TaxpayerType' => $request->TaxpayerType,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data updated successfully.',
                new TaxpayerTypeResource($tblPropType)
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
    public function destroy($taxpayerType)
    {
        $tblPropUseID = TaxpayerType::where('TaxpayerTypeID', '=', $taxpayerType)->firstOrFail();
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
