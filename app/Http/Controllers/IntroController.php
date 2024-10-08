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
        try {
            $languageType = $request->header('Accept-Language');
            $intros = Intro::all();
    
            // Veriyi istediğin formata dönüştür
            $formattedIntros = $intros->map(function ($intro) use ($languageType) {
                // description alanını JSON'dan diziye dönüştür
                $descriptions = json_decode($intro->description, true);
    
                // Dil bilgisine göre açıklamayı al
                $description = $descriptions[$languageType] ?? $descriptions['en'] ?? '';
    
                return [
                    'image' => $intro->image,
                    'description' => $description, // Kullanıcının diline göre açıklamayı ekle
                ];
            });
    
            // Dil bilgisine göre başarı mesajını ayarla
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
    
            // Dönüştürülmüş veriyi JSON olarak döndür
            return response()->json([
                'status' => true,
                'message' => $successMessage, // Başarı mesajı
                'data' => [
                    'items' => $formattedIntros
                ]
            ]);
        } catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
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
