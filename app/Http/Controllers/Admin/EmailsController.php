<?php

namespace App\Http\Controllers\Admin;

use App\Emails;
use App\MailList;
use Bilaliqbalr\IrbLicenseManager\Facade\IrbLicenseManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmailsController extends Controller
{
    function unSubscribe($emailAddr, $token){
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $email = Emails::where(['email' => $emailAddr, 'token' => $token])->first();
        if($email) {
            $email->delete();
        } else {
            $email = Emails::onlyTrashed()->where(['email' => $emailAddr, 'token' => $token])->count();
            if($email == 0) {
                $email = null;
            }
        }

        $args['email'] = $email;
        return view('unsubscribe', $args);
    }

    function emailViewed($id){
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    MailList::where(['id' => base64_decode($id), 'status' => 1])->update(['is_viewed' => 1]);
        return response('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGP6zwAAAgcBApocMXEAAAAASUVORK5CYII=')
            ->header('Content-Type', 'image/png');
    }
}
