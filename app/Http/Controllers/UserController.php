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
        // Find the user by ID
        $user = User::find($id);

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

        return response()->json([
            'status' => true,
            'message' => 'User updated successfully.',
            'data' => $user, // Return updated user data
        ]);
    }
    public function destroy($id)
    {
     
        $user = User::findOrFail($id);

     
        $user->delete();

        return response()->json([
            'status' => true,
            'message' => 'User Deleted Successfully',
            'data' => $user, 
        ]);
    }
}
