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

class RideController extends Controller
{
    public function newRide(Request $request){
        try{
            $user = User::where('id',auth()->user()->id)->first();
            //must check if the user is actually a driver --handled in front-end?--
            //dd(!($user->is_driver));
            if(!($user->is_driver)){
                return response()->json([
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
            $ride= Ride::where('id', $request->ride_id)->first();
            $user = User::where('id', $ride->user_id)->first();
            $comments = DriverRate::select('comment')->where('driver_id', $request->user_id)->get();

            return response()->json([
                'status' => true,
                'profile_img' => $user->profile_img,
                'name' => $user->name,
                'Rating' => Helper::getDriverRating($user->id),
                'comments' => $comments 
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
            $user = auth()->user();
            $rqst= RequestRide::find($request->request_id);
            $ride= Ride::find($rqst->ride_id);
            
            if($user->id == $ride->user_id){
                //edit the price
                $psngr= PassengerRide::where('id',$rqst->passenger_ride_id)->first();
                $psngr->cost = $request->cost;
                $psngr->ride_id = $ride->id;
                $psngr->save();

                //editing the price means that the driver has accepted this request
                $rqst->status = "accepted";
                $rqst->save();


                return response()->json([
                    'status' => true,
                    'message' => "Price changed successfully"
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Only the driver for this ride can edit the price"
            ], 500);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function acceptRide(Request $request){
        // ride_id:
        // request_id:
        try{
            $user = auth()->user();
            $rqst= RequestRide::find($request->request_id);
            $ride= Ride::find($request->ride_id);
            
            
            if($user->id == $ride->user_id){
                //add the ride information to the passenger ride
                $psngr= PassengerRide::where('id',$rqst->passenger_ride_id)->first();
                $psngr->cost = $ride->price;
                $psngr->ride_id = $ride->id;
                $psngr->save();

                //editing the price means that the driver has accepted this request
                $rqst->status = "accepted";
                $rqst->save();

                //changing the ride status to active
                $ride->status = "active";
                $ride->save();


                return response()->json([
                    'status' => true,
                    'message' => "Ride request accepted successfully"
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => "Only the driver for this ride can accept the request"
            ], 500);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function activateRide(){

        // "departure": " ",
        // "destination": " ",
        // "NoPassengers": " "
        //user name
        //img
        //rating

        try{
            $user = auth()->user();
            $ride= Ride::where('user_id', $user->id)->where('status', 'active')->first();
            $psngr= $ride->passengerRide;


            return response()->json([
                'status' => false,
                'name' => $ride->passengerRide->user->name,
                'profile_img' => $ride->passengerRide->user->profile_img,
                'rating' => Helper::getPassengerRating($psngr->user_id),
                'NoPassenger' => 1,
                'departure' => $psngr->departure,
                'destination' => $psngr->destination,
            ], 500);
            
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
            $user = auth()->user();
            $ride= Ride::where('id', $request->ride_id)->first();
            $rqst= RequestRide::where('ride_id', $ride->id)->where('status', 'accepted')->first();
            //should check if this ride driver is the same as the user who wants to terminate this ride?? authorization?
            if($user->id !== $ride->user_id){
                return response()->json([
                    'status' => false,
                    'message' => "Only the driver of this ride can terminate the ride "
                ], 500);
            }
            $rqst->status = 'terminated';
            $rqst->save();
            $ride->status = 'terminated';
            $ride->save();
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
