<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MailList extends Model
{
    public $fillable = ['template_id', 'email_id', 'is_viewed', 'status'];
    public $timestamps = false;

    public function template(){
        return $this->belongsTo(Templates::class);
    }

    public function email(){
        return $this->hasOne(Emails::class, 'id', 'email_id');
    }

}
