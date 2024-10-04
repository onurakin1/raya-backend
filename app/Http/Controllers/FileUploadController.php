<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
  

    public function upload(Request $request)
    {
        $validatedData = $request->validate([
            'file' => 'nullable|mimes:jpg,png,pdf|max:2048', // nullable ile isteğe bağlı hale getiriyoruz
            'name' => 'nullable|string|max:255', // nullable ile isteğe bağlı hale getiriyoruz
        ]);
    
        if ($request->file()) {
            $fileName = time().'_'.$request->file->getClientOriginalName();
            $filePath = $request->file('file')->storeAs('images', $fileName, 'public');
    
            return response()->json(['success'=>'File uploaded successfully', 'filePath' => $filePath, 'name' => $validatedData]);
        }
    
        // Additional logic (e.g., storing file information in the database)

      
    }
}
