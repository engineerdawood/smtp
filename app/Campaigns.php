<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Campaigns extends Model
{

    public $fillable = ['name' /*, 'status' */];

    public function mails(){
        return $this->hasMany(Emails::class, 'campaign_id');
    }

}
