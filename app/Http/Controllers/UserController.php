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
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{


    public function viewProfile(Request $request, $id){
        try{
            $classifications = CarClassification::all();
            $user = User::find($id);
            $car = Car::where('user_id', $id)->first();

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
        catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
        
    
    }

    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(), 
            [
                'name' => 'required',
                'mobile_no' => 'required|unique:users,mobile_no|min:8',
                'is_driver' => 'required',
                'plate' => $request->input('is_driver') ? 'required' : 'nullable',
                'capacity' => $request->input('is_driver') ? 'required' : 'nullable',
                'type' => $request->input('is_driver') ? 'required' : 'nullable',
                'color' => $request->input('is_driver') ? 'required' : 'nullable'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            // $user = User::create([
            //     'name' => $request->name,
            //     'mobile_no' => $request->mobile_no,
            //     'is_driver' => $request->is_driver,
            //     'plate' => $request->plate,
            //     'capacity' => $request->capacity,
            //     'type' => $request->type,
            //     'color' => $request->color,    
            // ]);


            // try {
                DB::beginTransaction();

                // $user = User::create([
                    $user = new User();
                    $user->name = $request->name;
                    $user->mobile_no = $request->mobile_no;
                    $user->is_driver = $request->is_driver;
                    // $user->plate = $request->plate;
                    // $user->capacity = $request->capacity;
                    // $user->type = $request->type;
                    // $user->color = $request->color;
                    $user->save();    
                // ]);
    

                if($request->is_driver){

                    $carClassification= CarClassification::where('classification',$request->classification)->first();
                    
                    // $car = new Car();
                    // $car->$user_id = $request->$user_id ;
                    // $car->classification_id = $carClassification->id ; //the car calssifications table get the id of the classification that received from the user
                    // $car->type = $request->type ;
                    // $car->capacity = $request->capacity ;
                    // $car->color = $request->color ;
                    // //$car->availability = $request->availability ;
                    // $car->plate = $request->plate;

                    // $car->save();


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
                    'message' => 'User Created Successfully',
                    'token' => $user->createToken("API TOKEN")->plainTextToken,
                    'otp' => $otp->random
                ], 200);


            
            
            
            
            } catch (\Throwable $th) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => $th->getMessage()
                ], 500);
            }






            // return response()->json([
            //     'status' => true,
            //     'message' => 'User Created Successfully',
            //     'token' => $user->createToken("API TOKEN")->plainTextToken
            // ], 200);

        //  } //catch (\Throwable $th) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => $th->getMessage()
        //     ], 500);
        // }
    }

    
    public function signIn(){

        return response()->json(["message"=>" done", "data"=>"doneee"]);
    }

    public function verify(){

        return response()->json(["message"=>" done", "data"=>"doneee"]);
    }
    
    
    
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        
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
