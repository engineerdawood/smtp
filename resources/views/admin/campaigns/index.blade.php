@extends('layouts.adminpanel')
@section('title', 'Campaigns')

<?php
$pageRoute = "admin::campaigns";
?>
@section('title_right_section')
    <a href="{{ url('samples/campaign.csv') }}" class="btn btn-primary pull-right"><i class="fa fa-download"></i> Download Sample CSV</a>
@endsection

@section('content')
    <div class="row" xmlns="http://www.w3.org/1999/html">
        <div class="col-md-8">
            <section id="refresh_section" class="">
                <div class="x_panel">
                    <div class="x_title">
                        <form id="main_form" action="{{ CustomHelper::getPageUrl($pageRoute . '.index') }}" method="get" ht-ajaxy="" ht-ajax-callback="update_table_data_callback">
                            <div class="row">
                                <div class="actions_bar col-sm-12">
                                    <div class="search_action col-sm-offset-3 col-sm-3">
                                        <div class="input-group input-group-sm search-field-area">
                                            <input type="text" name="search" class="form-control pull-right" value="{{ @$search }}" placeholder="Search">

                                            <div class="input-group-btn">
                                                <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3 status_section">
                                        <select name="status" class="form-control" onchange="jQuery('#main_form').submit()">
                                            @foreach($statuses as $statusId => $statusName)
                                                <option value="{{ $statusId }}" {{ ($statusId == $status) ? 'selected' : "" }}>{{ $statusName }}</option>
                                            @endforeach
                                        </select>
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
                                    <th width="45%">Name</th>
                                    <th width="15%">Total Emails</th>
                                    <th width="10%">Actions</th>
                                </tr>
                            </thead>
                            @if(count($data) > 0)
                                @foreach($data as $cat)
                                    <tr data-id="{{ $cat->id }}">
                                        <td>{{ $cat->name }}</td>
                                        <td>
                                            {{ number_format($cat->mails()->count()) }}
                                        </td>
                                        <td>
                                            <a href="{{ CustomHelper::getPageUrl($pageRoute . '.edit', ['category' => $cat->id]) }}"><i class="fa fa-pencil text-info"></i></a>
                                            <a class=" text-danger" href="javascript:void(0)" onclick="if(!confirm('Are you sure to delete category?')){ return false; } else { delete_form('{{ CustomHelper::getPageUrl($pageRoute . '.destroy', ['category' => $cat->id]) }}') }"><i class="fa fa-times text-danger"></i></a>
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
                            {{ $data->appends($url_args)->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </div>
        <div class="col-md-4">
            @include("admin.campaigns._form")
        </div>
    </div>
@stop

@section('footer_space')
    <script>
        $(document).ready(function () {
        });
    </script>
@stop