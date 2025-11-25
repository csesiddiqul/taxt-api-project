<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Models\Billregister;
use App\Models\TaxPayer;
use App\Models\TaxRate;
use App\Http\Requests\StoreBillregisterRequest;
use App\Http\Requests\GovtBillregisterRequest;
use App\Http\Requests\SingleBillregisterRequest;
use App\Http\Requests\StoreTaxPayerRequest;
use App\Http\Requests\UpdateBillregisterRequest;
use App\Http\Resources\BillRegisterResource;
use App\Http\Resources\TaxPayerResource;
use App\Models\year;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class BillregisterController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function stsore(StoreTaxPayerRequest $request)
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



    public function store(StoreBillregisterRequest $request)
    {

        $clients = TaxPayer::where('Active', 1)->where('TaxpayerTypeID', '!=', 2)->get();

        $count = 0;

        foreach ($clients as $client) {
            $exists = Billregister::where('ClientNo', $client->ClientNo)
                ->where('Year', $request->year)
                ->where('Year1', $request->year1)
                ->where('Period_of_Bill', $request->period)
                ->exists();

            if ($exists) continue;


            $alreadyPaid = Billregister::where('ClientNo', $client->ClientNo)
                ->where('Year', $request->year)
                ->where('Year1', $request->year1)
                ->where(function ($q) {
                    $q->where('Paid_Date', '!=', 0)
                        ->whereNotNull('Paid_Date');
                })
                ->exists();

            if ($alreadyPaid) {
                continue;
            }


            $taxrate = TaxRate::first();
            $CurrentValue = $client->CurrentValue ?? 0;

            $holdingTax = $CurrentValue * ($client->HoldingTax == 1 ? $taxrate->HoldingT : 0) / 100;
            $lightTax   = $CurrentValue * ($client->LightingTax == 1 ? $taxrate->LightT : 0) / 100;
            $waterTax   = $CurrentValue * ($client->WaterTax == 1 ? $taxrate->WaterT : 0) / 100;
            $conserTax  = $CurrentValue * ($client->ConservancyTax == 1 ? $taxrate->ConservancyT : 0) / 100;

            $Q1 = ($holdingTax + $lightTax + $waterTax + $conserTax) / 4;

            $currentCharge2 = $Q1 * 2;
            $currentCharge3 = $Q1 * 3;
            $currentCharge4 = $holdingTax + $lightTax + $waterTax + $conserTax;


            switch ($request->period) {
                case 1:
                    $rebate1 = $Q1 * 0.05;
                    $rebate2 = $currentCharge2 * 0.075;
                    $rebate3 = $currentCharge3 * 0.075;
                    $rebate4 = $currentCharge4 * 0.10;
                    break;

                case 2:
                    $rebate1 = $Q1 * 0.00;
                    $rebate2 = $currentCharge2 * 0.025;
                    $rebate3 = $currentCharge3 * 0.05;
                    $rebate4 = $currentCharge4 * 0.05625;
                    break;

                case 3:
                    $rebate1 = $Q1 * 0.00;
                    $rebate2 = $currentCharge2 * 0.00;
                    $rebate3 = $currentCharge3 * 0.0167;
                    $rebate4 = $currentCharge4 * 0.0375;
                    break;

                case 4:
                    $rebate1 = $Q1 * 0.00;
                    $rebate2 = $currentCharge2 * 0.00;
                    $rebate3 = $currentCharge3 * 0.00;
                    $rebate4 = $currentCharge4 * 0.0125;
                    break;

                default:
                    $rebate1 = $rebate2 = $rebate3 = $rebate4 = 0;
                    break;
            }

            $arrear = $client->Arrear ?? 0;

            DB::table('Bill_Register')->insert([
                'HoldingNo' => $client->HoldingNo,
                'ClientNo' => $client->ClientNo,
                'Year' => $request->year,
                'Year1' => $request->year1 ?? null,
                'Period_of_Bill' => $request->period,
                'DateOfIssue' => $request->issue_date,
                'LastPaymentDate' => $request->last_date,

                'ArrStYear' => $client->ArrStYear,
                'ArrStYear1' => $client->ArrStYear1,
                'ArrStPeriod' => $client->ArrStPeriod,

                'HoldingTax' => $holdingTax,
                'LightTax' => $lightTax,
                'ConserTax' => $conserTax,
                'WaterTax' => $waterTax,

                'Q1' => $Q1,
                'Q2' => $Q1,
                'Q3' => $Q1,
                'Q4' => $Q1,

                'CurrentChearge' => $Q1,
                'CurrentChearge2' => $currentCharge2,
                'CurrentChearge3' => $currentCharge3,
                'CurrentChearge4' => $currentCharge4,


                '1QRebate' => $rebate1,
                '2QRebate' => $rebate2,
                '3QRebate' => $rebate3,
                '4QRebate' => $rebate4,

                'YArear' => $arrear,

                'Surcharge' => ($arrear > 0) ? ($arrear * 0.05) : 0,
                'PartArrPay' => 0,
                'PartSur' => 0,
                'BillPaid' => 0,
                'Paid_Date' => 0,
                'TaxpayerTypeID' => $client->TaxpayerTypeID,
                'sr' => 0,
                'PartCurrent' => null,
                'PartPayDate' => null,
            ]);

            $count++;
        }

        return response()->json([
            'message' => 'Bill generated successfully',
            'total_generated' => $count
        ], 200);
    }

    public function billgenerate(StoreBillregisterRequest $request)
    {

        $clients = TaxPayer::where('Active', 1)
            ->where('TaxpayerTypeID', '!=', 2)
            ->take(500)
            ->get();

        $count = 0;

        foreach ($clients as $client) {
            $exists = Billregister::where('ClientNo', $client->ClientNo)
                ->where('Year', $request->year)
                ->where('Year1', $request->year1)
                ->where('Period_of_Bill', $request->period)
                ->exists();

            if ($exists) continue;


            $alreadyPaid = Billregister::where('ClientNo', $client->ClientNo)
                ->where('Year', $request->year)
                ->where('Year1', $request->year1)
                ->where(function ($q) {
                    $q->where('Paid_Date', '!=', 0)
                        ->whereNotNull('Paid_Date');
                })
                ->exists();

            if ($alreadyPaid) {
                continue;
            }


            $taxrate = TaxRate::first();

            $CurrentValue = $client->CurrentValue ?? 0;

            $holdingTax = $CurrentValue * ($client->HoldingTax == 1 ? $taxrate->HoldingT : 0) / 100;
            $lightTax   = $CurrentValue * ($client->LightingTax == 1 ? $taxrate->LightT : 0) / 100;
            $waterTax   = $CurrentValue * ($client->WaterTax == 1 ? $taxrate->WaterT : 0) / 100;
            $conserTax  = $CurrentValue * ($client->ConservancyTax == 1 ? $taxrate->ConservancyT : 0) / 100;

            $Q1 = ($holdingTax + $lightTax + $waterTax + $conserTax) / 4;


            $currentCharge2 = $Q1 * 2;
            $currentCharge3 = $Q1 * 3;
            $currentCharge4 = $holdingTax + $lightTax + $waterTax + $conserTax;


            switch ($request->period) {
                case 1:
                    $rebate1 = $Q1 * 0.05;
                    $rebate2 = $currentCharge2 * 0.075;
                    $rebate3 = $currentCharge3 * 0.075;
                    $rebate4 = $currentCharge4 * 0.10;
                    break;

                case 2:
                    $rebate1 = $Q1 * 0.00;
                    $rebate2 = $currentCharge2 * 0.025;
                    $rebate3 = $currentCharge3 * 0.05;
                    $rebate4 = $currentCharge4 * 0.05625;
                    break;

                case 3:
                    $rebate1 = $Q1 * 0.00;
                    $rebate2 = $currentCharge2 * 0.00;
                    $rebate3 = $currentCharge3 * 0.0167;
                    $rebate4 = $currentCharge4 * 0.0375;
                    break;

                case 4:
                    $rebate1 = $Q1 * 0.00;
                    $rebate2 = $currentCharge2 * 0.00;
                    $rebate3 = $currentCharge3 * 0.00;
                    $rebate4 = $currentCharge4 * 0.0125;
                    break;

                default:
                    $rebate1 = $rebate2 = $rebate3 = $rebate4 = 0;
                    break;
            }



            $arrear = $client->Arrear ?? 0;

            DB::table('Bill_Register')->insert([
                'HoldingNo' => $client->HoldingNo,
                'ClientNo' => $client->ClientNo,
                'Year' => $request->year,
                'Year1' => $request->year1 ?? null,
                'Period_of_Bill' => $request->period,
                'DateOfIssue' => $request->issue_date,
                'LastPaymentDate' => $request->last_date,

                'ArrStYear' => $client->ArrStYear,
                'ArrStYear1' => $client->ArrStYear1,
                'ArrStPeriod' => $client->ArrStPeriod,

                'HoldingTax' => $holdingTax,
                'LightTax' => $lightTax,
                'ConserTax' => $conserTax,
                'WaterTax' => $waterTax,

                'Q1' => $Q1,
                'Q2' => $Q1,
                'Q3' => $Q1,
                'Q4' => $Q1,

                'CurrentChearge' => $Q1,
                'CurrentChearge2' => $currentCharge2,
                'CurrentChearge3' => $currentCharge3,
                'CurrentChearge4' => $currentCharge4,


                '1QRebate' => $rebate1,
                '2QRebate' => $rebate2,
                '3QRebate' => $rebate3,
                '4QRebate' => $rebate4,

                'YArear' => $arrear,


                'Surcharge' => ($arrear > 0) ? ($arrear * 0.05) : 0,

                'PartArrPay' => 0,
                'PartSur' => 0,
                'BillPaid' => 0,
                'Paid_Date' => 0,
                'TaxpayerTypeID' => $client->TaxpayerTypeID,
                'sr' => 0,
                'PartCurrent' => null,
                'PartPayDate' => null,
            ]);

            $count++;
        }

        return response()->json([
            'message' => 'Bill generated successfully',
            'total_generated' => $count
        ], 200);
    }


    public function govtbillgenerate(GovtBillregisterRequest $request)
    {

        $clients = TaxPayer::where('Active', 1)->where('TaxpayerTypeID', '=',2)->get();

        $count = 0;

        foreach ($clients as $client) {
            $exists = Billregister::where('ClientNo', $client->ClientNo)
                ->where('Year', $request->year)
                ->where('Year1', $request->year1)
                ->where('Period_of_Bill', $request->period)
                ->exists();

            if ($exists) continue;


            $alreadyPaid = Billregister::where('ClientNo', $client->ClientNo)
                ->where('Year', $request->year)
                ->where('Year1', $request->year1)
                ->where(function ($q) {
                    $q->where('Paid_Date', '!=', 0)
                        ->whereNotNull('Paid_Date');
                })
                ->exists();

            if ($alreadyPaid) {
                continue;
            }


            $taxrate = TaxRate::first();
            $CurrentValue = $client->CurrentValue ?? 0;

            $holdingTax = $CurrentValue * ($client->HoldingTax == 1 ? $taxrate->HoldingT : 0) / 100;
            $lightTax   = $CurrentValue * ($client->LightingTax == 1 ? $taxrate->LightT : 0) / 100;
            $waterTax   = $CurrentValue * ($client->WaterTax == 1 ? $taxrate->WaterT : 0) / 100;
            $conserTax  = $CurrentValue * ($client->ConservancyTax == 1 ? $taxrate->ConservancyT : 0) / 100;

            $Q1 = ($holdingTax + $lightTax + $waterTax + $conserTax) / 4;


            $currentCharge2 = $Q1 * 2;
            $currentCharge3 = $Q1 * 3;
            $currentCharge4 = $holdingTax + $lightTax + $waterTax + $conserTax;


            $rebate1 = $Q1 * 0.025;
            $rebate2 = $currentCharge2 * 0.0375;
            $rebate3 = $currentCharge3 * 0.0375;
            $rebate4 = $currentCharge4 * 0.05;

            // switch ($request->period) {
            //     case 1:
            //         $rebate1 = $Q1 * 0.05;
            //         $rebate2 = $currentCharge2 * 0.075;
            //         $rebate3 = $currentCharge3 * 0.075;
            //         $rebate4 = $currentCharge4 * 0.10;
            //         break;

            //     case 2:
            //         $rebate1 = $Q1 * 0.00;
            //         $rebate2 = $currentCharge2 * 0.025;
            //         $rebate3 = $currentCharge3 * 0.05;
            //         $rebate4 = $currentCharge4 * 0.05625;
            //         break;

            //     case 3:
            //         $rebate1 = $Q1 * 0.00;
            //         $rebate2 = $currentCharge2 * 0.00;
            //         $rebate3 = $currentCharge3 * 0.0167;
            //         $rebate4 = $currentCharge4 * 0.0375;
            //         break;

            //     case 4:
            //         $rebate1 = $Q1 * 0.00;
            //         $rebate2 = $currentCharge2 * 0.00;
            //         $rebate3 = $currentCharge3 * 0.00;
            //         $rebate4 = $currentCharge4 * 0.0125;
            //         break;

            //     default:
            //         $rebate1 = $rebate2 = $rebate3 = $rebate4 = 0;
            //     break;
            // }

            $arrear = $client->Arrear ?? 0;

            DB::table('Bill_Register')->insert([
                'HoldingNo' => $client->HoldingNo,
                'ClientNo' => $client->ClientNo,
                'Year' => $request->year,
                'Year1' => $request->year1 ?? null,
                'Period_of_Bill' => $request->period,
                'DateOfIssue' => $request->issue_date,
                'LastPaymentDate' => $request->last_date,

                'ArrStYear' => $client->ArrStYear,
                'ArrStYear1' => $client->ArrStYear1,
                'ArrStPeriod' => $client->ArrStPeriod,

                'HoldingTax' => $holdingTax,
                'LightTax' => $lightTax,
                'ConserTax' => $conserTax,
                'WaterTax' => $waterTax,

                'Q1' => $Q1,
                'Q2' => $Q1,
                'Q3' => $Q1,
                'Q4' => $Q1,

                'CurrentChearge' => $Q1,
                'CurrentChearge2' => $currentCharge2,
                'CurrentChearge3' => $currentCharge3,
                'CurrentChearge4' => $currentCharge4,


                '1QRebate' => $rebate1,
                '2QRebate' => $rebate2,
                '3QRebate' => $rebate3,
                '4QRebate' => $rebate4,

                'YArear' => $arrear,

                'Surcharge' => ($arrear > 0) ? ($arrear * $request->surcharge) : 0,

                'PartArrPay' => 0,
                'PartSur' => 0,
                'BillPaid' => 0,
                'Paid_Date' => 0,
                'TaxpayerTypeID' => $client->TaxpayerTypeID,
                'sr' => 0,
                'PartCurrent' => null,
                'PartPayDate' => null,
            ]);

            $count++;
        }

        return response()->json([
            'message' => 'Bill generated successfully',
            'total_generated' => $count
        ], 200);
    }


