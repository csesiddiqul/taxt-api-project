<?php

namespace App\Http\Controllers;

use App\Models\year;
use App\Http\Requests\StoreyearRequest;
use App\Http\Requests\UpdateyearRequest;

class YearController extends Controller
{

    public function year(){
        return year::first();
    }
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
    public function store(StoreyearRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(year $year)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(year $year)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateyearRequest $request, year $year)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(year $year)
    {
        //
    }
}
