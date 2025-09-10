<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Telegram\Bot\Api;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    public function webhook(Request $request)
    {
        // Initialize Telegram API
        try {
            $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));
        } catch (\Exception $e) {
            Log::error('Telegram API initialization failed: ' . $e->getMessage());
            return response('ok', 200);
        }

        $update = $request->all();
        Log::info('Telegram update:', $update);

        $message = $update['message'] ?? null;
        if (!$message) return response('ok', 200);

        $chatId = $message['chat']['id'] ?? null;
        $text   = $message['text'] ?? null;
        $user   = $message['from'] ?? [];

        // Handle /start command
        if ($text === '/start' && $chatId) {
            $welcomeText = "Hello " . ($user['first_name'] ?? 'User') . "!\n";
            $welcomeText .= "Telegram ID: " . ($user['id'] ?? 'N/A') . "\n";
            $welcomeText .= "Username: @" . ($user['username'] ?? 'N/A');
            $welcomeText .= ($user);

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

            try {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $welcomeText . "\n\nPlease share your phone number:",
                    'reply_markup' => $reply_markup
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send message: ' . $e->getMessage());
            }
        }

        // Handle shared contact
        if (isset($message['contact'])) {
            $phone = $message['contact']['phone_number'] ?? null;

            if ($chatId && $phone) {
                try {
                    $telegram->sendMessage([
                        'chat_id' => $chatId,
                        'text' => "Thanks! We've received your phone number: $phone"
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send confirmation message: ' . $e->getMessage());
                }
            }
        }

        return response('ok', 200);
    }
}
