<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Rooms;
use App\Models\MenuItems;
use App\Models\RoomUsers;
use Illuminate\Http\Request;

class MenuItemsController extends Controller
{
    public function UserSelf(Request $request)
    {
        $languageType = $request->header('Accept-Language');  // Dil türünü alıyoruz
        $user = $request->user();
    
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Your session has expired or your token has been deleted. Please log in again.',
            ], 401); // Return 401 Unauthorized status
        }
    
        // Kullanıcıya ait odalar ve Asterisk kullanıcı bilgilerini alıyoruz
        $rooms = Rooms::where('created_by', $user->id)->first();
        $asteriskUsers = RoomUsers::where('user_id', $user->id)->first();
        
        // Tüm menü öğelerini alıyoruz
        $menuItems = MenuItems::all();
    
        $userId = $user->id;
        // Kullanıcıya ait şirketleri alıyoruz
        $companyToGuides = Company::whereHas('guides', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    
        // Menü öğeleri için dil desteği eklenmiş bir yapı
        $menu = $menuItems->map(function ($item) use ($languageType) {
            // Menü başlıklarında dil kontrolü
            $menuTitles = json_decode($item->title, true);
            $menuName = $menuTitles[$languageType] ?? $menuTitles['en'] ?? ''; // Eğer dilde değer yoksa, İngilizceyi varsayılan alıyoruz
    
            return [
                'id' => $item->id,
                'name' => $menuName,  // Dil tercihi ile ayarlanmış menü adı
                'description' => $item->value,
                'seque' => $item->seque,
                'image_link' => $item->icon,
                'type' => $item->type,
                'switch_value' => 0
            ];
        });
    
        return response()->json([
            'status' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'firstname' => $user->name,
                    'lastname' => $user->last_name,
                    'phone_number' => $user->phone_number,
                    'username' => $user->email,
                    'photo_link' => $user->photo_link ?: "",  // Boşsa "" olarak dönüyor
                    'room' => $rooms,
                    'menu' => $menu,  // Dil desteği eklenmiş menü öğeleri
    
                    'isabel' => [
                        'username' => $asteriskUsers->name,
                        'password' => $asteriskUsers->password,
                        'link' => "https://pbx.limonisthost.com/"
                    ],
                    'company' => $companyToGuides,  // Kullanıcıya atanmış şirketler
                ]
            ],
            'message' => 'The operation has been successfully completed!',
        ]);
    }
}
