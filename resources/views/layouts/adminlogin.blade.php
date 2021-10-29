<?php
$menubars = \App\Facades\CustomHelper::get_admin_menus();
$main_bar = $menubars['main'];
$page_class = explode('.', $template);
$current_path = $menubars['active'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - {{ config('app.name') }}</title>

    <!-- Bootstrap -->
    <link href="{{ url('vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ url('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{ url('vendors/nprogress/nprogress.css') }}" rel="stylesheet">
    <link href="{{ url('vendors/animate.css/animate.min.css') }}" rel="stylesheet">
    <!-- Custom Theme Style -->
    <link href="{{ CustomHelper::custom_asset_url('css/custom.css') }}" rel="stylesheet">
</head>

<body class="page-{{ $page_class[0] }} {{ @$page_class[2] . (@$page_class[2] == 'register') ? ' login' : '' }}">
    <h1 class="text-center"><a href="{{ url('/') }}">{{ config('app.name') }}</a></h1>
    <div class="login_wrapper">
        <div class="row">
            <div class="col-sm-12">
                @include('errors.flash')
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                @section('content')@show
            </div>
        </div>
    </div>
</body>
</html>
