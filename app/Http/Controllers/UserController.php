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
        try {
            // Find the user by ID
        
            $user = $request->user();
            $languageType = $request->header('Accept-Language');
            
            // Check if the user exists
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.',
                ], 404);
            }
    
            // Get all input data from the request
            $input = $request->all();
    
            // If password is present, hash it before updating
            if (isset($input['password'])) {
                $input['password'] = bcrypt($input['password']);
            }
    
            // Check if a file is uploaded for profile_url
            $request->validate([
                'file' => 'required|mimes:jpg,png,pdf|max:2048',
            ]);
        
            if ($request->file()) {
                $fileName = time().'_'.$request->file->getClientOriginalName();
                $filePath = $request->file('file')->storeAs('images', $fileName, 'public');
        
                return response()->json(['success'=>'File uploaded successfully', 'filePath' => $filePath]);
            }
    
            // Update the user with the input data
            $user->update($input);
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
    
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => $user, // Return updated user data
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
