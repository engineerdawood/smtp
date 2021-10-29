<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Templates extends Model
{

    public $fillable = ['sender_name', 'sender_email', 'subject', 'campaign_id', 'message', 'status', 'spam_score'];

    public function mailList(){
        return $this->hasMany(MailList::class);
    }

    public function campaign(){
        return $this->belongsTo(Campaigns::class);
    }

}
