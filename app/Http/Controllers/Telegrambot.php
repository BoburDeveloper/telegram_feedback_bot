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
use App\Providers\AppserviceProvider;

class Telegrambot extends Controller
{



	/**
	 * Home page/User interface page for add/edit telegram bot
	 *
	 */
    public function index() 
	{
		$this->data['isCreateTrue'] = false;
        $this->data['bots'] = TelegraphBot::orderBy('created_at', 'desc')->paginate(10); // har 10 ta bot
		if($this->data['bots']->count() < 1) {
			$this->data['isCreateTrue'] = true;
		}
        return view('telegrambot/index', $this->data);
    }

	/**
	 * Add/edit telegram bot via User Interface
	 *
	 * @param Request $request
	 * @param string $action
	 * @param integer $id
	 */
    public function form(Request $request, $action, $id=0)
    {
		
            $bot = TelegraphBot::where('id', $id)->first();
            
            if($request->post('tbot')) {
				$request->validate([
					'tbot.name' => 'required|string|max:255',
					'tbot.username' => 'required|string|max:255',
					'tbot.token' => 'required|string',
				]);
                $request_data = $request->post('tbot');
               
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

	/**
	 * Telegram bot start event function
	 *
	 * @param array $request_data
	 * @param string $token
	 */
    private function start($request_data, $token) 
	{
        $defaultWelcomeMessage = __('messages.default_welcome_message');
		
        $bot = TelegraphBot::where('token', $token)->firstOrFail();

       $chat_type = AppserviceProvider::chat_type_detect(['chat'=>$request_data['message']['chat']]);
       $name = $chat_type['name'];
       $type = $chat_type['type'];

        $data = $request_data['message'];
        $text = $data['text'] ?? '';

        $chat = TelegraphChat::firstOrCreate([
            'chat_id' => $data['chat']['id'],
            'telegraph_bot_id' => $bot->id,
            'name'=>$name,
            'type'=>$type,
        ]);

        if ($text === '/start') {

            $is_sent = $chat->message($defaultWelcomeMessage)->send();

            return response('OK');
        }

    }

	/**
	 * Handle incoming webhook messages from Telegram bot.
	 * Supports feedback submission and replying logic.
	 *
	 * @param Request $request
	 * @param string $token
	 * @return \Illuminate\Http\Response
	 */
    public function handle(Request $request, $token)
    {
		$myGroupId = env('MY_GROUP_ID');
        
		$bot = TelegraphBot::where('token', $token)->firstOrFail();

        $payload = $request->all();
        
        $feedback_query = null;
        $is_sent = null;
        
        $text = $payload['message']['text'] ?? null;
	   

       $chat_type = AppserviceProvider::chat_type_detect(['chat'=>$payload['message']['chat']]);
       $name = $chat_type['name'];
       $type = $chat_type['type'];

	   // Check if message is a reply to an existing feedback
       if (isset($payload['message']['reply_to_message'])) {
            $replyText = $text;
            $originalMessage = $payload['message']['reply_to_message']['text'];
            if (preg_match('/#ID(\d+)#/', $originalMessage, $matches)) {
                $feedbackId = $matches[1];
              $feedback = FeedbackRequest::find($feedbackId);

			// Store feedback response in DB and send message to user
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
                    $is_sent = $chat->message("📩 You have received an answer:\n\n" . $replyText)->send();

                

                    $feedback_query = FeedbackResponse::create([
                        'feedback_request_id' => $feedbackId,
                        'response_text' => $replyText,
                    ]);
                } else {
					return response('Invalid feedback ID', 400);
                }
            } else {
                return response('ID not found in message', 400);
            }

        return response('reply is Ok', 200);
    
    } elseif($text == '/start') {
			// If it's first message
            return $this->start($payload, $token);
    
    } else {
			// If not a reply, treat it as new feedback
            Log::info('Telegram Webhook:', $payload);
        
            $message = $payload['message'] ?? null;
            if (!$message || !isset($message['chat'])) {
                Log::warning('Chat or message not found');
                return response('Chat or message not found', 422);
            }

            $chatId = $message['chat']['id']; // Telegram chat_id
            $firstName = $name;
            $username = $message['chat']['username'] ?? null;
        
            $request_data = [
                    'telegraph_bot_id' => $bot->id,
                    'chat_id' => $chatId,
                    'type'=>$type
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

            $myGroup = TelegraphChat::where('chat_id', $myGroupId)->firstOrFail();

            $feedback_query = FeedbackRequest::create([
                'full_name' => $firstName,
                'username' => $username,
                'chat_id' => $chatId,
                'message' => $text,
                'bot_id' => $bot->id,
                'is_answered' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $feedback = $feedback_query;

            $is_sent = $myGroup->message("New message: $text\nUser: {$request_data['name']} #ID{$feedback->id}#")->send();
			try {
			    Mail::to(config('mail.feedback_recipient'))->send(
					new FeedbackReceived($firstName, $username, $text)
				);	
			} catch(\Throwable $e) {
				Log::error('Mail sending failed:'.$e->getMessage());

                return response($e->getMessage(), 400);
			}

        }

        if($is_sent) {
            return response('Ok', 200);
        } else {
            return response('Something went wrong!', 400);
        }

    }
    

}
