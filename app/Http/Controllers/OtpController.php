<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class OtpController extends Controller
{
    public function verify(Request $request){
        try{  
            $validateUser = Validator::make($request->all(), 
            [
                'user_id' => 'required|numeric',
                'random' => 'required|numeric|digits:4'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }   
            
            $user = User::find($request->user_id);
            $otp = Otp::where('user_id', $user->id)->first();

            if ($otp->random == (int)$request->random) {

                if(Carbon::now()->lt($otp->expired_at)){
                    
                    $user->mobile_no_verified_at = Carbon::now();
                    $user->save();

                    return response()->json([
                        'status' => true,
                        'message' => 'User verified Successfully',
                        'token' => $user->createToken("API TOKEN")->plainTextToken,
                    ], 200);
                }
                else{
                    return response()->json([
                        'status' => false,
                        'message' => 'otp has expired',
                        ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'not matched otps',
                    ], 401);
            }   

        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function verifyNew(Request $request){//must send the new mobile_no in the body with the otp
        try{ 
            $validateUser = Validator::make($request->all(), 
            [
                'mobile_no' => 'required|numeric|digits_between:10,12',
                'random' => 'required|numeric|digits:4'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            } 
            
            $user = User::where('id', auth()->user()->id)->first();
            $otp = Otp::where('user_id', $user->id)->first();
            
            if ($otp->random == (int)$request->random) {
                if(Carbon::now()->lt($otp->expired_at)){
                    $user->update([
                        'mobile_no_verified_at' => Carbon::now(),
                        'mobile_no' => $request->mobile_no
                    ]);
                    return response()->json([
                        'status' => true,
                        'message' => 'mobile number updated Successfully'
                    ], 200);
                }
                else{
                    return response()->json([
                        'status' => false,
                        'message' => 'otp has expired',
                        ], 401);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'not matched otps',
                    ], 401);
            }

        } catch (Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }   
    }
}
