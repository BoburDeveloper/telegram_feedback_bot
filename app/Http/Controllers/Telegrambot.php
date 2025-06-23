<?php

namespace App\Http\Controllers;
use App\Models\FeedbackRequest;
use App\Models\FeedbackResponse;
use DefStudio\Telegraph\Telegraph;
use DefStudio\Telegraph\Models\TelegraphBot;
use DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\FeedbackReceived;


class Telegrambot extends Controller
{


    private function xss_clean($name, $value) {
        return strip_tags(trim($value));
    }


    public function index() {
        $this->data['bots'] = TelegraphBot::orderBy('created_at', 'desc')->paginate(10); // har 10 ta bot
        
        return view('telegrambot/index', $this->data);
    }

      public function form(Request $request, $action, $id=0)
    {
            $bot = TelegraphBot::where('id', $id)->first();
            
            if($request->post('tbot')) {
                $request_data = $request->post('tbot');
                foreach($request_data as $key => $value) {
                    $request_data[$key] = $this->xss_clean($key, $value);
                }
                $bot = TelegraphBot::where('token', $request_data['token'])->first();
                if(empty($bot)) {
                    TelegraphBot::firstOrCreate([
                        'name' => $request_data['name'],
                        'username'=>$request_data['username'],
                        'token' => $request_data['token'],
                    ]);

                    return redirect('/')->with('success', 'Primary bot successfully added!');
                } else {
                    $bot->update([
                        'name' => $request_data['name'],
                        'username'=>$request_data['username'],
                        'token' => $request_data['token'],
                    ]);
                            return redirect('/')->with('info', 'The bot was successfully edited.');
                }

            }

            $this->data['action'] = $action;
            $this->data['bot'] = $bot;

        return view('telegrambot/form', $this->data);
    }

    private function start($request_data, $token) {
    
        
        $bot = TelegraphBot::where('token', $token)->firstOrFail();

        $data = $request_data['message'];
        $text = $data['text'] ?? '';

        $chat = TelegraphChat::firstOrCreate([
            'chat_id' => $data['chat']['id'],
            'telegraph_bot_id' => $bot->id,
        ]);

        if ($text === '/start') {

            $chat->message("Welcome, leave your comments, suggestions and complaints!")->send();

            return response('OK');
        }



    }

    public function handle(Request $request, $token)
    {

        $bot = TelegraphBot::where('token', $token)->firstOrFail();

        $payload = $request->all();

        
        $text = $payload['message']['text'] ?? null;
       if (isset($payload['message']['reply_to_message'])) {
            $replyText = $text;
            $originalMessage = $payload['message']['reply_to_message']['text'];
            if (preg_match('/#ID(\d+)#/', $originalMessage, $matches)) {
                $feedbackId = $matches[1];
              $feedback = FeedbackRequest::find($feedbackId);

                if ($feedback) {
                    Log::info('reply_is_giving:', [
                        'to_user_id'=>$feedback->chat_id,
                        'reply_text'=>$replyText,
                        ]);


                        $chat = TelegraphChat::where('chat_id', $feedback->chat_id)->first();
                        if (!$chat) {
                                Log::warning("Chat ID not found: " . $feedback->chat_id);
                                return;
                            }
                    $chat->message("📩 You have received an answer:\n\n" . $replyText)->send();

                

                    FeedbackResponse::create([
                        'feedback_request_id' => $feedbackId,
                        'response_text' => $replyText,
                    ]);
                } else {
                    echo 'Error!';
                }
            } else {
                echo 'Error 2!';die;
            }

        return response('reply is Ok');
    
        } elseif($text == '/start') {

            return $this->start($payload, $token);
    
    } else {

            Log::info('Telegram Webhook:', $payload);
        
            $message = $payload['message'] ?? null;
            if (!$message || !isset($message['chat'])) {
                Log::warning('Chat or message not found');
                return response('warning');
            }

            $chatId = $message['chat']['id']; // Telegram chat_id
            $firstName = $message['chat']['first_name'] ?? null;
            $username = $message['chat']['username'] ?? null;
            $text = $text;

        
            $request_data = [
                    'telegraph_bot_id' => $bot->id,
                    'chat_id' => $chatId, 
                ];

            if(isset($username)) {
                $request_data['name'] = '@'.$username;
            } elseif(empty($username) and isset($firstName)) {
                $request_data['name'] = $firstName;
            } else {
                $request_data['name'] = 'unknown';
            }

            $chat = TelegraphChat::firstOrCreate(
                $request_data,
            );

            $myGroup = TelegraphChat::where('chat_id', -4935000473)->firstOrFail();

            $feedback = FeedbackRequest::create([
                'full_name' => $firstName,
                'username' => $username,
                'chat_id' => $chatId,
                'message' => $text,
                'bot_id' => $bot->id,
                'is_answered' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $myGroup->message("New message: $text\nUser: {$request_data['name']} #ID{$feedback->id}#")->send();

            Mail::to('zboburbek@mail.ru')->send(
                new FeedbackReceived($firstName, $username, $text)
            );

        }

        return response('ok');
    }
    

}
