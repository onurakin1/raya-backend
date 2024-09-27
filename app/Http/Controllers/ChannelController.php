<?php

namespace App\Http\Controllers;

use App\Models\MeetMe;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MeetMe::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming request data

    
        try {
            // Create a new meeting channel
            $createChannel = MeetMe::create([
                'exten' => $request->number,  // Use validated data
                'options' => 'qMm',
                'userpin' => "",
                'adminpin' => $request->password,
                'description' => $request->name,
                'joinmsg_id' => 0,
                'music' => 'inherit',
                'users' => 0
            ]);
    
            // Return success response with channel details
            return response()->json([
                'success' => true,
                'message' => 'Channel created successfully!',
                'createdChannel' => $createChannel,
            ], 201);
    
        } catch (\Exception $e) {
            // Handle any errors that occur during channel creation
            return response()->json([
                'success' => false,
                'message' => 'Failed to create channel: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MeetMe $spi)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MeetMe $spi)
    {
        //
    }
}
