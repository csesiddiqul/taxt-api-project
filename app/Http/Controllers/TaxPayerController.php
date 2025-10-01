<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Models\TaxPayer;
use App\Http\Requests\StoreTaxPayerRequest;
use App\Http\Requests\UpdateTaxPayerRequest;
use App\Http\Resources\TaxPayerResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaxPayerController extends BaseController
{

    public function taxPayer(Request $request)
    {
        try {
            $taxPayer = TaxPayer::select('ClientNo', 'OwnersName', 'BillingAddress', 'HoldingNo')
                ->when($request->search, function ($q, $search) {
                    $q->where(function ($query) use ($search) {
                        $query->where('HoldingNo', 'LIKE', '%' . $search . '%')
                            ->orWhere('ClientNo', 'LIKE', '%' . $search . '%')
                            ->orWhere('OwnersName', 'LIKE', '%' . $search . '%')
                            ->orWhere('BillingAddress', 'LIKE', '%' . $search . '%');
                    });
                })
                ->paginate($request->per_page ?? 15);

            return TaxPayerResource::collection($taxPayer);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('Data fetch failed.', $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->sendError('Data fetch failed.', $exception->getMessage());
        }
    }
    public function index(Request $request)
    {
        try {
            $taxPayer = TaxPayer::with(
                'street',
                'TblPropType',
                'PropUseID',
                'TaxpayerType',
                'BankAcc'
            )
                // Filter by exact HoldingNo if provided
                ->when($request->HoldingNo, function ($q, $HoldingNo) {
                    $q->where('HoldingNo', $HoldingNo);
                })
                // Search across multiple columns
                ->when($request->search, function ($q, $search) {
                    $q->where(function ($q) use ($search) {
                        // Text columns
                        $q->where('ClientNo', 'LIKE', "%{$search}%")
                            ->orWhere('StreetID', 'LIKE', "%{$search}%")
                            ->orWhere('OwnersName', 'LIKE', "%{$search}%")
                            ->orWhere('FHusName', 'LIKE', "%{$search}%")
                            ->orWhere('BillingAddress', 'LIKE', "%{$search}%")
                            ->orWhere('PropTypeID', 'LIKE', "%{$search}%")
                            ->orWhere('PropUseID', 'LIKE', "%{$search}%")
                            ->orWhere('TaxpayerTypeID', 'LIKE', "%{$search}%")
                            ->orWhere('BankNo', 'LIKE', "%{$search}%");

                        // Numeric columns (exact match instead of LIKE)
                        if (is_numeric($search)) {
                            $q->orWhere('OriginalValue', $search)
                                ->orWhere('CurrentValue', $search)
                                ->orWhere('HoldingTax', $search)
                                ->orWhere('WaterTax', $search)
                                ->orWhere('LightingTax', $search)
                                ->orWhere('ConservancyTax', $search)
                                ->orWhere('Arrear', $search);
                        }

                        // Boolean/Active column (optional)
                        if (in_array(strtolower($search), ['0', '1', 'active', 'inactive'])) {
                            $q->orWhere('Active', $search);
                        }
                    });
                })
                ->paginate($request->per_page ?? 15);

            return TaxPayerResource::collection($taxPayer);
        } catch (ModelNotFoundException $exception) {
            return $this->sendError('Data fetch failed.', $exception->getMessage());
        } catch (\Exception $exception) {
            return $this->sendError('Data fetch failed.', $exception->getMessage());
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
    public function store(StoreTaxPayerRequest $request)
    {
        try {
            DB::beginTransaction();
            $taxPayer = TaxPayer::create($request->validated());
            DB::commit();
            return $this->sendResponse(
                'Data created successfully.',
                new TaxPayerResource($taxPayer)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError(
                message: 'Data creation failed!',
                errors: $e->getMessage(),
                status: 500
            );
        }
    }


    /**
     * Display the specified resource.
     */
    public function show($HoldingNo)
    {
        try {
            $taxPayer = TaxPayer::with(
                'street',
                'TblPropType',
                'PropUseID',
                'TaxpayerType',
                'TaxpayerType',
                'BankAcc'
            )->where('HoldingNo', '=', $HoldingNo)->firstOrFail();
            return new TaxPayerResource($taxPayer);
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


    public function clientNoShow($clientNo)
    {
        try {
            $taxPayer = TaxPayer::with(
                'street',
                'TblPropType',
                'PropUseID',
                'TaxpayerType',
                'TaxpayerType',
                'BankAcc'
            )->where('ClientNo', '=', $clientNo)->firstOrFail();
            return new TaxPayerResource($taxPayer);
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
    public function edit(TaxPayer $taxPayer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTaxPayerRequest $request, TaxPayer $taxPayer)
    {
        $taxPayer->update($request->validated());

        return $this->sendResponse(
            'Data updated successfully.',
            new TaxPayerResource($taxPayer)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaxPayer $taxPayer)
    {
        //
    }
}
