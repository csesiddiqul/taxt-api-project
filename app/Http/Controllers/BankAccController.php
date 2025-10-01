<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Http\Resources\BankAccResource;
use App\Models\BankAcc;
use App\Http\Requests\StoreBankAccRequest;
use App\Http\Requests\UpdateBankAccRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankAccController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $bankAcc = BankAcc::when($request->BankNo, function ($q, $BankNo) {
                $q->where('BankNo', $BankNo);
            })->when($request->search, function ($q, $search) {
                $q->where('BankNo', 'LIKE', '%' . $search . '%');
                $q->orWhere('BankName', 'LIKE', '%' . $search . '%');
                $q->orWhere('Branch', 'LIKE', '%' . $search . '%');
                $q->orWhere('AccountsNo', 'LIKE', '%' . $search . '%');
            })->paginate($request->per_page ?? 15);

            return BankAccResource::collection($bankAcc);
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
    public function store(StoreBankAccRequest $request)
    {
        DB::beginTransaction();
        try {
            $street = BankAcc::create([
                'BankNo' => $request->BankNo,
                'BankName' => $request->BankName,
                'Branch' => $request->Branch,
                'AccountsNo' => $request->AccountsNo,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data created successfully.',
                new BankAccResource($street)
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
    public function show($bankAcc)
    {
        try {
            $bankAcc = BankAcc::where('BankNo', '=', $bankAcc)->firstOrFail();
            return new BankAccResource($bankAcc);
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
    public function edit(BankAcc $bankAcc)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankAccRequest $request, $bankAcc)
    {
        $bankAcc = BankAcc::where('BankNo', '=', $bankAcc)->firstOrFail();
        try {
            DB::beginTransaction();
            $bankAcc->update([
                'BankNo' => $request->BankNo,
                'BankName' => $request->BankName,
                'Branch' => $request->Branch,
                'AccountsNo' => $request->AccountsNo,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data updated successfully.',
                new BankAccResource($bankAcc)
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
    public function destroy($bankAcc)
    {
        $bankAcc = BankAcc::where('BankNo', '=', $bankAcc)->firstOrFail();
        try {
            DB::beginTransaction();
            $bankAcc->delete();
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
