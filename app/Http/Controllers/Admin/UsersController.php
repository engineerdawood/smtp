<?php

namespace App\Http\Controllers\Admin;

use Bilaliqbalr\IrbLicenseManager\Facade\IrbLicenseManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use App\Facades\CustomHelper;
use App\Http\Controllers\Controller;
use App\User;

class UsersController extends Controller
{
    public function arguments()
    {
        $args = $_REQUEST;
        return array_merge([
            'pagination' => CustomHelper::get_pagination(),
            'per_page' => 10,
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
        $data = User::select('*');
        if(!empty($args['search'])){
            $data->where('name', 'like', '%'.$args['search'].'%')
                ->orWhere('email', 'like', '%'.$args['search'].'%');
        }
        $args['data'] = $data->paginate((int)$args['per_page']);
        return view("admin.users.index", $args);
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

	    $args['record'] = User::find($id);
        return view("admin.users._single", $args);
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
        $args['record'] = $args['isNew'] ? [] : User::find($id);
        return view("admin.users._form", $args);
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
        $additionalCheck = [];
        if(!empty($data['password'])){
            $additionalCheck['password'] = 'min:6';
        }
        $this->validate($request, array_merge([
            'name' => 'required|max:150',
            'email' => 'required|email|max:255',
        ], $additionalCheck));

        $object = (empty($id)) ? new User() : User::find($id);
        $object->name = $data['name'];
        $object->email = $data['email'];
        if(!empty($data['password']))
            $object->password = Hash::make($data['password']);

        $object->save();
        if(empty($id)){
            custom_flash("User has been added successfully.", "success");

        } else {
            custom_flash("User has been updated successfully.", "success");
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

	    User::destroy($id);
	    custom_flash("User has been deleted successfully.", "success");
	    return redirect()->back();
    }

    public function validateUser($confirmation_token)
    {
	    $validation = IrbLicenseManager::validate();
	    if ( ! $validation['status'] ) {
		    return view( $validation['view'] );
	    }

	    $user = User::where(['confirmation_token' => $confirmation_token])->first();
        if($user){
            $user->confirmation_token = null;
            $user->status = 'active';
            $user->save();

            $user->setMeta([
                'meta_key' => 'email_confirmation_date',
                'meta_value' => new Carbon()
            ]);
            custom_flash("Your account has been confirmed.", "success");
        } else {
            custom_flash("Invalid token.", "danger");
        }
        return redirect(url("/"));
    }


}
