<?php

namespace App\Http\Controllers\Admin;

use App\Campaigns;
use App\Facades\CustomHelper;
use App\Jobs\BulkMailSenderJob;
use App\Mail\SingleMail;
use App\MailList;
use App\Templates;
use Bilaliqbalr\IrbLicenseManager\Facade\IrbLicenseManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Mail;

class TemplatesController extends Controller
{
    public function arguments()
    {
        $args = $_REQUEST;
        return array_merge([
            'pagination' => CustomHelper::get_pagination(),
            'per_page' => 10,
            'status' => -1,
            'spam' => -1,
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
        $minSpamValue = helper()->getSettings('min_spam_score');
        $args['statuses'] = [-1 => 'Select All Types'] + helper()->getTemplateStatuses();
        $args['all_spams'] = [-1 => 'Select All'] + ['Spam', 'Not Spam'];
        $data = Templates::select('*');
        if(!empty($args['search'])){
            $data->where('subject', 'like', '%'.$args['search'].'%');
        }
        if($args['status'] > -1){
            $data->where('status', $args['status']);
        }
        if($args['spam'] == 0){
            $data->where('spam_score', '<=', $minSpamValue);
        } else if ($args['spam'] == 1){
            $data->where('spam_score', '>', $minSpamValue);
        }
        $args['data'] = $data->paginate((int)$args['per_page']);
        return view("admin.template.index", $args);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->edit(0);
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $args['record'] = Templates::find($id);
        return view("admin.template._single", $args);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $args['isNew'] = empty($id);
        $args['record'] = $args['isNew'] ? [] : Templates::find($id);
        $args['campaigns'] = Campaigns::select(['id', 'name'])->get()->mapWithKeys(function ($item) {
            return [$item['id'] => $item['name']];
        })->all();
        $args['campaigns'] = [-1 => 'Select campaign'] + $args['campaigns'];
        return view("admin.template._form", $args);
    }

    public function getReports($id)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $args['record'] = Templates::find($id);
        $query = "
            select g.*, c.name as campaign_name from (
                select g.template_id, count(g.template_id) as total, g.campaign_id, g.is_viewed, g.status from (
                    select m.template_id, m.is_viewed, m.status, e.`campaign_id` from mail_lists as m left join emails as e on e.id = m.email_id where template_id = {$id} and template_id is not null
                ) as g group by g.template_id, g.is_viewed, g.status, g.campaign_id
            ) as g left join campaigns as c on g.campaign_id = c.id;
        ";
        $data = DB::select($query);
        $data = collect($data)->groupBy('campaign_id');
        $args['data'] = $data;
//        dd($args);
//        Get unique campaigns and create report table from MailList
//        $args['campaigns'] = Campaigns::where(['template_id' => ]);
//        $args['campaigns'] = [-1 => 'Select campaign'] + $args['campaigns'];
        return view("admin.template._report_list", $args);
    }

    public function resumeCampaign($id, $campaignId)
    {
    	set_time_limit(0);

	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $list = MailList::select('mail_lists.*')
	        ->join(DB::raw('emails as e'), 'mail_lists.email_id', '=', 'e.id')
	        ->where(['template_id' => $id, 'e.campaign_id' => $campaignId, 'mail_lists.status' => 0]);
	    $countList = clone $list;

	    if($countList->count() == 0){
		    custom_flash("Campaign already completed.", "info");
		    return redirect()->back();
	    }
	    $bulkMails = $list->get();
        $totalMails = $bulkMails->count();
        $i = 0;
        $queue = 'bulk-email';
        if($bulkMails) {
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
        custom_flash("Campaign has been resumed successfully.", "success");
        return redirect()->back();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Input::all();
        $this->validate($request, [
            'subject' => 'required|max:1024',
            'campaign_id' => 'required',
            'message' => 'required',
        ]);

        $object = (empty($id)) ? new Templates() : Templates::find($id);
        $object->sender_name = $data['sender_name'];
        $object->sender_email = $data['sender_email'];
        $object->subject = $data['subject'];
        $object->message = $data['message'];
        $object->campaign_id = $data['campaign_id'];

        $object->save();

        $reply = helper()->template_spam_test($object);
        if(is_array($reply)){
            custom_flash("Following error(s) occurred while checking spam score.<br />" . implode("<br />", $reply), "warning");
        }

        if(empty($id)){
            custom_flash("Template has been added successfully.", "success");
        } else {
            custom_flash("Template has been updated successfully.", "success");
        }

        return redirect()->back();
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

	    Templates::destroy($id);
        custom_flash("Template has been deleted successfully.", "success");
        return redirect()->back();
    }

    public function startSending()
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $template_id = Input::get('template_id');
        $template = Templates::find($template_id);

        if(!helper()->is_spam_free_template($template)){
            custom_flash("Unable to send emails because of spammy email.", "danger");

        } else if($template->status == 1 || $template->status == 2){
            custom_flash("Template is already in queue or progress.", "warning");

        } else {
            // TODO - Add some check to test if specific email already exists for sending

            $query = "INSERT INTO mail_lists (template_id, email_id) SELECT {$template_id}, id FROM emails WHERE campaign_id = {$template->campaign_id} AND status = 1;";
            // Adding new mail list
            DB::insert(DB::raw($query));

            if(app()->environment('production', 'local')){
                dispatch(new BulkMailSenderJob($template));
            }

            $template->status = 1;
            $template->update();

            custom_flash("Template has been added to queue.", "success");
        }
        return redirect()->back();
    }
}
