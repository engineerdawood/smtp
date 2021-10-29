@extends('layouts.adminpanel')
@if(!is_null($record))
    @section('title', $record->name)
@else
    @section('title', 'Invalid Template')
@endif

<?php
$pageRoute = "admin::campaigns";
$statuses = [
    -1 => 'Select All',
    0 => 'Pending',
    1 => 'Verified',
    2 => 'Not found',
];
?>
@section('content')
    <div class="row" xmlns="http://www.w3.org/1999/html">
        <div class="col-md-8">
            <form id="main_form" action="{{ CustomHelper::getPageUrl($pageRoute . '.edit', ['campaign' => $record->id]) }}" method="get" ht-ajaxy="" ht-ajax-callback="update_table_data_callback">
                <section id="refresh_section" class="">
                    <div class="x_panel">
                        <div class="x_title">
                            <div class="row">
                                <div class="actions_bar col-sm-12">
                                    <div class="col-sm-3">
                                        <button id="delete_emails" disabled="disabled" data-toggle="tooltip" title="Delete Emails" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                    </div>
                                    <div class="search_action col-sm-offset-3 col-sm-3">
                                        <div class="input-group input-group-sm search-field-area">
                                            <input type="text" name="search" class="form-control pull-right" value="{{ @$search }}" placeholder="Search">

                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="per_page_action col-sm-3">
                                        <select name="per_page" class="form-control" onchange="jQuery('#main_form').submit()">
                                            @foreach($pagination as $page)
                                                <option value="{{ $page }}" {{ ($page == $per_page) ? 'selected' : "" }}>{{ $page }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="box-tools">
                                {{ $emails->links() }}
                            </div>

                            <div class="row action-results">
                                <div class="col-md-6">
                                    @if($search)
                                        <strong>Search results for: </strong>{{ $search }}.
                                    @endif
                                </div>
                                <div class="col-md-6 text-right">
                                    Showing <strong>{{ number_format($emails->firstItem()) }} - {{ number_format($emails->lastItem()) }}</strong> out of <strong>{{ $emails->total() }}</strong>
                                </div>
                            </div>

                        </div>
                        <!-- /.box-header -->
                        <div class="x_content">
                            <table class="table table-striped table-hover data-table table-condensed">
                                <thead>
                                    <tr>
                                        <th width="5%"><input type="checkbox" id="toggle_emails" /></th>
                                        <th width="60%">Email</th>
                                        <th width="15%">Status</th>
                                        <th width="20%">Actions</th>
                                    </tr>
                                </thead>
                                @if(count($emails) > 0)
                                    @foreach($emails as $email)
                                        <tr data-id="{{ $email->id }}">
                                            <td><input type="checkbox" name="email_ids[]" value="{{ $email->id }}" /></td>
                                            <td><span>{{ $email->email }}</span></td>
                                            <td><span class="text text-{{ ($email->status == 1) ? 'success' : 'danger' }}">{{ $statuses[$email->status] }}</span></td>
                                            <td>
{{--                                                <a href="{{ CustomHelper::getPageUrl($pageRoute . '.edit', ['category' => $email->id]) }}"><i class="fa fa-pencil text-info"></i></a>--}}
                                                <a href="{{ CustomHelper::getPageUrl($pageRoute . '.deleteemail', ['campaign' => $email->campaign_id, 'email' => $email->id]) }}" onclick="if(!confirm('Are you sure to delete email?')){ return false; }"><i class="fa fa-times text-danger"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="3" class="text-center text-info"><i class="fa fa-info-circle"></i> No record found.</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                        <div class="box-footer clearfix">
                            <div class="box-tools">
                                {{ $emails->links() }}
                            </div>
                        </div>
                    </div>
                </section>
            </form>
        </div>
        <div class="col-md-4">
            @include("admin.campaigns._form")
        </div>
    </div>
@stop

@section('footer_space')
    <script>
        $(document).ready(function () {
            $('#delete_emails').on('click', function (e) {
                e.preventDefault();
                var el = $(this);
                if(confirm('Are you sure to delete emails?')) {
                    var url = $('[name="email_ids[]"]').serialize();
                    send_form("{{ CustomHelper::getPageUrl($pageRoute . '.deletebulkemail', ['campaign' => $email->campaign_id]) }}?" + url, 'POST');
                }
            });

            $('#toggle_emails').on('change', function () {
                var el = $(this);
                $('[name="email_ids[]"]').prop('checked', el.is(':checked'));
            });

            $('#toggle_emails, [name="email_ids[]"]').on('change', function () {
                var el = $(this);
                var btn = $('#delete_emails');
                if($('[name="email_ids[]"]:checked').length > 0){
                    btn.removeAttr('disabled');
                } else {
                    btn.attr('disabled', 'disabled');
                }
            });
        });
    </script>
@stop