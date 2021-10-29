<?php

namespace App\Mail;

use App\Facades\CustomHelper;
use App\MailList;
use App\Templates;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class SingleMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    /**
     * @var MailList
     */
    private $mail;
    /**
     * @var bool
     */
    private $isLast;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(MailList $mail, $queue = 'default', $isLast = false)
    {
        $this->onQueue($queue);
        $this->mail = $mail;
        $this->isLast = $isLast;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $settings = CustomHelper::getSettings();
        $template = $this->mail->template;
        $imgTag = '<img src="' . CustomHelper::getPageUrl('main::email.emailviewed', ['id' => base64_encode($this->mail->id)]) . '" style="display:none;" />';

        // updating status
        $this->mail->status = 1;
        $this->mail->update();

        if($this->isLast){
            Templates::where('id', $this->mail->template_id)->update(['status' => 3]);
        }

        $senderName = empty($template->sender_name) ? $settings['sender_name'] : $template->sender_name;
        $senderEmail = empty($template->sender_email) ? $settings['sender_email'] : $template->sender_email;

        return $this->from($senderEmail, $senderName)
            ->subject($template->subject)
            ->view('emails._generic')
            ->with([
                'mailHtml' => CustomHelper::content_to_replace($template->message, $this->mail->email, $imgTag),
            ]);
    }
}
