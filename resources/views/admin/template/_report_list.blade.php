@extends('layouts.adminpanel')
@if(!is_null($record))
    @section('title', '"' . substr($record->subject, 0, 80) . '" Statistics')
@else
    @section('title', 'Invalid Template')
@endif
<?php
$pageRoute = "admin::templates";
?>

@section('content')
    <div class="row" xmlns="http://www.w3.org/1999/html">
        <section id="refresh_section" class="col-md-12">
            <div class="x_panel">
                <!-- /.box-header -->
                <div class="x_content">
                    <table class="table table-striped table-hover data-table">
                        <thead>
                        <tr>
                            <th width="35%">Campaign</th>
                            <th width="15%">Total Emails</th>
                            <th width="10%">Sent</th>
                            <th width="10%">Pending</th>
                            <th width="10%">Viewed</th>
                            {{--<th class="text-center" width="20%">Status</th>--}}
                            <th width="20%" class="text-center">Actions</th>
                        </tr>
                        </thead>
                        @if(count($data) > 0)
                            @foreach($data as $records)
                                <?php
                                $record = new StdClass;
                                $recObj = collect($records);
                                $rec = $recObj->first();
                                $record->template_id = $rec->template_id;
                                $record->campaign_id = $rec->campaign_id;
                                $record->campaign_name = $rec->campaign_name;
                                $record->total = $recObj->sum('total');
                                $record->sent = 0;
                                $record->pending = 0;
                                $record->viewed = 0;
                                foreach($records as $rec){
                                    if($rec->is_viewed == 1){
                                        $record->viewed += $rec->total;
                                    }
                                    if($rec->status == 1){
                                        $record->sent += $rec->total;
                                    } else if($rec->status == 2){
                                        $record->pending += $rec->total;
                                    }
                                }
                                ?>
                                <tr>
                                    <td>
                                        <a href="{{ CustomHelper::getPageUrl('admin::campaigns.edit', ['category' => $record->campaign_id]) }}">{{ $record->campaign_name }}</a>
                                    </td>
                                    <td>{{ $record->total }}</td>
                                    <td>{{ $record->sent }}</td>
                                    <td>{{ $record->pending }}</td>
                                    <td>{{ $record->viewed }}</td>
{{--                                    <td>{{ CustomHelper::getMailListStatuses()[$record->status] }}</td>--}}
                                    <td class="text-center">
                                        @if($record->total > $record->sent)
                                            <a href="javascript:void(0)" onclick="send_form('{{ CustomHelper::getPageUrl($pageRoute . '.resume', [$record->template_id, $record->campaign_id]) }}', 'post')" data-toggle="tooltip" title="Resume Campaign"><i class="fa fa-refresh text-info"></i></a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="6" class="text-center text-info"><i class="fa fa-info-circle"></i> No record found.</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>
        </section>
    </div>
@stop

@section('footer_space')
    <script>
        $(document).ready(function () {
        });
    </script>
@stop