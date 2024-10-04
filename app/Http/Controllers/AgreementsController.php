<?php

namespace App\Http\Controllers;

use App\Models\Intro;
use App\Http\Requests\StoreIntroRequest;
use App\Http\Requests\UpdateIntroRequest;
use App\Models\Agreements;
use Illuminate\Http\Request;
class AgreementsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $languageType = $request->header('Accept-Language');

            $agreements = Agreements::all();
    
            // Veriyi istediğin formata dönüştür
            $formattedAgreements = $agreements->map(function ($agreement) use ($languageType) {
                // description alanını JSON'dan diziye dönüştür
                $titles = json_decode($agreement->title, true);
                $descriptions = json_decode($agreement->description, true);
        
                // Dil bilgisine göre açıklamayı al
                $title = $titles[$languageType] ?? $titles['en'] ?? '';
                $description = $descriptions[$languageType] ?? $titles['en'] ?? '';
                return [
                    'id' => $agreement->id,
                    'title' => $title,
                    'description' => $description
                   // Kullanıcının diline göre açıklamayı ekle
                ];
            });
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            // Dönüştürülmüş veriyi JSON olarak döndür
            return response()->json([
                'status' => true,
                'message' =>  $successMessage,
                'data' => $formattedAgreements
            ]);
        }
        catch (\Exception $e) {
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
