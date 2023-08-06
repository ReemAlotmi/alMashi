<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use App\Models\User;
use App\Models\DriverRate;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RideController extends Controller
{
    public function newRide(Request $request){
        try{
            $user = User::where('id',auth()->user()->id)->first();
            //must check if the user is actually a driver --handled in front-end?--
            if(!($user->is_driver)){
                response()->json([
                    'status' => false,
                    'message' => 'You need to register your car information before initiating a ride!'
                ], 500);
            }
            
            //must checks if this user has a ride that is active right now
            $activeRide= Ride::where('user_id', $user->id)->where('status', ['waiting', 'active'])->first();
            if($activeRide){
                return response()->json([
                    'status' => false,
                    'message' => 'You can\'t initiate a ride because you have an active ride!'
                ], 500);
            }

            //must validate the input fields

            $ride= new Ride();
            if(empty($request->time) ){
                $ride->time = Carbon::now();
            }
            else{
                $ride->time = $request->time;
            }
            $ride->user_id = $user->id;
            $ride->price = $request->price; 
            $ride->departure = $request->departure;
            $ride->destination = $request->destination;   
            $ride->save();

            return response()->json(
                [
                    'status' => true,
                    'message' => 'ride created successfully',
                    'ride id' => $ride->id 
                ], 200);

           
        }
        catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    
    }

    public function allRides(){ //returns all rides that is in waiting status
        try{
            $rides = Ride::where('status', 'waiting')->get();
            $rides = DB::table('rides')
                    ->join('users', 'users.id', '=', 'rides.user_id')
                    ->where('rides.status', '=', 'waiting')
                    ->select('users.profile_img', 'users.name', 'users.rating', 'rides.price', 'rides.destination', 'rides.id')
                    ->get();
            //the distance calculation code 
            return response()->json([
                'status' => true,
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

    
    public function index(){ //returns all rides that is in waiting status
        try{
            $rides = Ride::all();
            $rides = DB::table('rides')
                    ->join('users', 'users.id', '=', 'rides.user_id')
                    ->select('users.profile_img', 'users.name', 'users.rating', 'rides.price', 'rides.destination','rides.status', 'rides.id')
                    ->get();
            //the distance calculation code 
            return response()->json([
                'status' => true,
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

    public function driverInfo(Request $request){
        try{
            $ride= Ride::where('id', $request->ride_id)->first();
            $user = User::where('id', $ride->user_id)->first();
            $comments = DriverRate::select('comment')->where('driver_id', $request->user_id)->get();

            return response()->json([
                'status' => true,
                'profile_img' => $user->profile_img,
                'name' => $user->name,
                'Rating' => $user->rating,
                'comments' => $comments 
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
