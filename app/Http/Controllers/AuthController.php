<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users',
             'password' => 'required|string|min:8',

         ]);

         if ($validator->fails()) {
             return response()->json(["status" => 422, "message" => $validator->errors()]);
         }
        DB::beginTransaction();
        try {
            $userId = DB::table('users')->insertGetId([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            Cache::put('user_email_'.md5($request->email), $userId, 3600);

            return response()->json([
                'message' => 'User registered successfully',
                'user_id' => $userId,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Log in a user and return a token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

         $user = User::select(['id', 'email', 'password'])
                ->where('email', $request->email)
                ->first();
         if (!$user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    // Return only non-sensitive user data
    return response()->json([
        'token' => $token,
        'User' => $user->only(['id', 'email'])
    ]);
    }

    /**
     * Log out the authenticated user.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get the authenticated user.
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
