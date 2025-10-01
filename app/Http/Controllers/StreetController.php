<?php

namespace App\Http\Controllers;

use App\Http\API\BaseController;
use App\Http\Resources\StreetResource;
use App\Models\Street;
use App\Http\Requests\StoreStreetRequest;
use App\Http\Requests\UpdateStreetRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StreetController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try {
            $street = Street::when($request->StreetID, function ($q, $StreetID) {
                $q->where('StreetID', $StreetID);
            })->when($request->search, function ($q, $search) {
                $q->where('StreetID', 'LIKE', '%' . $search . '%');
                $q->orWhere('StreetName', 'LIKE', '%' . $search . '%');
            })->paginate($request->per_page ?? 15);

            return StreetResource::collection($street);
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
    public function store(StoreStreetRequest $request)
    {
        DB::beginTransaction();
        try {
            $street = Street::create([
                'StreetID' => $request->StreetID,
                'StreetName' => $request->StreetName,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data created successfully.',
                new StreetResource($street)
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
    public function show($StreetID)
    {
        try {
            $street = Street::where('StreetID', '=', $StreetID)->firstOrFail();
            return new StreetResource($street);
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
    public function edit(Street $street)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateStreetRequest $request, $street)
    {
        $street = Street::where('StreetID', '=', $street)->firstOrFail();
        try {
            DB::beginTransaction();
            $street->update([
                'StreetID' => $request->StreetID,
                'StreetName' => $request->StreetName,
            ]);
            DB::commit();
            return $this->sendResponse(
                'data updated successfully.',
                new StreetResource($street)
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
    public function destroy($StreetID)
    {
        $street = Street::where('StreetID', '=', $StreetID)->firstOrFail();
        try {
            DB::beginTransaction();
            $street->delete();
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
