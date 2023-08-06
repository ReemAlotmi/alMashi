<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use Illuminate\Http\Request;
use App\Models\PassengerRide;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class PassengerRideController extends Controller
{
    public function reserveRide(Request $request){

        try{
            $user = auth()->user();

            //must checks if this user has a ride that is active right now
            $activeRide= Ride::where('user_id', $user->id)->where('status', ['waiting', 'active'])->first();
            
            if($activeRide){
                return response()->json([
                    'status' => false,
                    'message' => 'You can\'t initiate a ride because you have an active ride!'
                ], 500);
            }

            $passengeRide= new PassengerRide();

            $passengeRide->user_id = $user->id;
            $passengeRide->departure = $request->departure;
            $passengeRide->destination = $request->destination;
            // $passengeRide->cost = 0; //NEW MIGRATION TO SET IT TO NULLABLE
            // $passengeRide->ride_id = 0; //NEW MIGRATION TO SET IT TO NULLABLE
            $passengeRide->save();

            $rides = DB::table('rides')
                    ->join('users', 'users.id', '=', 'rides.user_id')
                    ->where('rides.status', '=', 'waiting')
                    ->select('users.profile_img', 'users.name', 'users.rating', 'rides.price', 'rides.destination', 'rides.id')
                    ->get();
            //the distance calculation code//

            return response()->json([
                'status' => true,
                'message' => "Passenger ride created",
                'Rides' => $rides
            ], 500);
            

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }

    }
}
