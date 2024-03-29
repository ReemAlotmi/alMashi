<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Otp;
use App\Models\Ride;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\DriverRate;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CarClassification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function createUser(Request $request){
        try {
            $request->merge(['is_driver' => $request->input('is_driver') ?? false]);
            
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
            $otp->random = random_int (1111,9999); 
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
                ], 401);
            }
            $otp = Otp::where('user_id',$user->id)->first();
            $otp->random = random_int (1111,9999); 
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

    public function viewProfile(){
        try{
            $user = auth()->user();
            return response()->json(
                [
                    'status' => true,
                    'user' => new UserResource($user),     
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
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'profile_img' => 'required',
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $user = User::where('id', auth()->user()->id)->first();
            $user->name = $request->name;
            $user->profile_img = $request->profile_img;

            //checks the car information fields
            $fields = [$request->plate, $request->classification, $request->capacity, $request->type, $request->color];
            $filledFields = [];
            foreach ($fields as $field) {
                if (filled($field)) {
                    $filledFields[] = $field;
                } 
            }

            if($user->is_driver){ //this user is driver
                $response = $this->updateCar($request, $user);
                if ($response->getStatusCode() == 401) {
                    return json_decode($response->getContent());
             }
            }

            if(!($user->is_driver) && !(count($filledFields) === 0)){//if the user is not a driver but wants to be one by adding his car info
                $response = $this->addCar($request, $user);
                if ($response->getStatusCode() == 401) {
                    return json_decode($response->getContent());
             }
            }
            
            if($request->mobile_no != $user->mobile_no ){ //if the user wants to change his mobile he must verify it first or it wont be changed
                $response = $this->updateMobile($request->mobile_no, $user);
                    if ($response->getStatusCode() == 401) {
                        return json_decode($response->getContent());
                    } elseif($response->getStatusCode() == 200){
                        $content = json_decode($response->getContent(), true);
                        // Access the desired key from the content
                        $otp = $content['otp'];                 
                 }
            }  

            $user->save();
            return response()->json([
                'status' => true,
                'message' => 'User updated successfully',
                'user' => $user,
                'otp' => $otp ?? null 
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
            $validateUser = Validator::make($request->all(), 
            [
                'current_location' => 'required'   
            ]);
    
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
            $user = User::where('id', auth()->user()->id)->first();
            $user->current_location = $request->current_location;
            $user->save();



            // "profile_img": "https://www.gooFYADFQAAAAAdAAAAABAE",
            // "name": "Reem",
            // "rating": 3,
            // "price": 10,
            // "distance": " "     
            // "destination": "dskjfdsfjk"

            
            //get all the rides 
            //compare the departure of all of these rides with the user deprature
            //get only the rides that is distant by 10km or less 
            
            $availbleRides= Ride::where('status', ['waiting'])->get();
            $rides=[];

            foreach($availbleRides as $availbleRide){
                //calculate the distnace here
                $distance = Helper::clacDistance($availbleRide->departure, $request->current_location);
                if($distance <= 10){
                    array_push($rides, [
                        "name" =>$availbleRide->user->name,
                        "profile_img" =>$availbleRide->user->profile_img,
                        "rating" => DriverRate::where('driver_id',$availbleRide->user->id)->avg('rate'),
                        "price" => $availbleRide->price,
                        "distance" => $distance,
                        "destination" =>$availbleRide->destination
                    ]);
                }
                
            }

            return response()->json([
                'status' => true,
                'message' => 'Location added successfully',
                'Close rides' => $rides
            ], 200);

        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function signOut(){
        try{
            $user= auth()->user();
            $user->tokens()->delete();
            //PersonalAccessToken::where('tokenable_id', $user->id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'user signed out'
            ], 200);
        }
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function index(){ //returns all users
        $users = User::all();
        return response()->json($users);
    }

    protected function updateMobile($mobile_no, $user){
        $validateUser = Validator::make(
            ['mobile_no' => $mobile_no],
            ['mobile_no' => 'numeric|digits_between:10,12|unique:users,mobile_no']
        );

        if($validateUser->fails()){
            return response()->json([
                'status' => false,
                'message' => 'validation error',
                'errors' => $validateUser->errors()
            ], 401);
        }
            
        $otp = Otp::where("user_id", $user->id)->first();
        $otp->random = random_int (1111,9999); 
        $otp->expired_at = Carbon::now()->addMinutes(10);
        $otp->update();

        return response()->json([
            'status' => true,
            'otp' => $otp->random
        ], 200);
    
    }

    protected function addCar($request, $user){
        //checks the car information fields
        $fields = [$request->plate, $request->classification, $request->capacity, $request->type, $request->color];
        $filledFields = [];
        foreach ($fields as $field) {
            if (filled($field)) {
                $filledFields[] = $field;
            }   
        } 

        if (count($filledFields) > 0 && count($filledFields) < count($fields)) {// At least one field is filled, but not all of them
            // Handle the error or show appropriate response
            $validateUser = Validator::make($request->all(), 
            [
                'plate' => 'required',
                'classification' => 'required', 
                'type' => 'required', 
                'capacity' => 'required|numeric', 
                'color' => 'required'
            ]);

            if($validateUser->fails()){

                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
        }
        //otherwise all the fields are filled
        //add new
        $carClassification= CarClassification::where('classification',$request->classification)->first();
        if(Car::where('user_id', $user->id)->first()){
            

                return response()->json([
                    'status' => false,
                    'message' => 'there is a registered car for this user'
                ], 401);
            
        }
        $car = new Car();
        $car->user_id = $user->id ;
        $car->classification_id = $carClassification->id ; //the car calssifications table get the id of the classification that received from the user
        $car->type = $request->type ;
        $car->capacity = $request->capacity ;
        $car->color = $request->color ;
        $car->plate = $request->plate;
        $car->save();
        $user->is_driver = true;

        return response()->json([
            'status' => true
        ], 200);
    }

    protected function updateCar($request, $user){
        //checks the car information fields
        $fields = [$request->plate, $request->classification, $request->capacity, $request->type, $request->color];
        $filledFields = [];
        foreach ($fields as $field) {
            if (filled($field)) {
                $filledFields[] = $field;
            }
        }

        if (count($filledFields) > 0 && count($filledFields) < count($fields)) {// At least one field is filled, but not all of them
            // Handle the error or show appropriate response
            $validateUser = Validator::make($request->all(), 
            [
                 'plate' => 'required',
                 'classification' => 'required', 
                 'type' => 'required',
                 'capacity' => 'required', 
                 'color' => 'required'
            ]);
            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }
        } elseif (count($filledFields) === 0) {// The user wants to remove his car, subsequently he won't be a driver anymore
            // No fields are filled
            $car= Car::where('user_id', $user->id)->first();
            $car->delete();
            $user->is_driver= false;
            $user->save();
            
            return response()->json([
                'status' => true
            ], 200);
            

        } else {// All fields are filled means he wants to update his car info 
            $carClassification= CarClassification::where('classification',$request->classification)->first();
            $car= Car::where('user_id', $user->id)->update([
                'classification_id' => $carClassification->id, //the car calssifications table get the id of the classification that received from the user
                'type' => $request->type,
                'capacity' => $request->capacity,
                'color' => $request->color,
                'plate' => $request->plate,
            ]);
            return response()->json([
                'status' => true
            ], 200);
        }
    }

    
}
