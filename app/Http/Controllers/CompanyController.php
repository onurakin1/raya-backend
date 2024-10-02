<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use Carbon\Carbon;

class CompanyController extends Controller
{

    public function index() {}


    public function store(Request $request)
    {
        try {
            $today = Carbon::today();
            $createdCompany = Company::create([
                'name' => $request->name,
                'created_at' => $today

            ]);
            return response()->json([
                'success' => true,
                'message' => 'Company created successfully!',

                'data' => $createdCompany
            ], 201);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create channel: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function show(Company $intro)
    {
        //
    }


    public function update(Request $request, Company $intro)
    {
        //
    }


    public function destroy(Company $intro)
    {
        //
    }
}
