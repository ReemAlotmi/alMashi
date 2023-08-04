<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;

class OtpController extends Controller
{
    public function verify(Request $request){

        // $user = auth()->user();
        
        $user = User::find($request->user_id);
        $otp = Otp::where('user_id', $user->id)->first();
        
        
           //dd($request->random, $user,$otp );
           // dd($otp->random == (int)$request->random);
        

        try{            
            if ($otp->random == (int)$request->random) {
                if(Carbon::now()->lt($otp->expired_at)){
                    
                    $user->mobile_no_verified_at = Carbon::now();
                    $user->save();

                    // $token = PersonalAccessToken::where('tokenable_id', $user->id)->first();

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

        $user = User::where('id', auth()->user()->id)->first();
        $otp = Otp::where('user_id', $user->id)->first();
        try{            
            if ($otp->random == (int)$request->random) {
                if(Carbon::now()->lt($otp->expired_at)){
                    
                    $user->mobile_no_verified_at = Carbon::now();
                    $user->mobile_no = $request->mobile_no;
                    $user->update();

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
