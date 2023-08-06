<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use App\Models\DriverRate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DriverRateController extends Controller
{
    public function rateDriver(Request $request){
        try{
            $user = auth()->user();
            $ride = Ride::where('id', $request->ride_id)->first();

            $rating = new DriverRate();
            $rating->passenger_id = $user->id;
            $rating->rate = $request->rate;
            $rating->comment = $request->comment;
            $rating->ride_id = $request->ride_id;
            $rating->driver_id = $ride ->user_id;
            // return $rating;
            $rating->save();

            return response()->json([
                'status' => true,
                'message' => 'Rate registered successfully'
            ], 200);
            
        }
        catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
