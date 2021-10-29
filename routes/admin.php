<?php

Route::group(['namespace' => 'Admin', 'as' => 'admin::', 'middleware' => ['web', 'auth']], function(){

    Route::get('dashboard', 'DashboardController@index')->name('dashboard');

    Route::resource('users', 'UsersController');
    Route::post('users/{user}', 'UsersController@update')->name('users.update');

    Route::get('settings', 'SettingsController@index')->name('settings.index');
    Route::post('settings', 'SettingsController@update')->name('settings.update');

    Route::resource('templates', 'TemplatesController');
    Route::post('templates/startsending', 'TemplatesController@startSending')->name('templates.startsending');
    Route::post('templates/{template}', 'TemplatesController@update')->name('templates.update');
    Route::get('templates/{template}/reporting', 'TemplatesController@getReports')->name('templates.reporting');
    Route::post('templates/{template}/campaign/{campaign}/resume', 'TemplatesController@resumeCampaign')->name('templates.resume');

    Route::resource('campaigns', 'CampaignsController', ['except' => ['post']]);
    Route::post('campaigns/{campaign}', 'CampaignsController@update')->name('campaigns.update');
//    Route::post('campaigns/{campaign}/searchmail', 'CampaignsController@edit')->name('campaigns.searchmail');
    Route::get('campaigns/{campaign}/{email}/delete', 'CampaignsController@deleteEmail')->name('campaigns.deleteemail');
    Route::post('campaigns/{campaign}/bulk-delete', 'CampaignsController@bulkDeleteEmail')->name('campaigns.deletebulkemail');

});

