<?php

namespace App\Http\Controllers;

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
            'name' => 'nullable|string|max:255', // nullable ile isteğe bağlı
        ]);
    
        // Kullanıcıyı bul (örneğin, kimliğiyle)
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }
    
        // Dosya yükleme işlemi
        if ($request->hasFile('file')) {
            // Dosya yükleme
            $fileName = time().'_'.$request->file('file')->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('images', $fileName, 'public');
    
            // Kullanıcının profil URL'sini güncelle
            $user->profile_url = $filePath;
        }
    
        // Eğer name alanı varsa, kullanıcı adını güncelle
        if (isset($validatedData['name'])) {
            $user->name = $validatedData['name'];
        }
    
        // Kullanıcıyı güncelle
        $user->save();
    
        // Başarılı yanıt döndür
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => [
                'filePath' => $filePath ?? null, // Yüklenen dosya yolu
                'name' => $user->name, // Güncellenmiş kullanıcı adı
                'profile_url' => $user->profile_url // Güncellenmiş profil URL'si
            ]
        ]);
    }
    
    
    
    
    public function deleteUser(Request $request)
    {

        try{
     
            $languageType = $request->header('Accept-Language');

            $request->user()->delete();
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
      
                
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
    public function destroy(Request $request)
    {

        try{
     
            $languageType = $request->header('Accept-Language');

            $request->user()->delete();
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
      

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
}
