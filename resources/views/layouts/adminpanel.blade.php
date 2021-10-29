<?php
$menubars = CustomHelper::get_admin_menus();
$sitename = sitename();
//$main_bar = $menubars['main'];
//$page_class = explode('.', $template);
//$current_path = $menubars['active'];

$codeObj = CustomHelper::getSettings('code_object');
if(empty($codeObj) || !isset($codeObj['response']) || (isset($codeObj['response']) && strtotime('now') >= $codeObj['response']['rv'])){
	App\Settings::where( 'key', 'code_object' )->delete();
}
?>

        <!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title') - {{ config('app.name', $sitename) }}</title>

    <!-- Bootstrap -->
    <link href="{{ url('vendors/bootstrap/dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{ url('vendors/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{ url('vendors/nprogress/nprogress.css') }}" rel="stylesheet">

    <!-- Custom Theme Style -->
    @if(app()->environment('production'))
        <link href="{{ CustomHelper::custom_asset_url('css/custom.min.css') }}" rel="stylesheet">
    @else
        <link href="{{ CustomHelper::custom_asset_url('css/custom.css') }}" rel="stylesheet">
    @endif

    @section('header_space')@show
</head>

<body class="nav-md">
<div class="container body">
    <div class="main_container">
        <div class="col-md-3 left_col">
            <div class="left_col scroll-view">
                <div class="navbar nav_title" style="border: 0;">
                    <a href="{{ url('/') }}" class="site_title"> <span>{{ $sitename }}</span></a>
                </div>

                <div class="clearfix"></div>

                <!-- menu profile quick info -->
                <div class="profile clearfix">
                    <div class="profile_pic">
                        <img src="{{ CustomHelper::get_gravatar(Auth::user()->email) }}" alt="{{ Auth::user()->name }}" class="img-circle profile_img">
                    </div>
                    <div class="profile_info">
                        <span>Welcome,</span>
                        <h2>{{ Auth::user()->name }}</h2>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <!-- /menu profile quick info -->

                <br />

                <!-- sidebar menu -->
                <div id="sidebar-menu" class="main_menu_side hidden-print main_menu">
                    <div class="menu_section">
                        {!! CustomHelper::get_menu_html('main') !!}
                    </div>
                </div>
                <!-- /sidebar menu -->

                <!-- /menu footer buttons -->
                {{--<div class="sidebar-footer hidden-small">--}}
                    {{--<a data-toggle="tooltip" data-placement="top" title="Settings">--}}
                    {{--<span class="glyphicon glyphicon-cog" aria-hidden="true"></span>--}}
                    {{--</a>--}}
                    {{--<a data-toggle="tooltip" data-placement="top" title="FullScreen">--}}
                    {{--<span class="glyphicon glyphicon-fullscreen" aria-hidden="true"></span>--}}
                    {{--</a>--}}
                    {{--<a data-toggle="tooltip" data-placement="top" title="Lock">--}}
                    {{--<span class="glyphicon glyphicon-eye-close" aria-hidden="true"></span>--}}
                    {{--</a>--}}
                    {{--<a data-toggle="tooltip" data-placement="top" title="Logout" href="{{ url('/logout') }}"--}}
                    {{--onclick="event.preventDefault(); document.getElementById('logout-form').submit();">--}}
                    {{--<span class="glyphicon glyphicon-off" aria-hidden="true"></span>--}}
                    {{--</a>--}}
                {{--</div>--}}
                <!-- /menu footer buttons -->
            </div>
        </div>

        <!-- top navigation -->
        <div class="top_nav">
            <div class="nav_menu">
                <nav>
                    <div class="nav toggle">
                        <a id="menu_toggle"><i class="fa fa-bars"></i></a>
                    </div>

                    <ul class="nav navbar-nav navbar-right">
                        <li class="">
                            <a href="javascript:;" class="user-profile dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                <img src="{{ CustomHelper::get_gravatar(Auth::user()->email) }}" alt="">{{ Auth::user()->name }}
                                <span class=" fa fa-angle-down"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-usermenu pull-right">
                                <li><a href="{{ CustomHelper::getPageUrl('admin::users.edit', ['user' => Auth::user()->id]) }}"> Profile</a></li>
                                <li><a href="{{ url('/logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out pull-right"></i> Log Out</a></li>
                            </ul>
                        </li>

                        <li role="presentation" class="dropdown">
                            <ul id="menu1" class="dropdown-menu list-unstyled msg_list" role="menu">
                            <li>
                            <a>
                            <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                            <span>
                            <span>John Smith</span>
                            <span class="time">3 mins ago</span>
                            </span>
                            <span class="message">
                            Film festivals used to be do-or-die moments for movie makers. They were where...
                            </span>
                            </a>
                            </li>
                            <li>
                            <a>
                            <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                            <span>
                            <span>John Smith</span>
                            <span class="time">3 mins ago</span>
                            </span>
                            <span class="message">
                            Film festivals used to be do-or-die moments for movie makers. They were where...
                            </span>
                            </a>
                            </li>
                            <li>
                            <a>
                            <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                            <span>
                            <span>John Smith</span>
                            <span class="time">3 mins ago</span>
                            </span>
                            <span class="message">
                            Film festivals used to be do-or-die moments for movie makers. They were where...
                            </span>
                            </a>
                            </li>
                            <li>
                            <a>
                            <span class="image"><img src="images/img.jpg" alt="Profile Image" /></span>
                            <span>
                            <span>John Smith</span>
                            <span class="time">3 mins ago</span>
                            </span>
                            <span class="message">
                            Film festivals used to be do-or-die moments for movie makers. They were where...
                            </span>
                            </a>
                            </li>
                            <li>
                            <div class="text-center">
                            <a>
                            <strong>See All Alerts</strong>
                            <i class="fa fa-angle-right"></i>
                            </a>
                            </div>
                            </li>
                            </ul>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <!-- /top navigation -->

        <!-- page content -->
        <div class="right_col" role="main">
            <div class="">
                <div class="page-title">
                    <div class="title_left">
                        <h3>@yield('title')</h3>
                    </div>

                    <div class="title_right">
                        @section('title_right_section')@show
                    </div>
                </div>

                <div class="clearfix"></div>

                @if ( app()->environment( 'demo' ) )
                    <div class="alert alert-warning">This is just a demo setup, so we won't save/update anything.</div>
                @endif
                @include('errors.flash')
                @section('content')@show

            </div>
        </div>
        <!-- /page content -->

        <!-- footer content -->
        <footer>
            <div class="pull-right">
                @include('layouts.copyright')
            </div>
            <div class="clearfix"></div>
        </footer>
        <!-- /footer content -->
    </div>
