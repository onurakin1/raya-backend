<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

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
    public function update(Request $request, $id)
    {
        try {
            // Find the user by ID
            $user = User::find($id);
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
    public function destroy(Request $request, $id)
    {

        try{
            $user = User::findOrFail($id);
            $languageType = $request->header('Accept-Language');

            $user->delete();
            $successMessage = ($languageType === 'tr') ? 'Başarılı' : 'Successfully';
            return response()->json([
                'status' => true,
                'message' => $successMessage,
                'data' => $user,
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
