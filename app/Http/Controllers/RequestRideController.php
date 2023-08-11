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

class RequestRideController extends Controller
{
    public function passengerInfo(Request $request){
        // {"profile_img": " ",
        //     "name": "",
        //     "rate" : " "
        //     "comments": [] }
        
        try{
            $rqst= RequestRide::find($request->request_id);
            $psngr= User::find($rqst->user_id);
            $comments = PassengerRate::select('comment')->where('passenger_id', $psngr->id)->get();

            return response()->json([
                'status' => true,
                'profile_img' => $psngr->profile_img,
                'name' => $psngr->name,
                'Rating' => Helper::getPassengerRating($psngr->id),
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
    public function requestRide(Request $request){
        try{
            $user = auth()->user();
            $ride = PassengerRide::where('user_id',$user->id)->first();

            $rqst= RequestRide::where('user_id',$user->id)->where('status', ['waiting', 'accepted'])->get();
            if(count($rqst) != 0){
                return response()->json([
                    'status' => false,
                    'message' => 'can\'t create multiple requests'
                ], 401);
            }

            $rqst= new RequestRide();
            $rqst->user_id = $user->id;
            $rqst->ride_id = $request->ride_id;
            $rqst->departure = $request->departure;
            $rqst->destination = $request->destination;
            $rqst->passenger_ride_id = $ride->id;
            $rqst->save();

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

    public function requestStatus(){
        try{
            $user = auth()->user();
            $passengeride = PassengerRide::where('user_id',$user->id)->first();
            $rqst= RequestRide::where('user_id',$user->id)->first();

            if($rqst->status == 'rejected'){
                //it should delete the request because it got rejected
                $rqst->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Request canceled because its rejected'
                ], 200);
            }
            if(($rqst->status == 'waiting') && Carbon::now()->gt($rqst->created_at->addMinutes(1))) {
                //it should delete the request because its timed out 
                $rqst->delete();
                return response()->json([
                    'status' => true,
                    'message' => 'Request canceled because its timed out'
                ], 200);
            }

            if($rqst->status == 'accepted'){
                $passengeride->ride_id = $rqst->ride_id;
                $passengeride->save();
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
            $passengeride = PassengerRide::where('user_id',$user->id)->first();
            $rqst= RequestRide::where('user_id',$user->id)->first();

            if(!empty($rqst)) {
                if($rqst->status == 'accepted'){
                    $passengeride->update(['ride_id' => null, 'cost' => null]);
                }
                $rqst->delete();
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
                $distance = Helper::clacDistance($ride->departure, $rqst->departure);
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
