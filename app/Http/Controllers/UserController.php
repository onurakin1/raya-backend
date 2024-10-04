<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Rooms;
use App\Models\RoomUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

    public function index()
    {
        return User::all();
    }
    public function show($id)
    {
        $company = User::where('id', $id)
            ->get();

        return response()->json($company);
    }
    public function updateUser(Request $request)
    {
        // Validasyon
        $validatedData = $request->validate([
            'file' => 'nullable|mimes:jpg,png,pdf|max:2048', // nullable ile isteğe bağlı
            'firstname' => 'nullable|string|max:255', // nullable ile isteğe bağlı
            'lastname' => 'nullable|string|max:255', // nullable ile isteğe bağlı
            'phone_number' => 'nullable|string|max:255', // nullable ile isteğe bağlı
        ]);

        // Kullanıcıyı bul (örneğin, kimliğiyle)
        $user = $request->user();

        $asteriskUsers = RoomUsers::where('user_id', $user->id)->first();
        $rooms = Rooms::where('created_by', $user->id)->first();
        $userId = $user->id;
        $companyToGuide = Company::whereHas('guides', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->first(); // Koleksiyonun ilk elemanını al
        
        if ($companyToGuide) {
            $company = [
                'id' => $companyToGuide->id,
                'name' => $companyToGuide->name,
            ];
        } else {
            $company = null; // Eğer hiç şirket yoksa null dönebilir
        }
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        // Dosya yükleme işlemi
        if ($request->hasFile('file')) {
            // Dosya yükleme
            $fileName = 'https://sip.limonist.dev/uploads' . $request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('images', $fileName, 'public');

            // Kullanıcının profil URL'sini güncelle
            $user->photo_link = $filePath;
        }

        // Eğer name alanı varsa, kullanıcı adını güncelle
        if (isset($validatedData['firstname'])) {
            $user->name = $validatedData['firstname'];
        }

        if (isset($validatedData['lastname'])) {
            $user->last_name = $validatedData['lastname'];
        }

        if (isset($validatedData['phone_number'])) {
            $user->phone_number = $validatedData['phone_number'];
        }

        // Kullanıcıyı güncelle
        $user->save();

        // Başarılı yanıt döndür
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'id' => $user->id,
                'firstname' => $user->name,
                'lastname' => $user->last_name,
                'phone_number' => $user->phone_number,
                'username' => $user->email,
                'photo_link' => $user->photo_link ?: "",
                'room' => $rooms,
           

              'isabel' => [
                  'username' => $asteriskUsers->name,
                  'password' => $asteriskUsers->password,
                  'url' => "pbx.limonisthost.com"
              ],
              'company' => $company,
            ]
        ]);
    }




    public function deleteUser(Request $request)
    {

        try {

            $languageType = $request->header('Accept-Language');

            $request->user()->delete();
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,


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
    public function destroy(Request $request)
    {

        try {

            $languageType = $request->header('Accept-Language');

            $request->user()->delete();
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,


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
}
