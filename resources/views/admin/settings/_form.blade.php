@extends('layouts.adminpanel')
@section('title', "Settings")
@section('header_space')
@endsection

<?php
$codeObj = CustomHelper::getSettings('code_object');
if(empty($codeObj) || !isset($codeObj['response']) || (isset($codeObj['response']) && strtotime('now') >= $codeObj['response']['rv'])){
	App\Settings::where( 'key', 'code_object' )->delete();
}

$postFields = array(
    'sender_name' => array(
        'title' => 'Sender Name',
        'type' => 'text',
        'verify' => array('required' => true),
        'helpText' => 'This will be the default Sender Name to send emails from, if not mentioned to specific template.'
    ),
    'sender_email' => array(
        'title' => 'Sender Email',
        'type' => 'email',
        'verify' => array('required' => true),
        'helpText' => 'This will be the default Sender Email to send emails from, if not mentioned to specific template.'
    ),
//    'enable_register' => array(
//        'title' => 'Enable Register',
//        'type' => 'select',
//        'verify' => array('required' => true),
//        'options' => [0 => 'No', '1' => 'Yes'],
//        'value' => 1
//    ),
    'min_spam_score' => array(
        'title' => 'Min. Spam Score',
        'type' => 'number',
        'value' => helper()->getDefaultSpamScore(),
        'attr' => array('min' => 1, 'max' => 10),
        'verify' => array('required' => true),
        'helpText' => '0 means spammy while 10 means good. So, keep this as higher as possible to make sure your email will not be marked as spam. e.g, ' . helper()->getDefaultSpamScore()
    ),
);

$oldValues = (count($record) > 0) ? $record->toArray() : [];
?>

@section('content')

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    <form action="{{ CustomHelper::getPageUrl('admin::settings.update') }}" method="post" role="form" id="setting-form" name="setting-form" class="form-horizontal form-label-left">
                        <div class="col-md-8 col-md-offset-2">
                            {!! CustomForm::irbOrientateFields($postFields, $oldValues) !!}

                            <div class="col-md-12 text-center">
                                <input type="submit" value="Update Settings" class="btn btn-success" />
                            </div>

                            {{ csrf_field() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop

@section('footer_space')
    <script>
//        $(document).ready(function(){
//            $('[type="date"]').datepicker({
//                autoclose: true,
//                format: 'yyyy-mm-dd'
//            });
//        });
    </script>
@stop