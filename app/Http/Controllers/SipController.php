<?php

namespace App\Http\Controllers;

use App\Models\Sip;
use App\Http\Requests\StoreSpiRequest;
use App\Http\Requests\UpdateSpiRequest;

class SipController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Sip::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpiRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Sip $spi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpiRequest $request, Sip $spi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sip $spi)
    {
        //
    }
}
