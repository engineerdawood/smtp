<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Emails extends Model
{

//    Emails status
//    0 => not checked
//    1 => verified
//    2 => not exists
    public $fillable = ['campaign_id', 'email', 'status', 'token'];
    public $timestamps = false;

    public function campagin(){
        return $this->hasOne(Campaigns::class);
    }

}
