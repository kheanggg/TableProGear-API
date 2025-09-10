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
        // Get Telegram Bot API instance
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        // Option 1: Use SDK getWebhookUpdates (works with real Telegram)
        try {
            $update = $telegram->getWebhookUpdates();
            $message = $update->getMessage();
        } catch (\Exception $e) {
            $message = null;
        }

        // Option 2: Fallback for Postman testing (access request JSON directly)
        if (!$message) {
            $message = $request->input('message');
            if (!$message) {
                return response('ok', 200);
            }
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

            // Send message back to user (only works with real Telegram chat IDs)
            if ($chatId) {
                $telegram->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $userInfoText
                ]);
            }
        }

        return response('ok', 200);
    }
}
