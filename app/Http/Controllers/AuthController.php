<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use App\Models\User;

use App\Http\Requests\AuthRequest;

class AuthController extends Controller
{

    public static function middleware(): array
    {
        return [
            'auth',
            new Middleware('auth', except: ['login', 'register']),
        ];
    }


    public function login(AuthRequest $request)
    {
        //input validation is done by AuthRequest form request
        $credentials = $request->safe()->only('email', 'password');

        //attempt to log the user in and generate token
        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
                'status' => true,
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
        ], 200);

    }

    public function register(AuthRequest $request){

        //input validation is done by AuthRequest form request
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if($user){
            $token = Auth::login($user);
            return response()->json([
                'status' => true,
                'message' => 'User created successfully',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ], 201);
        }
    
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'status' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        //refresh the authentication token
        return response()->json([
            'status' => true,
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

}
