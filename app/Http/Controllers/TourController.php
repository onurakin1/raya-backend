<?php

namespace App\Http\Controllers;

use App\Models\Tours;
use Illuminate\Http\Request;
use App\Models\TourToGuide;


class TourController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Tüm turları ve ilgili ilk detaylarını al
        $tours = Tours::with('details')->get();

        // Veriyi istediğiniz formata dönüştür
        $formattedTours = $tours->map(function ($tour) {
            return [
                'id' => $tour->id,
                'code' => $tour->tour_code,
                'title' => $tour->name, // Tur ismi
                'description' => optional($tour->details->first())->description, // İlk detayın açıklaması
                'date' => optional($tour->details->first())->tour_dates,
                'materials' => optional($tour->details->first())->materials,
            ];
        });

        // Dönüştürülmüş veriyi JSON olarak döndür
        return response()->json([
            'status' => true,
            'message' => 'The operation has been successfully completed.',
            'data' => $formattedTours
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function tourRegistration(Request $request)
    {
        $code = $request->query('code');
        $tour = Tours::where('tour_code', $code)->first();

        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found.',
                'data' => null
            ], 404); // 404 Not Found
        }

        return response()->json([
            'status' => true,
            'message' => 'The operation has been successfully completed.',
            'data' =>  [
                'id' => $tour->id,
                'code' => $tour->tour_code, // Sadece tour_code'yu dahil ediyoruz
            ],
        ]);
    }

    public function tourDetail(Request $request)
    {
        $id = $request->query('id');
        $tour = Tours::where('id', $id)->first();
        // Eğer tur bulunamazsa hata mesajı döndür
        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found.',
                'data' => null
            ], 404); // 404 Not Found
        }

        // İlişkili detayları al
        $details = $tour->details;

        // Tur bilgilerini ve detayları JSON formatında döndür
        return response()->json([
            'status' => true,
            'message' => 'The operation has been successfully completed.',
            'data' => [
                'id' => $tour->id,
                'title' => $tour->name,

                'code' => $tour->tour_code, // Eğer tour_code'yu da dahil etmek istiyorsanız
                'description' => optional($details->first())->description,
                'date' => optional($details->first())->tour_dates,
                'materials' => optional($details->first())->materials ?? [], // Eğer materials null ise boş dizi döndür
            ],
        ]);
    }

    public function getAllToursToGuide(Request $request)
    {
        $userId = $request->query('user_id');

        // Kullanıcıya atanmış turları al
        $tours = Tours::whereHas('guides', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with('details')->get();

        // Eğer kullanıcıya atanmış tur yoksa hata mesajı döndür
        if ($tours->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No tours found for this user.',
                'data' => null
            ], 404); // 404 Not Found
        }

        // Turları ve detaylarını JSON formatında döndür
        return response()->json([
            'status' => true,
            'message' => 'Tours retrieved successfully.',
            'data' => $tours->map(function ($tour) {
                return [
                    'id' => $tour->id,
                    'code' => $tour->tour_code,
                    'name' => $tour->name,

                    'description' => optional($tour->details->first())->description,
                    'date' => optional($tour->details->first())->tour_dates,
                    'materials' => optional($tour->details->first())->materials ?? [],

           
                ];
            }),
        ]);
    }

    public function show($code) {}


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tours $app_control)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tours $app_control)
    {
        //
    }
}
