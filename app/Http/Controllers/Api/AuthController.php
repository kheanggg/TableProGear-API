<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // POST /api/admin/login
    public function login(Request $request)
    {
        $request->validate([
            'username'    => 'required',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('username', 'password'))) {
            return response()->json([
                'message' => 'Invalid login credentials'
            ], 401);
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {
            return response()->json([
                'message' => 'Access denied. Only admins can login.'
            ], 403);
        }

        // ✅ This works after Sanctum setup
        $token = $user->createToken('admin-token')->plainTextToken;

        return response()->json([
            'message' => 'Admin login successful',
            'user'    => $user,
            'token'   => $token
        ]);
    }

}
