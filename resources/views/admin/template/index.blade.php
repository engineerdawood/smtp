@extends('layouts.adminpanel')
@section('title', 'Templates')

<?php
$pageRoute = "admin::templates";
?>
@section('title_right_section')
    <a href="{{ CustomHelper::getPageUrl($pageRoute . '.create') }}" class="btn btn-success pull-right">Add new</a>
@endsection

@section('content')
    <div class="row" xmlns="http://www.w3.org/1999/html">
{{--            {!! csrf_field() !!}--}}
            <section id="refresh_section" class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <form id="main_form" action="{{ route($pageRoute . '.index') }}" method="get" ht-ajaxy="" ht-ajax-callback="update_table_data_callback">
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-3 search_action">
                                    <div class="input-group input-group-sm search-field-area">
                                        <input type="text" name="search" class="form-control pull-right" value="{{ $search }}" placeholder="Search">

                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-2 status_section">
                                    <select name="status" class="form-control" onchange="jQuery('#main_form').submit()">
                                        @foreach($statuses as $statusId => $statusName)
                                            <option value="{{ $statusId }}" {{ ($statusId == $status) ? 'selected' : "" }}>{{ $statusName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2 spam_section">
                                    <select name="spam" class="form-control" onchange="jQuery('#main_form').submit()">
                                        @foreach($all_spams as $spamId => $spamTitle)
                                            <option value="{{ $spamId }}" {{ ($spamId == $spam) ? 'selected' : "" }}>{{ $spamTitle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2 per_page_action">
                                    <select name="per_page" class="form-control" onchange="jQuery('#main_form').submit()">
                                        @foreach($pagination as $page)
                                            <option value="{{ $page }}" {{ ($page == $per_page) ? 'selected' : "" }}>{{ $page }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </form>

                        <div class="box-tools">
                            {{ $data->appends($url_args)->links() }}
                        </div>

                        <div class="row action-results">
                            <div class="col-md-6">
                                @if($search)
                                    <strong>Search results for: </strong>{{ $search }}.
                                @endif
                            </div>
                            <div class="col-md-6 text-right">
                                Showing <strong>{{ number_format($data->firstItem()) }} - {{ number_format($data->lastItem()) }}</strong> out of <strong>{{ $data->total() }}</strong>
                            </div>
                        </div>

                    </div>
                    <!-- /.box-header -->
                    <div class="x_content">
                        <table class="table table-striped table-hover data-table">
                            <thead>
                                <tr>
                                    <th width="45%">Subject</th>
                                    <th width="15%">Campaign</th>
                                    <th width="5%">Spam</th>
                                    <th class="text-center" width="15%">Status</th>
                                    <th width="20%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            @if(count($data) > 0)
                                @foreach($data as $record)
                                    <tr data-id="{{ $record->id }}">
                                        <td><a href="{{ CustomHelper::getPageUrl($pageRoute . '.edit', [$record->id]) }}">{{ $record->subject }}</a></td>
                                        <td>
                                            @if($record->campaign)
                                                <a href="{{ CustomHelper::getPageUrl('admin::campaigns.edit', ['category' => $record->campaign_id]) }}">{{ $record->campaign->name }}</a>
                                            @endif
                                        </td>
                                        <?php
                                        $valid = CustomHelper::is_spam_free_template($record);
                                            if($valid){
                                                $label = 'success';
                                                $spamMsg = 'Valid Email with spam score ' . $record->spam_score;
                                            } else {
                                                $label = 'danger';
                                                $spamMsg = 'Invalid Email with spam score ' . $record->spam_score;
                                            }
                                        ?>
                                        <td class="text-center" title="{{ $spamMsg }}" data-toggle="tooltip">
                                            <i class="fa fa-circle text-{{ $label }}"></i>
                                        </td>
                                        <?php
                                            $status = CustomHelper::getTemplateStatuses()[$record->status];
                                            if($record->status == 1 || $record->status == 2){
                                                $label = 'warning';
                                            } else if($record->status == 3){
                                                $label = 'info';
                                            } else if($record->status == 4){
                                                $label = 'success';
                                            } else {
                                                $label = 'danger';
                                            }
                                        ?>
                                        <td class="text-center">
                                            <label class="label label-{{ $label }}">{{ CustomHelper::getCampaignsStatuses()[$record->status] }}</label>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ CustomHelper::getPageUrl($pageRoute . '.edit', [$record->id]) }}"><i class="fa fa-pencil text-info"></i></a>
                                            <a href="{{ CustomHelper::getPageUrl($pageRoute . '.reporting', [$record->id]) }}"><i class="fa fa-area-chart text-success"></i></a>
                                            @if($record->status <> 1 && $record->status <> 2)
                                                <a class="text-danger" href="javascript:void(0)" onclick="if(!confirm('Are you sure to delete template?')){ return false; } else { delete_form('{{ CustomHelper::getPageUrl($pageRoute . '.destroy', [$record->id]) }}') }"><i class="fa fa-times text-danger"></i></a>
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
                    <div class="x_footer clearfix">
                        <div class="box-tools">
                            {{ $data->appends($url_args)->links() }}
                        </div>
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