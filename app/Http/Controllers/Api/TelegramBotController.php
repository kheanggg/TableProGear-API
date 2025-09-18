<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TelegramBotController extends Controller
{
    public function webhook(Request $request)
    {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN_USER'));

        // Try to get message via SDK
        try {
            $update = $telegram->getWebhookUpdates();
            $message = $update->getMessage();
        } catch (\Exception $e) {
            $message = null;
        }

        // Fallback for direct request
        if (!$message) {
            $message = $request->input('message');
            if (!$message) return response('ok', 200);

            $chatId = $message['chat']['id'] ?? null;
            $text = $message['text'] ?? null;
            $user = $message['from'] ?? null;
        } else {
            $chatId = $message->getChat()->getId();
            $text = $message->getText();
            $user = $message->getFrom();
        }

        // Log user info
        Log::info('Telegram User Info:', [
            'id' => $user['id'] ?? $user?->getId(),
            'first_name' => $user['first_name'] ?? $user?->getFirstName(),
            'last_name' => $user['last_name'] ?? $user?->getLastName(),
            'username' => $user['username'] ?? $user?->getUsername(),
            'language' => $user['language_code'] ?? $user?->getLanguageCode(),
        ]);

        // Handle /start command
        if ($text === '/start') {
            $userInfoText = "Hello " . ($user['first_name'] ?? $user?->getFirstName()) . "!";
            $userInfoText .= "\nTelegram ID: " . ($user['id'] ?? $user?->getId());
            $userInfoText .= "\nUsername: @" . (($user['username'] ?? $user?->getUsername()) ?? 'N/A');
            $userInfoText .= "\nLanguage: " . (($user['language_code'] ?? $user?->getLanguageCode()) ?? 'N/A');

            // Keyboard button to request phone number
            $keyboard = [
                [
                    ['text' => 'Share Phone Number', 'request_contact' => true]
                ]
            ];

            $reply_markup = json_encode([
                'keyboard' => $keyboard,
                'one_time_keyboard' => true,
                'resize_keyboard' => true
            ]);

            // Send welcome message + request phone number
            if ($chatId) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $userInfoText . "\n\nPlease share your phone number:",
                    'reply_markup' => $reply_markup
                ]);
            }
        }

        // Handle shared contact
        if (isset($message['contact'])) {
            $phone = $message['contact']['phone_number'] ?? null;
            $firstName = $message['contact']['first_name'] ?? null;

            if ($chatId && $phone) {
                // Send confirmation message
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => "Thanks $firstName! We've received your phone number: $phone"
                ]);

                Log::info("User shared phone number: $phone, Name: $firstName");
            }
        }

        return response('ok', 200);
    }

    public function getUser(Request $request)
    {
        $update = $request->all();
        $from   = $update['message']['from'] ?? null;

        if ($from) {
            // Create or update Telegram user
            $user = User::updateOrCreate(
                ['telegram_id' => $from['id']],
                [
                    'username'   => $from['username'] ?? null,
                    'first_name' => $from['first_name'] ?? null,
                    'last_name'  => $from['last_name'] ?? null,
                    'role'       => 'telegram',
                ]
            );

            // Create Sanctum token
            $token = $user->createToken('telegram')->plainTextToken;

            // Log the user in (optional if using Sanctum)
            Auth::login($user);

            return response()->json([
                'status' => 'ok',
                'user'   => $user,
                'token'  => $token,
            ]);
        }

        return response()->json(['status' => 'no user found'], 200);
    }
}