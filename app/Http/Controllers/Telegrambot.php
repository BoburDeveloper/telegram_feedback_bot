<?php

namespace App\Http\Controllers;

use App\Models\FeedbackRequest;
use App\Models\FeedbackResponse;
use App\Models\BotMessaging;
use App\Mail\FeedbackReceived;
use App\Providers\AppserviceProvider;
use DefStudio\Telegraph\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class Telegrambot extends Controller
{
    /**
     * Display the main dashboard with bots listing.
     */
    public function index()
    {
        $bots = TelegraphBot::orderByDesc('created_at')->paginate(10);

        return view('telegrambot.index', [
            'bots' => $bots,
            'isCreateTrue' => $bots->isEmpty(),
        ]);
    }

    /**
     * Handle add/edit bot and messaging forms.
     */
    public function form(Request $request, $action, $id = 0)
    {
        $bot = TelegraphBot::find($id);
        $view = in_array($action, ['edit', 'add']) ? 'form' : 'messaging';

        if ($action === 'messaging') {
            $this->data['messagings'] = BotMessaging::latest()->paginate(10);
        }

        if ($request->has('tbot')) {
            $validated = $request->validate([
                'tbot.name' => 'required|string|max:255',
                'tbot.username' => 'required|string|max:255',
                'tbot.token' => 'required|string',
            ]);

            $bot = TelegraphBot::updateOrCreate(
                ['token' => $validated['tbot']['token']],
                [
                    'name' => $validated['tbot']['name'],
                    'username' => $validated['tbot']['username'],
                ]
            );

            return redirect('/')->with('success', 'Bot saved successfully!');
        }

        if ($request->has('tbot_messaging') || $request->hasFile('image')) {
            $validated = $request->validate([
                'tbot_messaging.message' => 'required|string',
                'tbot_messaging.caption' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $path = $request->file('image')->store('broadcasts', 'public');
            $url = asset("storage/" . str_replace('public/', '', $path));

            if ($bot) {
                BotMessaging::create([
                    'telegraph_bot_id' => $bot->id,
                    'message' => $validated['tbot_messaging']['message'],
                    'image' => $url,
                    'caption' => $validated['tbot_messaging']['caption'],
                ]);

                $this->broadcast([
                    'image' => $url,
                    'caption' => $validated['tbot_messaging']['caption'],
                    'message' => $validated['tbot_messaging']['message'],
                ]);

                return redirect('/')->with('success', 'Message broadcasted successfully!');
            }
        }
            
            $this->data['action'] = $action;
            $this->data['bot'] = $bot;

        return view("telegrambot.{$view}", $this->data);
    }

    /**
     * Handle /start command.
     */
    private function start(array $payload, string $token)
    {
        $bot = TelegraphBot::where('token', $token)->firstOrFail();

        $chatData = $payload['message']['chat'];
        $chatType = AppserviceProvider::chat_type_detect(['chat' => $chatData]);

        $chat = TelegraphChat::firstOrCreate([
            'chat_id' => $chatData['id'],
            'telegraph_bot_id' => $bot->id,
        ], [
            'name' => $chatType['name'],
            'type' => $chatType['type'],
        ]);

        if (($payload['message']['text'] ?? '') === '/start') {
            $chat->message(__('messages.default_welcome_message'))->send();
            return response('OK');
        }
    }

    /**
     * Broadcast message to all chats.
     */
    private function broadcast(array $params)
    {
        foreach (TelegraphChat::all() as $chat) {
            try {
                $chat->photo($params['image'], $params['caption'])->send();
                $chat->message($params['caption'])->send();
                $chat->message($params['message'])->send();
            } catch (\Throwable $e) {
                Log::error("Failed to send to {$chat->chat_id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Handle Telegram webhook events.
     */
    public function handle(Request $request, string $token)
    {
        $bot = TelegraphBot::where('token', $token)->firstOrFail();
        $payload = $request->all();

        $chatType = AppserviceProvider::chat_type_detect(['chat' => $payload['message']['chat']]);

        $chat = TelegraphChat::firstOrCreate([
            'chat_id' => $payload['message']['chat']['id'],
            'telegraph_bot_id' => $bot->id,
        ], [
            'name' => $chatType['name'],
            'type' => $chatType['type'],
        ]);

        $text = $payload['message']['text'] ?? '';

        if (isset($payload['message']['reply_to_message'])) {
            return $this->handleReply($payload, $text);
        } elseif ($text === '/start') {
            return $this->start($payload, $token);
        } else {
            return $this->handleNewFeedback($bot, $chat, $payload, $text);
        }
    }

    /**
     * Handle reply to feedback.
     */
    private function handleReply(array $payload, string $text)
    {
        $replyTo = $payload['message']['reply_to_message']['text'] ?? '';
        if (preg_match('/#ID(\d+)#/', $replyTo, $matches)) {
            $feedbackId = $matches[1];
            $feedback = FeedbackRequest::find($feedbackId);

            if ($feedback) {
                $chat = TelegraphChat::where('chat_id', $feedback->chat_id)->first();
                if ($chat) {
                    $chat->message("📩 You have received an answer:\n\n{$text}")->send();
                    FeedbackResponse::create([
                        'feedback_request_id' => $feedbackId,
                        'response_text' => $text,
                    ]);
                    return response('Reply sent', 200);
                }
            }
            return response('Invalid feedback ID', 400);
        }
        return response('Feedback ID not found in message', 400);
    }

    /**
     * Handle new feedback message.
     */
    private function handleNewFeedback($bot, $chat, $payload, $text)
    {
        $myGroupId = env('MY_GROUP_ID');
        $myGroup = TelegraphChat::where('chat_id', $myGroupId)->firstOrFail();

        $feedback = FeedbackRequest::create([
            'full_name' => $chat->name,
            'username' => $payload['message']['chat']['username'] ?? null,
            'chat_id' => $chat->chat_id,
            'message' => $text,
            'bot_id' => $bot->id,
            'is_answered' => false,
        ]);

        $myGroup->message("New message: {$text}\nUser: {$chat->name} #ID{$feedback->id}#")->send();

        try {
            Mail::to(config('mail.feedback_recipient'))->send(new FeedbackReceived(
                $chat->name,
                $payload['message']['chat']['username'] ?? null,
                $text
            ));
        } catch (\Throwable $e) {
            Log::error('Mail sending failed: ' . $e->getMessage());
        }

        return response('Feedback received', 200);
    }
}
