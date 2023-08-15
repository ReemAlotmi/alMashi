<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Helpers\Helper;
use App\Models\RequestRide;
use Illuminate\Http\Request;
use App\Models\PassengerRide;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class PassengerRideController extends Controller
{
    public function reserveRide(Request $request){
        //input fields 
        //departure:{(86.8457), (52.417)}
        //destination:{(86.8450), (52.420)}
        try{
            $validateUser = Validator::make($request->all(), 
                [
                    'departure' => 'required',
                    'desttination' => 'required'
                ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();  
            
            //must check if this user has an initiated ride at this moment
            $ride = Ride::where('user_id', $user->id)->where('status', ['waiting', 'active'])->first();
            if($ride){
                return response()->json([
                    'status' => false,
                    'message' => "can't reserve a ride bacause you have an active ride"
                ], 401);
            }

            
            //get all the rides 
            //compare the departure of all of these rides with the user deprature
            //get only the rides that is distant by 10km or less 
            
            $availbleRides= Ride::where('status', ['waiting'])->get();
            $rides=[];
            foreach($availbleRides as $availbleRide){ 
                $distance = Helper::clacDistance($availbleRide->departure, $request->departure);
                if($distance <= 10){
                    array_push($rides, [
                        "name" =>$availbleRide->user->name,
                        "profile_img" =>$availbleRide->user->profile_img,
                        "rating" =>$availbleRide->user->rating,
                        "price" => $availbleRide->price,
                        "distance" => $distance,
                        "destination" =>$availbleRide->destination,
                        "ride_id" => $availbleRide->id
                    ]);
                }  
            }

            return response()->json([
                'status' => true,
                'message' => "Passenger ride created",
                'Rides' => $rides
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
