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
            if ($request->hasFile('profile_url')) {
                $file = $request->file('profile_url');
    
                // Validate that the file is an image
                $request->validate([
                    'profile_url' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // max size 2MB
                ]);
    
                // Define the storage path for the image
                $destinationPath = 'uploads/profile_images';
    
                // Create a unique file name for the image
                $fileName = time() . '.' . $file->getClientOriginalExtension();
    
                // Move the image to the specified path
                $file->move(public_path($destinationPath), $fileName);
    
                // Save the profile image path in the input array
                $input['profile_url'] = $destinationPath . '/' . $fileName;
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
