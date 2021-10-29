@extends('layouts.adminpanel')
@section('title', "Dashboard")

@section('header_space')
    {{--<link rel="stylesheet" href="{{ asset('assets/plugins/datepicker/datepicker3.css') }}">--}}
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12 col-sm-12 col-xs-12">
            <div class="x_panel">
                <div class="x_title">
                    <h2>General Statistics</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="row tile_count">
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-bullhorn"></i> In Queue</span>
                            <div class="count text-info">{{ number_format($stats['queue_campaigns']) }}</div>
                            <span class="count_bottom">Campaigns</span>
                        </div>
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-paper-plane"></i> In Progress</span>
                            <div class="count text-primary">{{ number_format($stats['progress_campaigns']) }}</div>
                            <span class="count_bottom">Campaigns</span>
                        </div>
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-exclamation-triangle"></i> Spam Templates</span>
                            <div class="count red">{{ number_format($stats['spam_templates']) }}</div>
                            <span class="count_bottom">Templates</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="x_panel">
                <div class="x_title">
                    <h2>Email Statistics</h2>
                    <div class="clearfix"></div>
                </div>
                <div class="x_content">
                    <div class="row tile_count">
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-inbox"></i> Total Sent</span>
                            <div class="count green">{{ number_format($stats['sent_mails']) }}</div>
                            <span class="count_bottom">Emails</span>
                        </div>
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-paper-plane"></i> Total Left</span>
                            <div class="count">{{ number_format($stats['left_mails']) }}</div>
                            <span class="count_bottom">Emails</span>
                        </div>
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-eye"></i> Total Viewed</span>
                            <div class="count text-success">{{ number_format($stats['viewed_mails']) }}</div>
                            <span class="count_bottom">Emails</span>
                        </div>
                        <div class="col-sm-3 col-xs-6 tile_stats_count">
                            <span class="count_top"><i class="fa fa-eye-slash"></i> Not Viewed</span>
                            <div class="count text-warning">{{ number_format($stats['not_viewed_mails']) }}</div>
                            <span class="count_bottom">Emails</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('footer_space')
{{--    <script src="{{ asset("assets/plugins/datepicker/bootstrap-datepicker.js") }}"></script>--}}
    <script>
        $(document).ready(function(){

        });
    </script>
@stop