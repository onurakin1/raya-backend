<?php

namespace App\Http\Controllers;

use App\Models\AppControls;
use Illuminate\Http\Request;


class AppControlsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $app_control = AppControls::where('is_active', 1)->first();
    
        // if ($app_control) {
        //     // İçeriği dönüştürme işlemi
        //     $response = [
        //         'status' => true,
        //         'message' => 'The operation has been successfully completed.',
        //         'data' => [
        //             'status' => 'VERSION',
        //             'data' => [
        //                 'contents' => $app_control->contents ? json_decode($app_control->contents, true)['en'] : '',
        //                 'button_title' => $app_control->button_title ? json_decode($app_control->button_title, true)['en'] : '',
        //                 'update_link' => $app_control->update_link,
        //                 'button_action_type' => $app_control->button_action_type,
        //                 'is_can_close' => $app_control->is_can_close,
        //             ]
            
        //         ]
        //     ];
        //     return response()->json($response);
        // }
    
        // return response()->json(['status' => false, 'message' => 'No active version found.']);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function checkVersion(Request $request)
    {
       
        $version = $request->query('version');
        $type = $request->query('type');


        if (!$version || !$type) {
            return response()->json([
                'status' => false,
                'message' => 'Version and type parameters are required.'
            ], 400);
        }

       
        $app_control = AppControls::where('version', $version)->where('type', $type)->first();

  
        if ($app_control) {
            return response()->json([
                'status' => true,
                'message' => 'The operation has been successfully completed.',
                'data' => [
                    'status' => 'VERSION',
                    'data' => [
                        'contents' => $app_control->contents, 
                        'button_title' => $app_control->button_title,
                        'update_link' => $app_control->update_link,
                        'button_action_type' => $app_control->button_action_type,
                        'is_can_close' => $app_control->is_can_close,
                    ],
                 
                ]
            ], 200);
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Version or type not found.',
            'data' => [
               'status' => 'NOT YET'
            ]
        ], 404);
    }


    public function show(Request $request)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AppControls $app_control)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AppControls $app_control)
    {
        //
    }
}
