<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email'     =>  'required|string|max:255|email',
            'password'  =>  'required'
        ]);

        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)){
            return [
                'message'   =>  'Bad Credentials'
            ];
        }

        $token = $user->createToken('tealwallet-api-token')->plainTextToken;
        $response = [
            'user'      =>  $user,
            'token'     =>  $token,
        ];

        return response($response, 200);
    }


    public function register(Request $request)
    {
        $fields = $request->validate([
            'name'      =>  'required|string|max:255',
            'email'     =>  'required|string|max:255|unique:users,email',
            'password'  =>  'required|confirmed'
        ]);

        $user = User::create([
            'name'      =>  $fields['name'],
            'email'     =>  $fields['email'],
            'password'  =>  Hash::make($fields['password']),
        ]);

        $token = $user->createToken('tealwallet-api-token')->plainTextToken;
        $response = [
            'user'      =>  $user,
            'token'     =>  $token,
        ];

        return response($response, 201);
    }

    public function logout(){
        auth()->user()->tokens()->delete();
        return [
            'message'   =>  'User logged out'
        ];
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response([
            'users' =>  User::all()
        ],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
