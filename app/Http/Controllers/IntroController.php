<?php

namespace App\Http\Controllers;

use App\Models\Intro;
use App\Http\Requests\StoreIntroRequest;
use App\Http\Requests\UpdateIntroRequest;

class IntroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Veritabanından tüm verileri al
        $intros = Intro::all();

        // Veriyi istediğin formata dönüştür
        $formattedIntros = $intros->map(function ($intro) {
            return [
                'image' => $intro->image,
                'description' => $intro->description,
            ];
        });

        // Dönüştürülmüş veriyi JSON olarak döndür
        return response()->json([
            'status' => true,
            'message' => 'The operation has been successfully completed.',
            'data' => [
                'items' => $formattedIntros
            ]
            
            
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreIntroRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Intro $intro)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateIntroRequest $request, Intro $intro)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Intro $intro)
    {
        //
    }
}
