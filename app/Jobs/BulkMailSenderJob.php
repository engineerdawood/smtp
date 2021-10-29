<?php

namespace App\Jobs;

use App\Mail\SingleMail;
use App\MailList;
use App\Settings;
use App\Templates;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Mail;

class BulkMailSenderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * @var Templates
     */
    private $template;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Templates $template)
    {
        $this->onQueue('bulk-template');
        $this->template = $template;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $bulkMails = MailList::where(['template_id' => $this->template->id, 'status' => 0])->get();
        Templates::where('id', $this->template->id)->update(['status' => 2]);
        $totalMails = $bulkMails->count();
        $i = 0;
        $queue = 'bulk-email';
	    $codeObj = helper()->getSettings('code_object');
	    if(empty($codeObj) || !isset($codeObj['response']) || (isset($codeObj['response']) && strtotime('now') >= $codeObj['response']['rv'])){
		    Settings::where( 'key', 'code_object' )->delete();
	    } else {
		    foreach ( $bulkMails as $mail ) {
			    $i ++;
			    $isLast = ( $totalMails == $i ) ? true : false;
			    $fail   = false;

			    if ( $mail ) {
				    $emailObj = $mail->email;
				    if ( $emailObj ) {
					    if ( strlen( $emailObj->email ) > 0 && filter_var( $emailObj->email, FILTER_VALIDATE_EMAIL ) ) {
						    Mail::to( $emailObj->email )
						        ->queue( new SingleMail( $mail, $queue, $isLast ) );
					    } else {
						    $fail = true;
					    }

				    } else {
					    $fail = true;
				    }
			    } else {
				    $fail = true;
			    }
			    if ( $fail ) {
				    $mail->status = 2;
				    $mail->update();
			    }
		    }
	    }
    }
}
