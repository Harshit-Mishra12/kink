<?php

namespace App\Http\Controllers\V1;


use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the input
        $request->validate([
            'email' => 'required|email',  // Ensure email is provided and in a valid format
            'password' => 'required|string',  // Ensure password is provided
        ]);

        // Find the user by email
        $user = User::where('email', $request->input('email'))->first();

        // Check if user exists
        if ($user) {
            // Verify the password
            if (Hash::check($request->input('password'), $user->password)) {
                // Generate API token for the user
                $token = $user->createToken('api-token')->plainTextToken;
                return response()->json([
                    'status_code' => 1,
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ],
                    'message' => 'Login successful.',
                ]);
            } else {
                return response()->json([
                    'status_code' => 2,
                    'data' => [],
                    'message' => 'Incorrect password.',
                ]);
            }
        } else {
            return response()->json([
                'status_code' => 2,
                'data' => [],
                'message' => 'Account not registered.',
            ]);
        }
    }

    public function createAnonymousUser(Request $request)
    {
        $validatedData = $request->validate([
            'language' => 'required|string',
            'gender' => 'required|string',
            'orientation' => 'required|string',
            'country' => 'required|string',
            'age_category' => 'required|string',
        ]);

        // Create anonymous user
        $user = User::create([
            'language' => $validatedData['language'],
            'gender' => $validatedData['gender'],
            'orientation' => $validatedData['orientation'],
            'country' => $validatedData['country'],
            'age_category' => $validatedData['age_category'],
            'role' => 'ANONYMOUSUSER'
        ]);

        // Generate Sanctum token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status_code' => 2,
            'data' => ['user' => $user, 'token' => $token],
            'message' => 'Anonymous user created successfully.',
        ]);
    }
}
