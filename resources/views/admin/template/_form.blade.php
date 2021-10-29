@extends('layouts.adminpanel')
@section('title', $isNew ? "Add Template" : "Edit Template")
@section('header_space')
@endsection

<?php
$postFields = array(
    'subject' => array(
        'title' => 'Subject',
        'type' => 'text',
        'attr' => array('max' => 1024),
        'verify' => array('required' => true),
        'helpText' => 'This will be the email subject.'
    ),
    'campaign_id' => array(
        'title' => 'Campaign',
        'type' => 'select',
        'options' => $campaigns,
        'verify' => array('required' => true)
    ),
    'sender_name' => array(
        'title' => 'Sender Name',
        'type' => 'text',
        'attr' => array('max' => 255),
    ),
    'sender_email' => array(
        'title' => 'Sender Email',
        'type' => 'email',
        'attr' => array('max' => 255),
    ),
    'message' => array(
        'title' => 'Description',
        'type' => 'textarea',
        'verify' => array('required' => true),
    ),
    'id' => array(
        'type' => 'hidden',
        'value' => 0
    ),
);

$oldValues = (count($record) > 0) ? $record->toArray() : [];
?>

@section('content')

    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_content">
                    @if(!$isNew)
                        @if($record->status == 1 || $record->status == 2)
                            <div class="alert alert-info">
                                You cannot modify template until all emails sent.
                            </div>
                        @endif
                        @if(!helper()->is_spam_free_template($record))
                            <div class="alert alert-danger">
                                Your spam sore is <strong>{{ $record->spam_score }}</strong>. Please improve your template to avoid spamming.
                            </div>
                        @else
                            <div class="alert alert-success">
                                Great! Your spam score is <strong>{{ $record->spam_score }}</strong>.
                            </div>
                        @endif
                    @endif
                    <form action="{{ CustomHelper::getPageUrl('admin::templates.update', ['template' => $isNew ? '' : $record->id]) }}" method="post" role="form" enctype="multipart/form-data" id="user-form" name="user-form" class="form-horizontal form-label-left">
                        <div class="col-md-10 col-md-offset-1">
                            {!! CustomForm::irbOrientateFields($postFields, $oldValues) !!}

                            <div class="col-md-10 col-md-offset-1 text-center">
                                @if($isNew || (!$isNew && $record->status <> 1 && $record->status <> 2))
                                    <input type="submit" value="{{ $isNew ? 'Add & Verify Template' : 'Update & Verify Template' }}" class="btn btn-success" />
                                @endif
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#template_preview">Template Preview</button>
                                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#guide">Shortcode Guide</button>
                                @if(!$isNew)
                                    @if($record->status <> 1 && $record->status <> 2 && helper()->is_spam_free_template($record))
                                        <a onclick="if(confirm('Would you like to add this to queue?')){ $('#mailsending').submit(); }" href="javascript:void(0)" class="btn btn-success">
                                            <i class="fa fa-paper-plane-o"></i> Start Sending
                                        </a>
                                    @endif
                                @endif
                            </div>

                            {{ csrf_field() }}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!$isNew)
        @if($record->status <> 1 && $record->status <> 2 && helper()->is_spam_free_template($record))
            <form action="{{ CustomHelper::getPageUrl('admin::templates.startsending') }}" method="post" role="form" enctype="multipart/form-data" id="mailsending" name="mailsending" class="hide">
                {{ csrf_field() }}
                <input type="hidden" name="template_id" value="{{ $record->id }}" />
            </form>
        @endif
    @endif

@stop

@section('popup_space')
    <div class="modal fade" id="template_preview" tabindex="-1" role="dialog" aria-labelledby="template_previewLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="template_previewLabel">Template Preview</h4>
                </div>
                <div class="modal-body">
                    <iframe id="preview" frameborder="0" width="100%" height="500"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="guide" tabindex="-1" role="dialog" aria-labelledby="guideLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="guideLabel">Shortcode Guide</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Shortcode</th>
                            <th width="70%">Description</th>
                        </tr>
                        <tr>
                            <td>$${UNSUBSCRIBE}$$</td>
                            <td>Unsubscribe Link</td>
                        </tr>
                        <tr>
                            <td>$${EMAIL}$$</td>
                            <td>User Email Address</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_space')
    <script src="{{ asset('vendors/ckeditor/ckeditor.js') }}"></script>
    <script>
        var message;
        function syncPreview() {
            var iframe = document.getElementById('preview'),
                iframedoc = iframe.contentDocument || iframe.contentWindow.document;;
            iframedoc.body.innerHTML = message.getData();
        }

        CKEDITOR.config.allowedContent=true;

        $.each(['message'], function(i, e){
            message = CKEDITOR.replace(e, {
                on: {
                    // Synchronize the preview on user action that changes the content.
                    change: syncPreview,
                    // Synchronize the preview when the new data is set.
                    contentDom: syncPreview
                }
            });
        });
//        $(document).ready(function(){
//            $('[type="date"]').datepicker({
//                autoclose: true,
//                format: 'yyyy-mm-dd'
//            });
//        });
    </script>
@stop