public function singlebillgenerate(SingleBillregisterRequest $request)
{
    // =============================
    // 1. Fetch Client
    // =============================
    $client = TaxPayer::where('HoldingNo', $request->HoldingNo)
        ->where('ClientNo', $request->ClientNo)
        ->firstOrFail();

    $arrear = $client->Arrear ?? 0;


    // =============================
    // 2. Check Existing Bill
    // =============================
    $exists = Billregister::where('ClientNo', $client->ClientNo)
        ->where('Year', $request->year)
        ->where('Year1', $request->year1)
        ->where('Period_of_Bill', $request->period)
        ->exists();

    if ($exists) {
        return response()->json([
            'success' => false,
            'message' => 'Bill already exists for this period.'
        ], 409);
    }


    // =============================
    // 3. Check Already Paid
    // =============================
    $alreadyPaid = Billregister::where('ClientNo', $client->ClientNo)
        ->where('Year', $request->year)
        ->where('Year1', $request->year1)
        ->whereNotNull('Paid_Date')
        ->where('Paid_Date', '!=', 0)
        ->exists();

    if ($alreadyPaid) {
        return response()->json([
            'success' => false,
            'message' => 'This bill is already paid.'
        ], 409);
    }


    // =============================
    // 4. Tax Calculation
    // =============================
    $taxrate = TaxRate::first();
    $currentValue = $client->CurrentValue ?? 0;

    $holdingTax  = $client->HoldingTax      ? ($currentValue * $taxrate->HoldingT / 100) : 0;
    $lightTax    = $client->LightingTax     ? ($currentValue * $taxrate->LightT / 100) : 0;
    $waterTax    = $client->WaterTax        ? ($currentValue * $taxrate->WaterT / 100) : 0;
    $conserTax   = $client->ConservancyTax  ? ($currentValue * $taxrate->ConservancyT / 100) : 0;

    $Q1 = ($holdingTax + $lightTax + $waterTax + $conserTax) / 4;

    $currentCharge2 = $Q1 * 2;
    $currentCharge3 = $Q1 * 3;
    $currentCharge4 = $holdingTax + $lightTax + $waterTax + $conserTax;


    // =============================
    // 5. Rebate Calculation
    // =============================
    $period = $request->period;

    $rebate1 = $rebate2 = $rebate3 = $rebate4 = 0;
    $Surcharge = 0;

    if ($client->TaxpayerTypeID != 2) {

        switch ($period) {
            case 1:
                $rebate1 = $Q1 * 0.05;
                $rebate2 = $currentCharge2 * 0.075;
                $rebate3 = $currentCharge3 * 0.075;
                $rebate4 = $currentCharge4 * 0.10;
                break;

            case 2:
                $rebate1 = $Q1 * 0.00;
                $rebate2 = $currentCharge2 * 0.025;
                $rebate3 = $currentCharge3 * 0.05;
                $rebate4 = $currentCharge4 * 0.05625;
                break;

            case 3:
                $rebate1 = 0;
                $rebate2 = 0;
                $rebate3 = $currentCharge3 * 0.0167;
                $rebate4 = $currentCharge4 * 0.0375;
                break;

            case 4:
                $rebate1 = 0;
                $rebate2 = 0;
                $rebate3 = 0;
                $rebate4 = $currentCharge4 * 0.0125;
                break;
        }

        $Surcharge = $arrear > 0 ? ($arrear * 0.05) : 0;
    }

    // =============================
    // 6. Taxpayer Type = 2 Rule
    // =============================
    if ($client->TaxpayerTypeID == 2) {
        $rebate1 = $Q1 * 0.025;
        $rebate2 = $currentCharge2 * 0.0375;
        $rebate3 = $currentCharge3 * 0.0375;
        $rebate4 = $currentCharge4 * 0.05;

        $Surcharge = $arrear > 0 ? ($arrear * $request->surcharge) : 0;
    }


    // =============================
    // 7. Insert Bill
    // =============================
DB::table('Bill_Register')->insert([
    'HoldingNo'        => $client->HoldingNo,
    'ClientNo'         => $client->ClientNo,
    'Year'             => $request->year,
    'Year1'            => $request->year1,
    'Period_of_Bill'   => $request->period,
    'DateOfIssue'      => $request->issue_date,
    'LastPaymentDate'  => $request->last_date,

    'ArrStYear'        => $client->ArrStYear,
    'ArrStYear1'       => $client->ArrStYear1,
    'ArrStPeriod'      => $client->ArrStPeriod,

    'HoldingTax'       => $holdingTax,
    'LightTax'         => $lightTax,
    'ConserTax'        => $conserTax,
    'WaterTax'         => $waterTax,

    'Q1'               => $Q1,
    'Q2'               => $Q1,
    'Q3'               => $Q1,
    'Q4'               => $Q1,

    'CurrentChearge'   => $Q1,
    'CurrentChearge2'  => $currentCharge2,
    'CurrentChearge3'  => $currentCharge3,
    'CurrentChearge4'  => $currentCharge4,

    '1QRebate'         => $rebate1,
    '2QRebate'         => $rebate2,
    '3QRebate'         => $rebate3,
    '4QRebate'         => $rebate4,

    'YArear'           => $arrear,

    'Surcharge'        => $Surcharge ?? 0,
    'PartArrPay'       => 0,
    'PartSur'          => 0,
    'BillPaid'         => 0,
    'Paid_Date'        => null,
    'TaxpayerTypeID'   => $client->TaxpayerTypeID,
    'sr'               => 0,
    'PartCurrent'      => null,
    'PartPayDate'      => null,
]);



    return response()->json([
        'success' => true,
        'message' => 'Bill generated successfully',
    ], 200);
}




    public function singleBillShow($ClientNo)
    {

        $year = year::first();
        $billregister = Billregister::where('Year','=',2025)
        ->where('Year1','=',2026)
        ->where('Period_of_Bill','=',1)
        ->where('ClientNo', '=', $ClientNo)->first();

        return  $this->sendResponse(
            'single bill show success',
            new BillRegisterResource($billregister),
            '200'
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(Billregister $billregister)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Billregister $billregister)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillregisterRequest $request, Billregister $billregister)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Billregister $billregister)
    {
        //
    }
}
