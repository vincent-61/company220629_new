<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public $html;
    public $subject;

    /**
     * Create a new message instance.
     *
     * @param string $html 邮件内容
     * @param string $subject 邮件标题
     *
     * @return void
     */
    public function __construct($html, $subject)
    {
        $this->html = $html;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // return $this->view('email.message');

        return $this->html($this->html)->subject($this->subject);
    }
}
