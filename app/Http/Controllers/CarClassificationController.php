<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarClassification;
use App\Http\Controllers\Controller;

class CarClassificationController extends Controller
{
    public function index()
    {
        try{
            $classifications = CarClassification::all();
            return response()->json(
                [
                    'status' => true,
                    'data' => $classifications   
                ], 200);
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    }
}
