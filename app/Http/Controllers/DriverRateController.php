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
            $user = auth()->user();
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

             
            DriverRate::create([
                'passenger_id' => $user->id,
                'rate' => $request->rate,
                'comment' => $request->comment,
                'ride_id' => $request->ride_id,
                'driver_id' => $ride->user_id,
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
