<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use App\Models\User;
use App\Models\DriverRate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class DriverRateController extends Controller
{
    public function rateDriver(Request $request){
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

            $ride= Ride::find($request->ride_id);
            if($ride->status !== "terminated"){
                return response()->json([
                    'status' => false,
                    'message' => 'can\'t rate unless ride is terminated'
                ], 401);
            }

            $user = auth()->user();
            $ride = Ride::where('id', $request->ride_id)->first();

            $rating = new DriverRate();
            $rating->passenger_id = $user->id;
            $rating->rate = $request->rate;
            $rating->comment = $request->comment;
            $rating->ride_id = $request->ride_id;
            $rating->driver_id = $ride ->user_id;
            $rating->save();

            //code for calculating the total rates for this driver
            // $rates= DriverRate::where('driver_id', $ride->user_id)->get();
            // $totalRates= $rates->sum('rate');
            
            // $thisDriver= User::find($ride->user_id);
            // $thisDriver->rating = $rates->count() > 0 ? $totalRates / $rates->count() : 0;
            // $thisDriver->save();


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
