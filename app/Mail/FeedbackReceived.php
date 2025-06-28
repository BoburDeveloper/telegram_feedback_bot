<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FeedbackReceived extends Mailable
{
    use Queueable, SerializesModels;

    public $firstName;
    public $username;
    public $text;

    public function __construct($firstName, $username, $text)
    {
        $this->firstName = $firstName;
        $this->username = $username;
        $this->text = $text;
    }

    public function build()
    {
        return $this->subject(trans('messages.new_telegram_feedback'))
            ->view('emails.feedback_received');
    }
}
