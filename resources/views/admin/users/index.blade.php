@extends('layouts.adminpanel')
@section('title', 'Users')

<?php
$pageRoute = "admin::users";
?>
@section('title_right_section')
    <a href="{{ CustomHelper::getPageUrl('admin::users.create') }}" class="btn btn-success pull-right">Add new</a>
@endsection

@section('content')
    <div class="row" xmlns="http://www.w3.org/1999/html">
        <form id="main_form" action="{{ route($pageRoute . '.index') }}" method="post" ht-ajaxy="" ht-ajax-callback="update_table_data_callback">
            {!! csrf_field() !!}
            <section id="refresh_section" class="col-md-12">
                <div class="x_panel">
                    <div class="x_title">
                        <div class="row">
                            <div class="col-sm-7"></div>
                            <div class="col-sm-3 search_action">
                                <div class="input-group input-group-sm search-field-area">
                                    <input type="text" name="search" class="form-control pull-right" placeholder="Search">

                                    <div class="input-group-btn">
                                        <button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-2 per_page_action">
                                <select name="per_page" class="form-control" onchange="jQuery('#main_form').submit()">
                                    @foreach($pagination as $page)
                                        <option value="{{ $page }}" {{ ($page == $per_page) ? 'selected' : "" }}>{{ $page }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="box-tools">
                            {{ $data->links() }}
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
                                    <th width="25%">Name</th>
                                    <th width="35%">Email</th>
                                    {{--<th width="15%">Status</th>--}}
                                    <th width="25%">Actions</th>
                                </tr>
                            </thead>
                            @if(count($data) > 0)
                                @foreach($data as $record)
                                    <tr data-id="{{ $record->client_id }}">
{{--                                        <td><a href="{{ CustomHelper::getPageUrl($pageRoute . '.show', [$record->id]) }}">{{ $record->name .' '. $record->lname }}</a></td>--}}
                                        <td>{{ $record->name .' '. $record->lname }}</td>
                                        <td>{{ $record->email }}</td>
{{--                                        <td>{{ ucfirst($record->status) }}</td>--}}
                                        <td>
                                            <a href="{{ CustomHelper::getPageUrl($pageRoute . '.edit', [$record->id]) }}"><i class="fa fa-pencil text-info"></i></a>
                                            <a class="text-danger" href="javascript:void(0)" onclick="if(!confirm('Are you sure to delete user?')){ return false; } else { delete_form('{{ CustomHelper::getPageUrl($pageRoute . '.destroy', [$record->id]) }}') }"><i class="fa fa-times text-danger"></i></a>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-info"><i class="fa fa-info-circle"></i> No record found.</td>
                                </tr>
                            @endif
                        </table>
                    </div>
                    <div class="x_footer clearfix">
                        <div class="box-tools">
                            {{ $data->links() }}
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </div>
@stop

@section('footer_space')
    <script>
        $(document).ready(function () {
        });
    </script>
@stop