<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactAgencyMail extends Mailable
{
    use Queueable, SerializesModels;

    public $subject;
    public $content;
    public $userName;
    public $userEmail;

    public function __construct($subject, $content, $userName, $userEmail)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->userName = $userName;
        $this->userEmail = $userEmail;
    }

        public function build()
        {
            return $this->subject($this->subject)
                        ->replyTo($this->userEmail)
                        ->view('emails.contact_agency')
                        ->with([
                            'content' => $this->content,
                            'userName' => $this->userName,
                            'userEmail' => $this->userEmail,
                            'subject' => $this->subject
                        ]);
        }
}