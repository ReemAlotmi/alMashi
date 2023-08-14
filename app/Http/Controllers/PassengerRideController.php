<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Helpers\Helper;
use App\Models\RequestRide;
use Illuminate\Http\Request;
use App\Models\PassengerRide;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;


class PassengerRideController extends Controller
{
    public function reserveRide(Request $request){
        //input fields 
        //departure:{(86.8457), (52.417)}
        //destination:{(86.8450), (52.420)}
        try{
            $user = auth()->user();  

            // "profile_img": "https://www.gooFYADFQAAAAAdAAAAABAE",
            // "name": "Reem",
            // "rating": 3,
            // "price": 10,
            // "distance": " "     
            // "destination": "dskjfdsfjk"
            // "ride_id": 2

            
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
