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
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;


class PassengerRateController extends Controller
{
    //ratePassenger
    public function ratePassenger(Request $request){
        try{
            $user = auth()->user();//the driver
            /////the ride must be set to terminated so they can rate
            $ride= Ride::find($request->ride_id);
            if($ride->status !== "terminated"){
                return response()->json([
                    'status' => false,
                    'message' => 'can\'t rate unless ride is terminated'
                ], 401);
            }
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

            $passengeride = PassengerRide::where('ride_id', $request->ride_id)->where('status', "terminated")->first();
           // return ($request->ride_id);
            PassengerRate::create([
                'passenger_id' => $passengeride->user_id,
                'rate' => $request->rate,
                'comment' => $request->comment,
                'ride_id' => $request->ride_id,
                'driver_id' => $user->id
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
    public function getPRate(Request $request){
        try{
            $rqst= RequestRide::where('id', $request->request_id)->where('status', 'waiting')->first;
            $psngr= User::find($rqst->user_id);
            $comments = PassengerRate::select('comment')->where('passenger_id', $psngr->user_id)->get();

            return response()->json([
                'status' => true,
                'profile_img' => $psngr->profile_img,
                'name' => $psngr->name,
                'Rating' => PassengerRate::where('passenger_id', $psngr->id)->avg('rate'),
                'comments' => $comments 
            ], 200);
            

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
                        // try{
                        //     //validate the fields
                        //     $validateUser = Validator::make($request->all(), 
                        //         [
                        //             'user_id' => 'required|numeric',
                        //         ]);

                        //         if($validateUser->fails()){
                        //             return response()->json([
                        //                 'status' => false,
                        //                 'message' => 'validation error',
                        //                 'errors' => $validateUser->errors()
                        //             ], 401);
                        //         }
                        //     return response()->json([
                        //         'status' => true,
                        //         'rate' => PassengerRate::where('passenger_id', $request->user_id)->avg('rate')
                        //     ], 200);
                            
                        // }
                        // catch (Throwable $th) {
                        //     return response()->json([
                        //         'status' => false,
                        //         'message' => $th->getMessage()
                        //     ], 500);
                        // }
    }
}
