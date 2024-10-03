<?php

namespace App\Http\Controllers;

use App\Models\Tours;
use Illuminate\Http\Request;
use App\Models\TourToGuide;
use Carbon\Carbon;

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
        $languageType = $request->header('Accept-Language');
    
        // Eğer tur bulunamazsa hata mesajı döndür
        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found.',
                'data' => null
            ], 404); // 404 Not Found
        }
    
        // Tur adı için dil bilgisine göre değer al
        $tourNames = json_decode($tour->name, true);
        $tourName = $tourNames[$languageType] ?? $tourNames['en'] ?? ''; // 'en' değeri yoksa boş bir string döndür
    
        // İlişkili detayları al
        $details = $tour->details;
    
        // İlk detayın açıklamasını al
        $tourDescriptions = json_decode(optional($details->first())->description, true);
        $tourDescription = $tourDescriptions[$languageType] ?? $tourDescriptions['en'] ?? ''; // 'en' değeri yoksa boş bir string döndür
    
        // voice_rooms kontrolü (geçerlilik süresine göre)
        $roomsData = json_decode(optional($details->first())->voice_rooms, true);
        $rooms = [];
    
        if ($roomsData && is_array($roomsData)) {
            foreach ($roomsData as $room) {
                if (isset($room['expires_time'])) {
                    $expiresTime = Carbon::parse($room['expires_time']);
                    if ($expiresTime->isFuture()) {
                        // expires_time gelecekte ise, odayı ekle
                        $rooms[] = $room;
                    }
                }
            }
        }
    
        // materials verisini al
        $materials = json_decode(optional($details->first())->materials, true);
    
        // Tur bilgilerini ve detayları JSON formatında döndür
        return response()->json([
            'status' => true,
            'message' => 'The operation has been successfully completed.',
            'data' => [
                'id' => $tour->id,
                'title' => $tourName,
                'code' => $tour->tour_code, // Eğer tour_code'yu da dahil etmek istiyorsanız
                'description' => $tourDescription,
                'date' => optional($details->first())->tour_dates,
                'rooms' => $rooms, // Sadece geçerli odalar
                'materials' => $materials ?? [], // Eğer materials null ise boş dizi döndür
            ],
        ]);
    }
    
    

    public function getAllToursToGuide(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $languageType = $request->header('Accept-Language');
    
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
            'data' => $tours->map(function ($tour) use ($languageType) {
                // Tur adı için dil kontrolü
                $tourNames = json_decode($tour->name, true);
                $tourName = $tourNames[$languageType] ?? $tourNames['en'] ?? '';
    
                // Detay açıklaması için dil kontrolü
                $tourDetails = $tour->details->first();
                $tourDescriptions = json_decode(optional($tourDetails)->description, true);
                $tourDescription = $tourDescriptions[$languageType] ?? $tourDescriptions['en'] ?? '';
    
                $tourRooms = json_decode(optional($tourDetails)->voice_rooms, true);
                $tourMaterials = json_decode(optional($tourDetails)->materials, true);
                // Diğer detaylar (tarih, materyaller, odalar)
                $tourDate = optional($tourDetails)->tour_dates ?? 'N/A'; // Eğer tarih yoksa 'N/A' göster
                $tourMaterial = $tourMaterials ?? []; // Eğer materyaller null ise boş dizi
                $tourRoom = $tourRooms ?? []; // Eğer odalar null ise boş dizi
    
                return [
                    'id' => $tour->id,
                    'code' => $tour->tour_code,
                    'name' => $tourName, // Dile göre ayarlanmış tur adı
                    'description' => $tourDescription, // Dile göre ayarlanmış açıklama
                    'date' => $tourDate, // Tarih bilgisi
                    'materials' => $tourMaterial, // Materyal bilgisi
                    'rooms' => $tourRoom, // Oda bilgisi
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
