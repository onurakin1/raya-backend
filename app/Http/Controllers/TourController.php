<?php

namespace App\Http\Controllers;

use App\Models\RoomUsers;
use App\Models\Tours;
use Illuminate\Http\Request;
use App\Models\TourToGuide;
use App\Models\Offers;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;

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

    public function createOffers(Request $request)
    {
       // Request body'den verileri al
       $offerData = $request->only([
        'full_name',
        'phone_number',
        'email',
        'tax_plate',
        'signature_circular',
        'created_at',
        'deleted_at'
    ]);

    // Yeni teklif oluştur
    $offer = Offers::create($offerData);

    // Başarılı yanıt döndür
    return response()->json([
        'message' => 'Teklif başarıyla oluşturuldu.',
        'offer' => $offer
    ], 201);
    }
    public function AccountCheck(Request $request)
    {
        try {
            $code = $request->query('code');
            $languageType = $request->header('Accept-Language') ?? 'en'; // Dil bilgisi boşsa varsayılan olarak 'en' kabul edilir
            
            // name ve role ile filtreleme
            $room_users = RoomUsers::where('name', $code)
                ->where('role', 'User')
                ->get();
    
            // Eğer kayıt bulunmuşsa
            if (!$room_users->isEmpty()) {
                $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
                return response()->json([
                    'status' => true,
                    'message' => $successMessage, // Başarı mesajı
                    
                ]);
            }
    
            // Eğer kayıt bulunamamışsa
            $notFoundMessage = ($languageType === 'tr') ? 'Bulunamadı' : 'Not Found';
            return response()->json([
                'status' => false,
                'message' => $notFoundMessage,
         
            ], 404);
    
        } catch (\Exception $e) {
            // Dil bilgisine göre hata mesajını ayarla
            $languageType = $request->header('Accept-Language') ?? 'en'; // Hata durumunda dil ayarını tekrar alıyoruz
            $errorMessage = ($languageType === 'tr') ? 'Sunucu hatası oluştu' : 'Server error occurred';
    
            // Hata durumunda JSON yanıtı döndür
            return response()->json([
                'status' => false,
                'message' => $errorMessage, // Hata mesajı
            ], 500);
        }
    }
    
    public function tourRegistration(Request $request)
    {
        try{
            $code = $request->query('code');
            $tour = Tours::where('tour_code', $code)->first();
            $details = $tour->details;
            $tourDates = optional($details->first())->tour_dates;
            $languageType = $request->header('Accept-Language');

            if ($tour) {
                // Tarih aralığını ayır
                list($startDate, $endDate) = explode(' - ', $tourDates);
                $startDate = Carbon::createFromFormat('d.m.Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d.m.Y', trim($endDate));
    
                // Eğer tarih aralığı geçmişse kapalı olarak işaretle
                if ($endDate->isPast()) {
                    $tourExpires = ($languageType === 'tr') ? 'Tur tarihi geçmiş' : 'Tour date has expired';
                    return response()->json([
                        'status' => true,
                        'message' => $tourExpires,
                        'data' => (object) [],
                    ], 404);
                }
            }
            if (!$tour) {
                $notFoundMessage = ($languageType === 'tr') ? 'Bulunamadı' : 'Not Found';
                return response()->json([
                    'status' => false,
                    'message' => $notFoundMessage,
                    'data' => null
                ], 404); // 404 Not Found
            }
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' =>  [
                    'id' => $tour->id,
                    'code' => $tour->tour_code, // Sadece tour_code'yu dahil ediyoruz
                ],
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

    public function tourDetail(Request $request)
    {
        try{
            $id = $request->query('id');
            $tour = Tours::where('id', $id)->first();
            $languageType = $request->header('Accept-Language');
    
            // Eğer tur bulunamazsa hata mesajı döndür
            if (!$tour) {
                $notFoundMessage = ($languageType === 'tr') ? 'Bulunamadı' : 'Not Found';
                return response()->json([
                    'status' => false,
                    'message' => $notFoundMessage,
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
    
            // Tur tarihlerini kontrol et
            $tourDates = optional($details->first())->tour_dates;
            $isClosed = false; // Varsayılan olarak kapalı değil
    
            if ($tourDates) {
                // Tarih aralığını ayır
                list($startDate, $endDate) = explode(' - ', $tourDates);
                $startDate = Carbon::createFromFormat('d.m.Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d.m.Y', trim($endDate));
    
                // Eğer tarih aralığı geçmişse kapalı olarak işaretle
                if ($endDate->isPast()) {
                    $isClosed = true;
                }
            }
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            // Tur bilgilerini ve detayları JSON formatında döndür
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'id' => $tour->id,
                    'title' => $tourName,
                    'code' => $tour->tour_code, // Eğer tour_code'yu da dahil etmek istiyorsanız
                    'description' => $tourDescription,
                    'date' => $tourDates,
                    'rooms' => $rooms, // Sadece geçerli odalar
                    'materials' => $materials ?? [], // Eğer materials null ise boş dizi döndür
                    'closed' => $isClosed // Kapalı durumu
                ],
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




    public function getAllToursToGuide(Request $request)
    {
        try {
            $user = $request->user();
            $userId = $user->id;
            $languageType = $request->header('Accept-Language');
    
            // Kullanıcıya atanmış turları al
            $tours = Tours::whereHas('guides', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->with('details')->get();
    
            // Eğer kullanıcıya atanmış tur yoksa hata mesajı döndür
            if ($tours->isEmpty()) {
                $NoFoundTourMessage = ($languageType === 'tr') ? 'Bu kullanıcı için tur bulunamadı.' : 'No tours found for this user.';
                return response()->json([
                    'status' => false,
                    'message' => $NoFoundTourMessage,
                    'data' => null
                ], 404); // 404 Not Found
            }
    
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
    
            // Turları ve detaylarını JSON formatında döndür
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => [
                    'items' => $tours->map(function ($tour) use ($languageType) {
                        // Tour name localization
                        $tourNames = json_decode($tour->name, true);
                        $tourName = $tourNames[$languageType] ?? $tourNames['en'] ?? '';
    
                        // Details localization
                        $tourDetails = $tour->details->first();
                        $tourDescriptions = json_decode(optional($tourDetails)->description, true);
                        $tourDescription = $tourDescriptions[$languageType] ?? $tourDescriptions['en'] ?? '';
    
                        // Extract additional details safely
                        $tourRooms = json_decode(optional($tourDetails)->voice_rooms, true) ?? [];
                        $tourMaterials = json_decode(optional($tourDetails)->materials, true) ?? [];
                        $tourDates = optional($tourDetails)->tour_dates ?? 'N/A'; // Get tour dates
    
                        // Check if the dates are in the past
                        if ($tourDates !== 'N/A') {
                            [$startDate, $endDate] = explode(' - ', $tourDates);
                            // Convert to Carbon instances for comparison
                            $currentDate = now(); // Get the current date
                            $isPast = Carbon::parse($endDate)->isPast(); // Check if the end date is in the past
    
                            // If the tour dates are in the past, return null
                            if ($isPast) {
                                return null; // Return null instead of an object
                            }
                        }
    
                        return [
                            'id' => $tour->id,
                            'code' => $tour->tour_code,
                            'title' => $tourName,
                            'description' => $tourDescription,
                            'date' => $tourDates,
                            'materials' => $tourMaterials,
                            'rooms' => $tourRooms,
                        ];
                    })->filter() // Remove null values from the collection
                ],
            ]);
            
        } catch (AuthenticationException $e) {
            $errorMessage = ($languageType === 'tr') ? 'Yetkilendirme hatası oluştu' : 'Unauthorized access';
            return response()->json([
                'status' => false,
                'message' => $errorMessage,
            ], 401); // 401 Unauthorized
    
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
