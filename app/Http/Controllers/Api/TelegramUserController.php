<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class TelegramUserController extends Controller
{
    public function storeOrLogin(Request $request)
    {
        Log::info('Incoming Telegram payload:', $request->all());
        $data = $request->all();

        Log::error('This is a test error log');
        // Automatically find or create user
        $user = User::firstOrCreate(
            ['telegram_id' => $data['id']], // condition
            [
                'username'   => $data['username'] ?? null,
                'first_name' => $data['first_name'] ?? null,
                'last_name'  => $data['last_name'] ?? null,
                'phone_number' => $data['phone_number'] ?? null,
                'role'       => 'telegram',
            ]
        );

        $token = $user->createToken('telegram')->plainTextToken;

        Auth::login($user);

        return response()->json(['user' => $user, 'token' => $token]);
    }
}
