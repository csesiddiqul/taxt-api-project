<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Models\TaxRate;
use App\Http\Requests\StoreTaxRateRequest;
use App\Http\Requests\UpdateTaxRateRequest;
use App\Http\Resources\TaxRateResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxRateController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $taxRate = TaxRate::when($request->Id, function ($q, $Id) {
                $q->where('Id', $Id);
            })->when($request->search, function ($q, $search) {
                $q->where('HoldingT', 'LIKE', '%' . $search . '%');
                $q->orWhere('ConservancyT', 'LIKE', '%' . $search . '%');
                $q->orWhere('WaterT', 'LIKE', '%' . $search . '%');
                $q->orWhere('LightT', 'LIKE', '%' . $search . '%');
                $q->orWhere('TotT', 'LIKE', '%' . $search . '%');
            })->paginate($request->per_page ?? 15);

            return TaxRateResource::collection($taxRate);
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
    public function store(StoreTaxRateRequest $request)
    {
        try {
            DB::beginTransaction();
            $tblPropUseType = TaxRate::create([
                'Id' => $request->Id,
                'HoldingT' => $request->HoldingT,
                'ConservancyT' => $request->ConservancyT,
                'WaterT' => $request->WaterT,
                'LightT' => $request->LightT,
                'TotT' =>  $request->HoldingT + $request->ConservancyT + $request->WaterT + $request->LightT,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data created successfully.',
                new TaxRateResource($tblPropUseType)
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
    public function show($taxRate)
    {
        try {
            $taxRate = TaxRate::where('Id', '=', $taxRate)->firstOrFail();
            return new TaxRateResource($taxRate);
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
    public function edit(TaxRate $taxRate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxRateRequest $request,  $taxRate)
    {
        $taxRate = TaxRate::where('Id', '=', $taxRate)->firstOrFail();
        try {
            DB::beginTransaction();
            $taxRate->update([
                'Id' => $request->Id,
                'HoldingT' => $request->HoldingT,
                'ConservancyT' => $request->ConservancyT,
                'WaterT' => $request->WaterT,
                'LightT' => $request->LightT,
                'TotT' =>  $request->HoldingT + $request->ConservancyT + $request->WaterT + $request->LightT,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data updated successfully.',
                new TaxRateResource($taxRate)
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
    public function destroy($taxRate)
    {
        $tblPropUseID = TaxRate::where('Id', '=', $taxRate)->firstOrFail();
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
