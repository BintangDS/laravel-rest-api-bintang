<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Check if the user exists in the database
        $user = User::where('email', $request->email)->first();

        // 3. If user is not found or password is incorrect, return an error response
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => [
                    'credentials' => ['Invalid email or password.']
                ]
            ], 401);
        }

        // 4. If authentication is successful, generate a Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Return a standardized success response
        return response()->json([
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]
        ], 200);
    }

    public function logout(Request $request)
    {
        // Delete the token that was used to authenticate the current request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'data' => [
                'message' => 'Successfully logged out.'
            ]
        ], 200);
    }
}