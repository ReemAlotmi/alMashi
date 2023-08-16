<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\DriverRate;
use App\Models\RequestRide;
use Illuminate\Http\Request;
use App\Models\PassengerRide;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RideController extends Controller
{
    public function newRide(Request $request){
        $validateUser = Validator::make($request->all(), 
                [
                    'departure' => 'required',
                    'destination' => 'required',
                    'price' => 'required|numeric',
                    'time' => 'nullable|date_format:H:i:s'
                ]);
        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
        try{
            $user = User::where('id',auth()->user()->id)->first();
            
            //must check if the user is actually a driver 
            if(!($user->is_driver)){
                return response()->json([
                    'status' => false,
                    'message' => 'You need to register your car information before initiating a ride!'
                ], 401);
            }
            
            //must check if this user has a ride that is waiting/active right now
            $activeRide= Ride::where('user_id', $user->id)->where('status', ['waiting', 'active'])->first();
            if($activeRide){
                return response()->json([
                    'status' => false,
                    'message' => 'You can\'t initiate a ride because you have an active ride!'
                ], 401);
            }

            //must check if this user reserved a ride right now from the request/passenger ride tables
            $reservedRide= PassengerRide::where('user_id', $user->id)->where('status', 'active')->first();
            $requestRide= RequestRide::where('user_id', $user->id)->where('status', 'waiting')->first();
            if($reservedRide || $requestRide){
                return response()->json([
                    'status' => false,
                    'message' => "You can't initiate a ride because you have reserved a ride!"
                ], 401); 
            }

            //must validate the input fields
            $ride= new Ride();
            if(empty($request->time)){
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
            //need to modify the rating and show it as driver rating
            $rides = Ride::where('status', 'waiting')->get();
            $rides = DB::table('rides')
                    ->join('users', 'users.id', '=', 'rides.user_id')
                    ->where('rides.status', '=', 'waiting')
                    ->select('users.profile_img', 'users.name', 'users.rating', 'rides.price', 'rides.destination', 'rides.id')
                    ->get(); 
            return response()->json([
                'status' => true,
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
            ], 200);
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
            $validateUser = Validator::make($request->all(), 
            [
                'ride_id' => 'required|numeric',    
            ]);
    
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $ride= Ride::where('id', $request->ride_id)->first();
            $user = User::where('id', $ride->user_id)->first();

            return response()->json([
                'status' => true,
                'profile_img' => $user->profile_img,
                'name' => $user->name,
                'Rating' => Helper::getDriverRating($user->id),
                'comments' => DriverRate::where('driver_id',  $ride->user_id)->pluck('comment')
            ], 200);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function editPrice(Request $request){
        try{
            $validateUser = Validator::make($request->all(), 
            [
                'ride_id' => 'required|numeric',    
            ]);
    
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();
            $rqst= RequestRide::find($request->request_id);
            $ride= Ride::find($rqst->ride_id);
            
            if($user->id == $ride->user_id){
                //maybee this user already got accepted so we check if there's a passenger ride that is created and active with this user id and ride id
                $psngr = PassengerRide::where('user_id', $rqst->user_id)->where('ride_id', $request->ride_id)->where('status', 'active')->first();
                if(!$psngr){
                    //add the ride information to the passenger ride
                    PassengerRide::create([
                        'user_id' => $rqst->user_id,
                        'ride_id' => $request->ride_id,
                        'cost' => $ride->price,
                        'departure' => $rqst->departure,
                        'destination' => $rqst->destination,
                    ]); //by def the passenger ride status is set to active
                }
                //add the ride information to the passenger ride        
                PassengerRide::create([
                    'user_id' => $rqst->user_id,
                    'ride_id' => $ride->id,
                    'cost' => $request->cost,
                    'departure' => $rqst->departure,
                    'destination' => $rqst->destination,
                ]); //by def the passenger ride status is set to active
                
                //changing the request status to accepted
                $rqst->update(['status' => 'accepted']);

                //changing the ride status to active
                $ride->update(['status' => 'active']);
                
                return response()->json([
                    'status' => true,
                    'message' => "Price changed successfully"
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Only the driver for this ride can edit the price"
            ], 401);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function acceptRide(Request $request){
        try{
            $validateUser = Validator::make($request->all(), 
            [
                'ride_id' => 'required|numeric',  
                'request_id' => 'required|numeric',    
            ]);
    
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();
            $rqst= RequestRide::find($request->request_id);
            $ride= Ride::find($request->ride_id);
            
            
            if($user->id == $ride->user_id){
                //maybee this user already got accepted so we check if there's a passenger ride that is created and active with this user id and ride id
                $psngr = PassengerRide::where('user_id', $rqst->user_id)->where('ride_id', $request->ride_id)->where('status', 'active')->first();
                if(!$psngr){
                    //add the ride information to the passenger ride
                    PassengerRide::create([
                        'user_id' => $rqst->user_id,
                        'ride_id' => $request->ride_id,
                        'cost' => $ride->price,
                        'departure' => $rqst->departure,
                        'destination' => $rqst->destination,
                    ]); //by def the passenger ride status is set to active
                }
                
                //changing the request status to accepted
                $rqst->update(['status' => 'accepted']);

                //changing the ride status to active
                $ride->update(['status' => 'active']);
                
                return response()->json([
                    'status' => true,
                    'message' => "Ride request accepted successfully"
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Only the driver for this ride can accept the request"
            ], 401);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function activateRide(){
        try{
            $user = auth()->user();
            $ride= Ride::where('user_id', $user->id)->where('status', 'active')->first();
            $psngr= $ride->passengerRide;


            return response()->json([
                'status' => true,
                'name' => $ride->passengerRide->user->name,
                'profile_img' => $ride->passengerRide->user->profile_img,
                'rating' => Helper::getPassengerRating($psngr->user_id),
                'NoPassenger' => 1,
                'departure' => $psngr->departure,
                'destination' => $psngr->destination,
            ], 200);
            
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function terminate(Request $request){
        try{
            $validateUser = Validator::make($request->all(), 
            [
                'ride_id' => 'required|numeric'  
            ]);
    
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();
            $ride = Ride::where('id', $request->ride_id)->first();
            $rqst = RequestRide::where('ride_id', $ride->id)->where('status', 'accepted')->first();
            $psnger = PassengerRide::where('user_id', $rqst->user_id)->where('status', 'active')->first();
            
            if($user->id !== $ride->user_id){
                return response()->json([
                    'status' => false,
                    'message' => "Only the driver of this ride can terminate the ride"
                ], 401);
            }

            $psnger->update(['status' => 'terminated']);
            $rqst->update(['status' => 'terminated']);
            $ride->update(['status' => 'terminated']);

            return response()->json([
                'status' => true,
                'message' => 'Ride terminated successfully'
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
