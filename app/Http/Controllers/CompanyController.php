<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Carbon\Carbon;

class CompanyController extends Controller
{

    public function index() {}


    public function store(Request $request)
    {
        try {
            $languageType = $request->header('Accept-Language');
            $today = Carbon::today();
            $createdCompany = Company::create([
                'name' => $request->name,
                'created_at' => $today

            ]);
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'success' => true,
                'message' => $successMessage,

                'data' => $createdCompany
            ], 201);
        }    catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500); // 500 sunucu hatası kodu
        }
    }


    public function show(Company $intro)
    {
        //
    }


    public function update(Request $request, Company $intro)
    {
        //
    }


    public function destroy(Company $intro)
    {
        //
    }
}
