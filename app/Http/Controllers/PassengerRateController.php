<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\RequestRide;
use Illuminate\Http\Request;
use App\Models\PassengerRate;
use App\Models\PassengerRide;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class PassengerRateController extends Controller
{
    //ratePassenger
    public function ratePassenger(Request $request){
        try{
            $validateUser = Validator::make($request->all(), 
            [
                'rate' => 'required|numeric|lte:5',
                'ride_id' => 'required',
                'comment' => 'nullable'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = auth()->user();//the driver
            //the ride must be set to terminated so they can rate
            $ride= Ride::find($request->ride_id);
            if($ride->status !== "terminated"){
                return response()->json([
                    'status' => false,
                    'message' => "can't rate unless ride is terminated"
                ], 401);
            }
            
            $passengeride = PassengerRide::where('ride_id', $request->ride_id)->where('status', "terminated")->latest()->first();
            
            //make sure that the user hasn't already rated this ride's passenger 
            $rated = PassengerRate::where('driver_id', $user->id)->where('ride_id', $request->ride_id)->first();
            if(empty($rated)){
                PassengerRate::create([
                    'passenger_id' => $passengeride->user_id,
                    'rate' => $request->rate,
                    'comment' => $request->comment,
                    'ride_id' => $request->ride_id,
                    'driver_id' => $user->id
                ]);
            }
            
            $rated->update([
                'passenger_id' => $passengeride->user_id,
                'rate' => $request->rate,
                'comment' => $request->comment,
                'ride_id' => $request->ride_id,
                'driver_id' => $user->id
            ]);

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
