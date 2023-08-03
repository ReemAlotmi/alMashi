<?php

namespace App\Http\Controllers;

use Throwable;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;

class OtpController extends Controller
{
    public function verify(Request $request){

        
        $user = User::find($request->user_id);
            $otp = Otp::where('user_id', $user->id)->first();

           //dd($request->random, $user,$otp );
           // dd($otp->random == (int)$request->random);
        

        try{

            
            
            if ($otp->random == (int)$request->random) {
                if(Carbon::now()->lt($otp->expired_at)){
                    $user->mobile_no_verified_at = Carbon::now();
                    $user->update();
                    return response()->json([
                        'status' => true,
                        'message' => 'User verified Successfully'
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
