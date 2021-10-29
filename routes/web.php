<?php
use App\Mail\SingleMail;
use App\MailList;

include_once(__DIR__ . '/admin.php');

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function (){
    return redirect(helper()->getPageUrl('admin::dashboard'));
});

//Route::get('/tm', function (){
//    Mail::raw('Hi, welcome user!', function ($message) {
//        $message->to('pklhqmkh@sharklasers.com')->subject("My Mail");
//    });
//});
//
//Route::get('/rm', function (\Illuminate\Http\Request $request){
//    Mail::to('pklhqmkh@sharklasers.com')->send(new App\Mail\SingleMail(App\MailList::find(24), 'bulk-email', true));
////    Mail::to($request->user())->send(new App\Mail\SingleMail(App\MailList::find(24), 'bulk-email', true));
//    dd(Mail::failures());
//});
//
//Route::get('/qm', function (){
//    Mail::to('pklhqmkh@sharklasers.com')->queue(new App\Mail\SingleMail(App\MailList::find(24), 'bulk-email', true));
//});

Auth::routes();

Route::group(['namespace' => 'Admin', 'as' => 'main::'], function() {
    Route::get('unsubscribe/{email}/{token}', 'EmailsController@unSubscribe')->name('email.unsubscribe');
    Route::get('image/{id}', 'EmailsController@emailViewed')->name('email.emailviewed');
});