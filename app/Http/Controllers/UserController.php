<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CarClassification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{


    public function viewProfile(Request $request){
        //$token=;
        //User::where('id', $request->user_id)->first();
        //return $user;
        //auth()->user();
        // $token = PersonalAccessToken::where('token', $request->header('token'))->first();
        // return  $request->header('token');
        // $user = $token->tokenable;
        try{
            $user = auth()->user();
            if($user->is_driver){
                $car = Car::where('user_id', $user->id)->first();
                $carClassification= CarClassification::where('id',$car->classification_id)->first();

                return response()->json(
                [
                    'status' => true,
                    'name' => $user->name,   
                    'mobile_no' => $user->mobile_no,   
                    'plate' => $car->plate,   
                    'classification' => $carClassification->classification,
                    'capacity' => $car->capacity,   
                    'type' => $car->type,   
                    'color' => $car->color,   
                    'is_driver' => $user->is_driver,   
                ], 200);
            }
            return response()->json(
                [
                    'status' => true,
                    'name' => $user->name,   
                    'mobile_no' => $user->mobile_no,   
                    'plate' => "",   
                    'classification' => "",   
                    'capacity' => "",   
                    'type' => "",   
                    'color' => "",   
                    'is_driver' => $user->is_driver,   
                ], 200);  
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function editProfile(Request $request){
        try{
            //$user = auth()->user();
            $user = User::where('id', auth()->user()->id)->first();
            //return $user;

            $user->profile_img = $request->profile_img ?? $user->profile_img;
            $user->name = $request->name ?? $user->name ;
            
            if($user->is_driver){ //if the user is driver and wants to update his car info
                $car = Car::where('user_id', $user->id)->first();

                if(!empty($request->classification)){
                    $carClassification= CarClassification::where('classification',$request->classification)->first();
                     $car->classification_id = $carClassification->id ; //the car calssifications table get the id of the classification that received from the user             
                }
                $car->type = $request->type ?? $car->type;
                $car->capacity = $request->capacity ?? $car->capacity;
                $car->color = $request->color ?? $car->color;
                $car->plate = $request->plate ?? $car->plate;

                $car->update();
            }

            if(!($user->is_driver) && !empty($request->classification)){//if the user is not a driver but wants to be one by adding his car info

                $validateUser = Validator::make($request->all(), 
                [
                    'classification' => 'required',
                    'type' => 'required',
                    'capacity' => 'required',
                    'color' => 'required',
                    'plate' => 'required'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }


                $carClassification= CarClassification::where('classification',$request->classification)->first();
                $car = new Car();
                $car->user_id = $user->id ;
                $car->classification_id = $carClassification->id ; //the car calssifications table get the id of the classification that received from the user
                $car->type = $request->type ;
                $car->capacity = $request->capacity ;
                $car->color = $request->color ;
                $car->plate = $request->plate;

                $car->save();

                $user->is_driver = true;
            }
            
            $user->update();

            if ($request->mobile_no != $user->mobile_no && !empty($request->mobile_no)){ //if the user wants to change his mobile he must verify it first or it wont be changed
                //Validated
                $validateUser = Validator::make($request->all(), 
                [
                    'mobile_no' => 'numeric|digits_between:10,12'
                ]);

                if($validateUser->fails()){
                    return response()->json([
                        'status' => false,
                        'message' => 'validation error',
                        'errors' => $validateUser->errors()
                    ], 401);
                }
                
                $otp = Otp::find($user->id);
                $otp->random = random_int (1111,9999); //Str::random(4, '0123456789');// generate_otp() is a custom function to generate the OTP.
                $otp->expired_at = Carbon::now()->addMinutes(10);
                $otp->update();


                return response()->json([
                    'status' => true,
                    'message' => 'you should verify the new mobile_no or it wont be updated',
                    'otp' => $otp->random
                ], 200);
            }


            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function addLocation(Request $request){
        try{
            $user = User::where('id', auth()->user()->id)->first();
            $user->current_location = $request->current_location;
            $user->update();

            return response()->json([
                'status' => true,
                'message' => 'Location added successfully',
                'user' => $user
            ], 500);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function signOut(Request $request){
        try{
            $user = auth()->user();
            PersonalAccessToken::where('tokenable_id', $user->id)->delete();
            return response()->json([
                'status' => true,
                'message' => 'user signed out'
            ], 500);
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function replica(Request $request){
        try{
            $user = auth()->user();
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function createUser(Request $request){
        try {
            $request->merge(['is_driver' => $request->input('is_driver') ?? false]);
            
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'mobile_no' => 'required|unique:users,mobile_no|numeric|digits_between:10,12',
                'plate' => 'is_driver' ? 'nullable' : 'required',
                'capacity' => 'is_driver' ? 'nullable' : 'required',
                'type' => 'is_driver' ? 'nullable' : 'required',
                'color' => 'is_driver' ? 'nullable' : 'required'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            DB::beginTransaction();
            
                $user = new User();
                $user->name = $request->name;
                $user->mobile_no = $request->mobile_no;
                $user->is_driver = $request->is_driver;
                $user->save();  


            if($request->is_driver){
                $carClassification= CarClassification::where('classification',$request->classification)->first();
                $car = new Car();
                $car->user_id = $user->id ;
                $car->classification_id = $carClassification->id ; //the car calssifications table get the id of the classification that received from the user
                $car->type = $request->type ;
                $car->capacity = $request->capacity ;
                $car->color = $request->color ;
                $car->plate = $request->plate;

                $car->save();


                Car::create([
                        'user_id' => $user->id,
                        'classification_id' => $carClassification->id, //the car calssifications table get the id of the classification that received from the user,
                        'type' => $request->type,
                        'plate' => $request->plate,
                        'capacity' => $request->capacity,
                        'color' => $request->color,    
                    ]);

            }
            

            $otp = new Otp();
            $otp->random = random_int (1111,9999); //Str::random(4, '0123456789');// generate_otp() is a custom function to generate the OTP.
            $otp->expired_at = Carbon::now()->addMinutes(10);
            $otp->user_id = $user->id;
            $otp->save();

            
            DB::commit();


            return response()->json([
                'status' => true,
                'message' => 'Please verify your mobile number',
                'user_id' => $user->id,
                'otp' => $otp->random
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
    public function signIn(Request $request){

        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'mobile_no' => 'required|numeric|digits_between:10,12'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            
            DB::beginTransaction();

            $user = User::where('mobile_no', $request->mobile_no)->first();
            if(empty($user)){
                return response()->json([
                    'status' => false,
                    'message' => 'Mobile number doesn\'t exist please regiseter'
                ], 500);
            }
            $otp = Otp::where('user_id',$user->id)->first();
            $otp->random = random_int (1111,9999); //Str::random(4, '0123456789');// generate_otp() is a custom function to generate the OTP.
            $otp->expired_at = Carbon::now()->addMinutes(10);
            $otp->update();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Please verify your mobile number',
                'user_id' => $user->id,
                'otp' => $otp->random
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    
    
    
    /**
     * Display a listing of the resource.
     */
    public function index(){ 
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //dd($request->all());

        $user= New User();

        $user->id = $request->id;
        $user->mobile_no = $request->mobile_no;
        $user->name = $request->name;
        $user->rating = $request->rating;
        $user->profile_img = $request->profile_img;
        $user->is_driver = $request->is_driver;
        $user->current_location = $request->current_location;

        $user->save();

        return response()->json($user);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $users = User::where('id',$id)->get();

        return response()->json($users);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
       //$users=["message"=>" done", "data"=>$user->all()];

        //dd($request->mobile_no);

        

        //$user->id = $request->id;
        $user->mobile_no = $request->mobile_no;
        // $user->name = $request->name;
        // $user->rating = $request->rating;
        // $user->porfile_img = $request->porfile_img;


        // User::where('id', $id)
        //       ->update(['mobile_no' => $request->mobile_no]);

        // User::where('id', $id)
        // ->update($request->all());//
        
        $user->save();

        return response()->json(["message"=>" done", "data"=>$user->all()]);

        


        
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        User::where('id', $id)
        ->delete();
    }
}
