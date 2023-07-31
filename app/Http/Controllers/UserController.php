<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
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
