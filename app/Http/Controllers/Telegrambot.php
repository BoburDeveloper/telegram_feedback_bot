<?php

namespace App\Http\Controllers;

use App\Models\FeedbackRequest;
use App\Models\FeedbackResponse;
use App\Models\BotMessaging;
use App\Mail\FeedbackReceived;
use App\Models\VerificationCode;
use App\Providers\AppserviceProvider;
use DefStudio\Telegraph\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class Telegrambot extends Controller
{
    /**
     * Display the main dashboard with bots listing.
     */
    public function index()
    {
        $bots = TelegraphBot::orderByDesc('created_at')->paginate(10);

        $bot_messagings_num = BotMessaging::count();

        $is_verified_num = VerificationCode::where(['verified'=>true])->count();

        return view('telegrambot.index', [
            'bots' => $bots,
            'isCreateTrue' => $bots->isEmpty(),
            'bots_num'=>$bots->count(),
            'bot_messagings_num'=>$bot_messagings_num,
            'is_verified_num'=>$is_verified_num,
        ]);
    }

    /**
     * Handle add/edit bot and messaging forms.
     */
    public function form(Request $request, $action, $id = 0)
    {
        $bot = TelegraphBot::find($id);
        if(in_array($action, ['edit', 'add'])) {
            $view = 'form';
        } else {
            $view = $action;
        }

        if ($action === 'messaging') {
            $this->data['messagings'] = BotMessaging::latest()->paginate(10);
        } elseif($action === 'vc') {
            $this->data['vc'] = DB::table('telegraph_chats')
                    ->leftJoin('verification_codes', 'verification_codes.chat_id', '=', 'telegraph_chats.chat_id')
                    ->select(
                        'verification_codes.*',
                        'telegraph_chats.chat_id',
                        'telegraph_chats.name as chat_name',
                        'telegraph_chats.type as chat_type',
                        'telegraph_chats.telegraph_bot_id'
                    )
                    ->orderBy('verification_codes.created_at', 'desc')
                    ->paginate(10); // ← paginate ishlatilmoqda
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
        } // 1. Agar bu kodga o‘xshagan matn bo‘lsa (masalan, 6 xonali raqam)
        elseif ($text && preg_match('/^\d{6}$/', $text)) {
            $chatId = $payload['message']['chat']['id'];
            $bot = TelegraphBot::where('token', $token)->firstOrFail();

            // VerificationController orqali yuborish
            return $this->verifyCode($chatId, $text, $bot);
        } 
        else {
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

    /**
     * Send verification Code.
     */
    public function sendCode(Request $request)
    {
        $chat_id = $request->input('chat_id');

        $code = rand(100000, 999999);
        $expires = now()->addMinutes(5); // 5 Expires up to 5 minutes

        VerificationCode::updateOrCreate(
            ['chat_id' => $chat_id],
            ['code' => $code, 'expires_at' => $expires]
        );

        $chat = TelegraphChat::where('chat_id', $chat_id)->first();
        if ($chat) {
            $chat->message("🔐 Your verification code is: <b>$code</b>\n\n⏳ Expires at: 5 minutes")->send();
        }

        return response()->json(['message' => 'Verification code sent']);
    }

    /**
     * Verification coming code.
     */
    public function verifyCode($chatId, $code, $bot)
    {
        $verification = VerificationCode::where('chat_id', $chatId)
            ->first();

        if ($verification->code==$code and $verification->expires_at > now()) {
            // Code is correct, Answer will sent
            $verification->verified = true;
            $verification->save();
            TelegraphChat::where('chat_id', $chatId)->first()?->message("✅ Verification successful!")->send();
            return 1;
        } else {
            // Code is incorrect or expired, Answer will sent
            $verification->verified = false;
            $verification->save();
            TelegraphChat::where('chat_id', $chatId)->first()?->message("❌ Invalid or expired verification code.")->send();
            return 0;
        }


    }

}
