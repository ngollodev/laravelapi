<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // lets make the public function for the register here 
    public function register(Request $request){
        // lets introduce the validation of the user details from the User model 
        $validated = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|min:8|max:20|confirmed'
        ]);

        if($validated->fails()){
            return response()->json( [
                $validated->errors()
            ], 403);
        }
        
        try {
            // let's make the user registration here 
            $user = User::create([
                'name' => $request->name,
                'email' => $request -> email,
                'password' => Hash::make($request->password)
            ]);

            // let's ,make the token for the user registration 
            $token = $user->createToken(`Auth_Token`)->plainTextToken;

            // lets return the response 

            return response() -> json([
                'access_token' => $token,
                'user' => $user,
            ], 200);
        } catch (\Exception $exception) {
            // let's return a response for the excption errors 
            return response() -> json([
                'error' => $exception->getMessage(),
            ], 409);
        }
    }

    // let's make the public function for the login 

    public function login(Request $request){
        $validated = Validator::make($request -> all(), [
            'email' => 'required|email|string',
            'password' => 'required|min:8|max:20|confirmed'
        ]);

        if($validated -> fails()){
            return response() -> json([
                $validated -> errors()
            ], 403);
        }
        $cridentials = [
            'email' => $request -> email,
            'password' => $request -> passowrd
        ];

        try {
            if (!auth()->attempt($cridentials)) {
                return response() -> json([
                    'error' => 'Invalid User Cridentials',
                ], 403);
            }
            $user = User::where('email', $request -> email)->firstOrfail();

            $token = $user -> createToken(`Auth_Token`)->plainTextToken;

             // lets return the response 

             return response() -> json([
                'access_token' => $token,
                'user' => $user,
            ], 200);

        } catch (\Exception $th) {
            return response() -> json([
                'error' => $th -> getMessage(),
            ], 409);
        }
    }


    // let's make the logout function 

    public function logout(Request $request){
        $request -> user()-> token()-> delete();

        return response()-> json([
            'Message' => 'The User Loged Out Successfully...!',
        ]);
    }
}
