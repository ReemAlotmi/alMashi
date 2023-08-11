<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Throwable;
use App\Models\Ride;
use App\Models\User;
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
            /////the ride must be set to terminated so they can rate
            $ride= Ride::find($request->ride_id);
            if($ride->status !== "terminated"){
                return response()->json([
                    'status' => false,
                    'message' => 'can\'t rate unless ride is terminated'
                ], 401);
            }
            //validate the fields
            $validateUser = Validator::make($request->all(), 
                [
                    'rate' => 'required|numeric|lte:5',
                    'ride_id' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }
            $user = auth()->user();//the driver
            $passengeride = PassengerRide::where('ride_id', $request->ride_id)->first();

            $rating = new PassengerRate();
            $rating->passenger_id = $passengeride->user_id;
            $rating->rate = $request->rate;
            $rating->comment = $request->comment;
            $rating->ride_id = $request->ride_id;
            $rating->driver_id = $user->id; //the driver which is the current usr who will rate the passenger
            $rating->save();

            //code for calculating the total rats for this passenger
            $rates= PassengerRate::where('passenger_id', $passengeride->user_id)->get();
            $totalRates= $rates->sum('rate');
            
            $thisPassenger= User::find($passengeride ->user_id);
            $thisPassenger->rating = $rates->count() > 0 ? $totalRates / $rates->count() : 0;
            $passengeride->save();



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


    public function getPRate(Request $request){
        try{
            //validate the fields
            $validateUser = Validator::make($request->all(), 
                [
                    'user_id' => 'required|numeric',
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }
            return response()->json([
                'status' => true,
                'rate' => PassengerRate::where('passenger_id', $request->user_id)->avg('rate')
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
