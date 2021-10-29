<?php

namespace App\Http\Controllers\Admin;

use Bilaliqbalr\IrbLicenseManager\Facade\IrbLicenseManager;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Campaigns;
use App\Emails;
use App\Facades\CustomHelper;
use App\Jobs\VerifyValidEmailsJob;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use Goodby\CSV\Import\Standard\Lexer;
use Goodby\CSV\Import\Standard\Interpreter;
use Goodby\CSV\Import\Standard\LexerConfig;

class CampaignsController extends Controller
{
    public function arguments()
    {
        $args = $_REQUEST;
        return array_merge([
            'pagination' => CustomHelper::get_pagination(),
            'per_page' => 10,
            'status' => -1,
            'search' => ''
        ], $args, [

        ]);
    }

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

	    $args = $this->arguments();
	    $args['url_args'] = $_GET;
        $args['statuses'] = [-1 => 'Select All'] + helper()->getCampaignsStatuses();
        $args['isNew'] = empty($id);
        $data = Campaigns::select('*');
        if(!empty($args['search'])){
            $data->where('name', 'like', '%'.$args['search'].'%');
        }
        if($args['status'] > -1){
            $data->where('status', $args['status']);
        }
        $args['data'] = $data->paginate((int)$args['per_page']);
        return view("admin.campaigns.index", $args);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->edit(request()->has('id') ? request()->get('id') : 0);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    if(Input::has('id'))
            return $this->update($request, 0);
        else
            return $this->index();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = 0)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

        set_time_limit(0);

        if($request->has('id')){
            $id = $request->get('id');
        }

        $validations = empty($request->hasFile('emails_csv')) ? ($id == 0 ? ['emails' => 'required'] : []) :
            ['emails_csv' => 'custom_mimes:csv,xls,xlsx'];
        $data = Input::all();
        $this->validate($request, array_merge([
            'name' => 'required'
        ], $validations));

	    $inValidEmails = [];
        $emails = $request->has('emails') ? $request->get('emails') : [];
        if(!empty($emails)) {
            $unVerifiedEmails = preg_split("/\r\n|\n/", $emails);
	        $emails = array_filter($unVerifiedEmails, function ($sMail) { return filter_var($sMail, FILTER_VALIDATE_EMAIL); });
	        $inValidEmails = array_diff($unVerifiedEmails, $emails);
        }
        $validEmails = empty($emails) ? [] : $emails;
        $totalImported = 0;

        $status = true;
        if (count($inValidEmails) > 0) {
            custom_flash("Following invalid emails have been ignored.<br />" . implode("<br />", $inValidEmails), "warning");
            $status = false;
        }

        $object = (empty($id)) ? new Campaigns() : Campaigns::find($id);
        $object->name = $data['name'];
        if(empty($id)){
            $object = Campaigns::create($object->toArray());
            custom_flash("Campaign has been added successfully.", "success");
        } else {
            $object->save();
//            custom_flash("Campaign has been updated successfully.", "success");
        }

        if($request->hasFile('emails_csv')){
            $path = $request->file('emails_csv')->move(public_path('uploads'), $request->emails_csv->getClientOriginalName());
            if(file_exists($path)) {
                $lexer = new Lexer(new LexerConfig());
                $interpreter = new Interpreter();
                $i = 0;
                $interpreter->addObserver(function (array $row) use (&$emails, &$i, $object, &$totalImported) {
                    $emails[] = $row[0];
                    $i++;

                    if($i == 1500){
                        $mailObjs = [];
                        foreach($emails as $email){
                            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                $mailObjs[] = $email;
                            } else {
                                $inValidEmails[] = $email;
                            }
                        }
                        $totalImported += $this->saveEmails($mailObjs, $object);

                        // Resetting variables
                        $emails = [];
                        $i = 0;
                    }
                });
                $lexer->parse($path, $interpreter);
                // Delete file
                unlink($path);

                // Rest of emails
                $validEmails = array_merge($validEmails, $emails);
            }
        }
        if(count($validEmails) > 0){
            $totalImported += $this->saveEmails($validEmails, $object);
        }

        // Checking duplicates
	    $duplicateEmailsQry = "SELECT m1.email as email FROM emails m1, emails m2 WHERE m1.id < m2.id AND m1.email = m2.email AND m1.campaign_id = {$id};";
	    $duplicateEmails = collect(DB::select($duplicateEmailsQry))->pluck('email')->toArray();
	    $duplicateEmailsCount = count($duplicateEmails);
	    if (count($duplicateEmails) > 0) {
		    custom_flash("Following duplicate emails have been ignored.<br />" . implode("<br />", $duplicateEmails), "warning");
		    $status = false;
	    }

	    // Deleting duplicates
	    $delDuplicateQry = "DELETE m1 FROM emails m1, emails m2 WHERE m1.id < m2.id AND m1.email = m2.email AND m1.campaign_id = {$id};";
	    DB::statement($delDuplicateQry);

	    custom_flash(($totalImported - $duplicateEmailsCount) . " emails have been imported successfully.", "success");

        return redirect()->back();
    }

    private function saveEmails(array $emails, $campaign)
    {
        $mailObjs = [];
        foreach($emails as $email){
            $emailObj = new Emails;
//            $emailObj->campaign_id = $campaign->id;
            $emailObj->email = $email;
            $emailObj->token = helper()->randomNumber(30);
            // TODO - remove this when adding mail existance verifier
            $emailObj->status = 1;

            $mailObjs[] = $emailObj;
        }
        $campaign->mails()->saveMany($mailObjs);
        return count($mailObjs);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id = 0)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $args = $this->arguments();
        $args['isNew'] = empty($id);
        $data = Campaigns::find($id);
        $emails = Emails::where(['campaign_id' => $id]);
        if(!empty($args['search'])){
            $emails
                ->where('email', 'like', '%'.$args['search'].'%');
            if($args['status'] > -1){
                $emails->where('status', $args['status']);
            }
        }
        $args['record'] = $data;
        $args['emails'] = $emails->paginate((int)$args['per_page']);

        return view("admin.campaigns._single", $args);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    Campaigns::destroy($id);
        custom_flash("Campaign has been deleted successfully.", "success");
        return redirect()->back();
    }

    public function deleteEmail($campaignId, $emailId)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $mail = Emails::where(['id' => $emailId, 'campaign_id' => $campaignId])->delete();
        custom_flash("Email has been deleted successfully.", "success");
        return redirect()->back();
    }

    public function bulkDeleteEmail($campaignId)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }
	    $emailIds = Input::has('email_ids') ? Input::get('email_ids') : [];
	    $emailCount = count($emailIds);

	    if($emailCount > 0) {
		    $mail = Emails::where( [ 'campaign_id' => $campaignId ] )->whereIn( 'id', $emailIds )->delete();
		    custom_flash( "{$emailCount} emails have been deleted successfully.", "success" );
	    } else {
		    custom_flash( "Please select emails to delete.", "danger" );
	    }
        return redirect(helper()->getPageUrl('admin::campaigns.edit', ['category' => $campaignId]));
    }

}