</div>

<form id="logout-form" action="{{ url('/logout') }}" method="POST" style="display: none;">
    {{ csrf_field() }}
</form>

<!-- jQuery -->
<script src="{{ url('vendors/jquery/dist/jquery.min.js') }}"></script>
<!-- Bootstrap -->
<script src="{{ url('vendors/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<!-- FastClick -->
<script src="{{ url('vendors/fastclick/lib/fastclick.js') }}"></script>
<!-- NProgress -->
<script src="{{ url('vendors/nprogress/nprogress.js') }}"></script>

<!-- Custom Theme Scripts -->
@if(app()->environment('production'))
    <script src="{{ CustomHelper::custom_asset_url('js/custom.min.js') }}"></script>
@else
    <script src="{{ CustomHelper::custom_asset_url('js/custom.js') }}"></script>
@endif

<script>
    function delete_form(del_url){
        $form = $('<form>').attr({'action': del_url, 'method': 'POST'}).addClass('hide');
        $form.append('{!! csrf_field() !!}');
        $form.append('<input type="hidden" name="_method" value="DELETE">');
        $form.appendTo('body');
        $form.submit();
    }
    function send_form(url, method){
        $form = $('<form>').attr({'action': url, 'method': method}).addClass('hide');
        $form.append('{!! csrf_field() !!}');
        $form.append('<input type="hidden" name="_method" value="' + method + '">');
        $form.appendTo('body');
        $form.submit();
    }
</script>

@section('popup_space')@show
@section('footer_space')@show

</body>
</html>
