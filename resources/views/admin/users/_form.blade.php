@extends('layouts.adminpanel')
@section('title', $isNew ? "Add User" : "Edit User")
@section('header_space')

@endsection

<?php
$personalFields = array(
    'name' => array(
        'title' => 'Name',
        'type' => 'text',
        'verify' => array('required' => true)
    ),
    'email' => array(
        'title' => 'Email',
        'type' => 'text',
        'verify' => array('required' => true),
        'attr' => (!$isNew ? array('readonly' => true) : [])
    ),
    'password' => array(
        'title' => 'Password',
        'type' => 'password',
        'verify' => ($isNew ? array('required' => true) : [])
    ),
//    'role' => array(
//        'title' => 'Role',
//        'type' => 'select',
//        'options' => array_merge(((!$isNew && $record->role == 'superadmin') ? ['superadmin' => 'Super Admin'] : [] ), ['user' => 'User']),
//        'value' => 'user',
//        'verify' => array('required' => true)
//    ),
//    'status' => array(
//        'title' => 'Status',
//        'type' => 'select',
//        'options' => ['active' => 'Active', 'deactive' => 'Deactive'],
//        'verify' => array('required' => true)
//    ),
    'id' => array(
        'type' => 'hidden',
        'value' => 0
    ),
);

$oldValues = (count($record) > 0) ? $record->toArray() : false;
?>

@section('content')

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    <form action="{{ CustomHelper::getPageUrl('admin::users.update', ['user' => $isNew ? '' : $record->id]) }}" method="post" role="form" enctype="multipart/form-data" id="user-form" name="user-form" class="form-horizontal form-label-left">
                        <div class="col-md-6 col-md-offset-3">
                            {!! CustomForm::irbOrientateFields($personalFields, $oldValues) !!}
                            <input type="submit" value="{{ $isNew ? 'Add User' : 'Update User' }}" class="btn btn-success text-center" />
                            {{ csrf_field() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop

@section('footer_space')

@stop