<?php

namespace App\Http\Controllers\Admin;

use App\Settings;
use Bilaliqbalr\IrbLicenseManager\Facade\IrbLicenseManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $args['record'] = helper()->getSettings();
        return view("admin.settings._form", $args);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $data = Input::all();
        $this->validate($request, [
            'sender_name' => 'required',
            'sender_email' => 'required|email',
            'min_spam_score' => 'required',
//            'enable_register' => 'required',
        ]);
        unset($data['_token']);
        foreach($data as $key => $value){
            Settings::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        custom_flash("Settings have been updated successfully.", "success");

        return redirect()->back();
    }

}
