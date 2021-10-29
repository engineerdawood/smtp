<?php

namespace App\Http\Controllers\Admin;

use App\Campaigns;
use App\MailList;
use App\Templates;
use Bilaliqbalr\IrbLicenseManager\Facade\IrbLicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index() {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

//	    $validation = IrbLicenseManager::validate();
//	    if ( ! $validation['status'] ) {
//		    return redirect( CustomHelper::getPageUrl('irb-lm::license-form') );
//	    }

	    $minSpamValue                        = helper()->getSettings( 'min_spam_score' );
	    $args['stats']                       = [];
	    $args['stats']['queue_campaigns']    = Campaigns::select( DB::raw( 'count(id) as count' ) )->where( 'status', 1 )->get()->pluck( 'count' )->first();
	    $args['stats']['progress_campaigns'] = Campaigns::select( DB::raw( 'count(id) as count' ) )->where( 'status', 2 )->get()->pluck( 'count' )->first();
	    $args['stats']['sent_mails']         = MailList::select( DB::raw( 'count(id) as count' ) )->where( 'status', 1 )->get()->pluck( 'count' )->first();
	    $args['stats']['left_mails']         = MailList::select( DB::raw( 'count(id) as count' ) )->where( 'status', 0 )->get()->pluck( 'count' )->first();
	    $args['stats']['not_viewed_mails']   = MailList::select( DB::raw( 'count(id) as count' ) )->where( [
		    'is_viewed' => 0,
		    'status'    => 1
	    ] )->get()->pluck( 'count' )->first();
	    $args['stats']['viewed_mails']       = MailList::select( DB::raw( 'count(id) as count' ) )->where( [
		    'is_viewed' => 1,
		    'status'    => 1
	    ] )->get()->pluck( 'count' )->first();
	    $args['stats']['spam_templates']     = Templates::select( DB::raw( 'count(id) as count' ) )->where( 'spam_score', '<', (is_null($minSpamValue) ? helper()->getDefaultSpamScore() : $minSpamValue) )->get()->pluck( 'count' )->first();

	    return view( 'admin.dashboard', $args );
    }
}
