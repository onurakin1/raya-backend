<?php

namespace App\Http\Controllers;

use App\Models\Intro;
use App\Http\Requests\StoreIntroRequest;
use App\Http\Requests\UpdateIntroRequest;
use Illuminate\Http\Request;

class IntroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $languageType = $request->header('Accept-Language');
        $intros = Intro::all();
    
        // Veriyi istediğin formata dönüştür
        $formattedIntros = $intros->map(function ($intro) use ($languageType) {
            // description'dan button_title bilgilerini çıkar
            $button_titles = json_decode($intro->description, true);
            // Dil bilgisine göre button_title'ı belirle
            $button_title = $button_titles[$languageType] ?? $button_titles['en'];
    
            return [
                'image' => $intro->image,
                'description' => $button_title, // orijinal açıklamayı da eklemek isterseniz
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
