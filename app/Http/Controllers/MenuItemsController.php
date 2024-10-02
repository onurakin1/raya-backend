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
        // Check if the user has valid tokens
        $user = $request->user();


        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Your session has expired or your token has been deleted. Please log in again.',
            ], 401); // Return 401 Unauthorized status
        }
    
        $rooms = Rooms::where('created_by', $user->id)->first();
        $asteriskUsers = RoomUsers::where('user_id', $user->id)->first();
        $menuItems = MenuItems::all();
    
        $userId = $user->id;
        $companyToGuides = Company::whereHas('guides', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->get();
    
        // Convert menu items collection to array with desired structure
        $menu = $menuItems->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->title,
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
                'user' =>  $user,
                'room' => $rooms,
                'menu' => $menu,
                'isabel' => [
                    'username' => $asteriskUsers->name,
                    'password' => $asteriskUsers->password,
                    'link' => "https://pbx.limonisthost.com/"
                ],
                'company' => $companyToGuides,
            ],
            'message' => 'The operation has been successfully completed!',
        ]);
    }
    
}
