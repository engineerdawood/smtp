<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $fillable = ['key', 'value'];
    public $timestamps = false;

	public function getValueAttribute($value) {
	    try {
		    $dValue = json_decode($value, true);
	    } catch (\Exception $e) {
		    $dValue = $value;
	    }
	    if(is_null($dValue) && !empty($value)){
	    	$dValue = $value;
	    }
	    return $dValue;
    }
}
