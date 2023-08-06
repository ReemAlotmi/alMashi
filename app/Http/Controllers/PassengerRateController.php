<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use Illuminate\Http\Request;
use App\Models\PassengerRate;
use App\Models\PassengerRide;
use App\Http\Controllers\Controller;

class PassengerRateController extends Controller
{
    //ratePassenger
    public function ratePassenger(Request $request){
        try{
            $user = auth()->user();//the driver
            $passengeride = PassengerRide::where('ride_id', $request->ride_id)->first();

            $rating = new PassengerRate();
            $rating->passenger_id = $passengeride ->user_id;
            $rating->rate = $request->rate;
            $rating->comment = $request->comment;
            $rating->ride_id = $request->ride_id;
            $rating->driver_id = $user->id; //the driver which is the current usr who will rate the passenger
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
