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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class RequestRideController extends Controller
{
    public function passengerInfo(Request $request){
        // {"profile_img": " ",
        //     "name": "",
        //     "rate" : " "
        //     "comments": [] }
        try{
            $validateUser = Validator::make($request->all(), 
                [
                    'request_id' => 'required|numeric',    
                ]);
    
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            
            $rqst= RequestRide::where('id', $request->request_id)->where('status', 'waiting')->first();
            $psngr= User::find($rqst->user_id);

            return response()->json([
                'status' => true,
                'profile_img' => $psngr->profile_img,
                'name' => $psngr->name,
                'Rating' => PassengerRate::where('passenger_id', $psngr->id)->avg('rate'),
                'comments' => PassengerRate::where('passenger_id', $psngr->id)->pluck('comment')
            ], 200);
            

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
    public function requestRide(Request $request){
        try{
            $validateUser = Validator::make($request->all(), 
            [
                'departure' => 'required',
                'destination' => 'required',
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
            $rqst= RequestRide::where('user_id',$user->id)->where('status', ['waiting', 'accepted'])->get();
            if(count($rqst) != 0){
                return response()->json([
                    'status' => false,
                    'message' => "can't create multiple requests"
                ], 401);
            }
            RequestRide::create([
                'user_id' => $user->id,
                'ride_id' => $request->ride_id,
                'departure' => $request->departure,
                'destination' => $request->destination
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Request sent successfully'
            ], 200);

        }
        catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function requestStatus(Request $request){
        
        try{
            $validateUser = Validator::make($request->header(), 
            [
                'ride_id' => 'required',    
                'ride_id' => 'numeric'   
            ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = auth()->user();
            $rqst= RequestRide::where('user_id',$user->id)->where('ride_id', $request->header('ride_id'))->latest()->first();
            if($rqst->status == 'rejected'){
                return response()->json([
                    'status' => true,
                    'message' => 'Request canceled because its rejected'
                ], 200);
            }
            if($rqst->status == 'terminated'){
                return response()->json([
                    'status' => true,
                    'message' => 'Request terminated'
                ], 200);
            }
            if(($rqst->status == 'waiting') && Carbon::now()->gt($rqst->created_at->addMinutes(1))) {
                //it should delete the request because its timed out
                $rqst->status = 'terminated';
                $rqst->update();
                return response()->json([
                    'status' => true,
                    'message' => 'Request canceled because its timed out'
                ], 200);
            }

            if($rqst->status == 'accepted'){      
                return response()->json([
                'status' => true,
                'message' => 'Request accepted by driver'
            ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Request is still in waiting status'
            ], 200);    

        }
        catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function requestCancel(){
        try{
            $user = auth()->user();
            $rqst= RequestRide::where('user_id',$user->id)->where('status',['waiting', 'accepted'])->first();
            $passengeride = PassengerRide::where('user_id',$user->id)->where('ride_id', $rqst->ride_id)->first();

            if(!empty($rqst)) {
                if($rqst->status == 'accepted'){ //means this user has a passenger ride and it should be terminated
                    $passengeride->update(['status' => 'terminated']);
                }
                $rqst->update(['status' => 'terminated']); //either ways it will be terminated
                return response()->json([
                    'status' => true,
                    'message' => 'Request canceled successfully' 
                ], 200);
            }

            return response()->json([
                'status' => true,
                'message' => 'Request has already canceled'
            ], 200);

        }
        catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function myOrdersDriver(){//get all the requests for a specific ride
        // "name":"",
        // "profile_img": "",
        // "rating" : "',
        // "NoPassengers: "",
        // "distance:"",
        // "request_id: " " 

        try{
            $user = auth()->user();
            $ride = Ride::where('user_id', $user->id)->where('status', 'waiting')->first(); //get the ride id from here to search for the requests of this ride
            //return $ride;
            $rqsts= RequestRide::where('ride_id',$ride->id)->where('status', 'waiting')->get(); //get the requests that match the ride_id
            //return $rqsts;
            $data=[];
            foreach($rqsts as $rqst){
                //calculate the destinace here
                $distance = Helper::clacDistance($rqst->departure, $ride->departure);
                //dd($rqst->id, $ride->id);
                $user= User::find($rqst->user_id);
                array_push($data, [
                    "name" =>$user->name,
                    "profile_img" =>$user->profile_img,
                    "rating" =>Helper::getPassengerRating($user->id),
                    "NoPassengers" => 1,
                    "distance" => $distance,
                    "request_id" =>$rqst->id
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Orders for this ride',
                'data'=> $data
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
