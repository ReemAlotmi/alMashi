<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class RideController extends Controller
{
    public function newRide(Request $request, $id){

        try{
            $user = User::find($id);

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
}
