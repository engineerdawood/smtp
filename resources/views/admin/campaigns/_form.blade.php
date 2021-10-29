<?php
//dd($all_campagins);
//if(!$isNew){
//    unset($optionCampaigns[$record->post_type][$record->id]);
//}
$mainPart = array(
    'name' => array(
        'title' => 'Name',
        'type' => 'text',
        'verify' => array('required' => true),
    ),
//    'status' => array(
//        'title' => 'Status',
//        'type' => 'select',
//        'options' => CustomHelper::getCampaignsStatuses(),
//    ),
    'emails_csv' => array(
        'title' => 'Upload CSV',
        'type' => 'file',
    ),
    'emails' => array(
        'title' => 'Add Bulk Emails',
        'type' => 'textarea',
        'attr' => ['rows' => 18],
//        'verify' => array('required' => true),
    ),
    'id' => array(
        'type' => 'hidden',
        'value' => 0,
    ),
);

$oldValues = !$isNew ? $record->toArray() : (empty(old()) ? false : old());
?>

<div class="x_panel">
    <div class="x_content">
        <form action="{{ CustomHelper::getPageUrl('admin::campaigns.update', ['campaign' => $isNew ? '' : $record->id]) }}" method="post" role="form" id="campaign-form" name="campaign-form" enctype="multipart/form-data">
            {!! CustomForm::irbOrientateFields($mainPart, $oldValues) !!}
            <input type="submit" value="{{ $isNew ? 'Add Campaign' : 'Update Campaign' }}" class="btn btn-success text-center" />
            {{ csrf_field() }}
        </form>
    </div>
</div>
