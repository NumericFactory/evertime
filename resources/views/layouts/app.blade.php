<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset("images/icon-transparent-70px.png") }}" />
    <title><?php echo config('app.name'); ?></title>
    <!-- css, fonts -->
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.2/toastr.min.css" rel="stylesheet" type="text/css">
    <link href="/css/animate.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" rel="stylesheet" type="text/css">
    <style>
        .fa-btn {
            margin-right: 6px;
        }
        .table-text div {
            padding-top: 6px;
        }
        .bootstrap-datetimepicker-widget.wider {
            width: 100%;
        }
        #logo-icon {display:none;}
        .mini-navbar #logo {display:none;}
        .mini-navbar #logo-icon {display:block;}
        .btn-blue, .bg-blue{
            background-color: #32BAC0;
            border-color: #32BAC0;
        }
        .btn-blue:hover{
            background-color: #319b9f;
            border-color: #319b9f;
        }

        .navbar-default .special_link a {
            background: #44bcc2;
            color: white;
        }

        .navbar-default li a:hover, .navbar-default .special_link a:hover {
            background: #319b9f !important;
        }
        .navbar-minimalize {
            background: #4cbabe;
        }

        .navbar-minimalize:hover {
            background-color: #319b9f !important;
        }
    </style>
    <!-- js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.2/toastr.min.js"></script>
</head>
<body>

    <!-- Google Tag Manager -->
    <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-PS59BW"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PS59BW');</script>
    <!-- End Google Tag Manager -->

    <div id="wrapper">

        <nav class="navbar-default navbar-static-side" role="navigation">
            <div class="sidebar-collapse">
                <ul class="nav metismenu" id="side-menu">
                    <li class="nav-header">
                        <img class='img-responsive' id='logo-icon' src='{{ asset("images/icon-transparent-70px.png") }}'>
                        <p><img class='img-responsive' id='logo' src='{{ asset("images/logo-transparent-170px.png") }}'></p>
                    </li>
                    @if (Auth::check())
                        <?php
                            $route = \Route::currentRouteName(); // resource routes
                            $route2 = Route::currentRouteAction(); // controller routes
                        ?>
                        <li class="{{ in_array($route, ['timers.index', 'timer.create', 'timer.update', 'timer.embed']) ? 'special_link' : '' }}"><a href="<?php echo url('timers'); ?>"><i class="fa fa-btn fa-list"></i> <span class="nav-label">{{ trans('translate.timers') }}</span></a></li>
                        <li class="{{ ($route2 == 'App\Http\Controllers\SettingsController@getUpdateProfile') ? 'special_link': '' }}"><a href="<?php echo url('settings/update-profile'); ?>"><i class="fa fa-btn fa-user"></i> <span class="nav-label">{{ trans('translate.profile') }}</span></a></li>

                    @endif
                </ul>
            </div>
        </nav>

        <div id="page-wrapper" class="gray-bg" style="min-height: 597px;">
            <div class="row border-bottom">
                <nav class="navbar navbar-static-top" style="margin-bottom: 0">
                    <div class="navbar-header">
                        <a class="navbar-minimalize minimalize-styl-2 btn btn-primary " href="#"><i class="fa fa-bars"></i> </a>
                    </div>
                    <ul class="nav navbar-top-links navbar-right">
                        @if (Auth::guest())
                            <li><a href="<?php echo url('register'); ?>"><i class="fa fa-btn fa-heart"></i> <span class="nav-label">{{ trans('translate.register') }}</span></a></li>
                            <li><a href="<?php echo url('login'); ?>"><i class="fa fa-btn fa-sign-in"></i> <span class="nav-label">{{ trans('translate.login') }}</span></a></li>
                        @else
                            <li><i class="fa fa-btn fa-user"></i> {{ trans('translate.welcome') }} {{ Auth::user()->name }}</li>
                            <li><a href="<?php echo url('logout'); ?>"><i class="fa fa-btn fa-sign-out"></i>{{ trans('translate.logout') }}</a></li>
                        @endif
                    </ul>
                </nav>
            </div>

            @yield('content')

            <div class="footer">
                <div>
                    <strong>Copyright</strong> {{ config('app.name') }} &copy; {{ date('Y') }} All rights reserved.
                </div>
            </div>

            <script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
            <script src="{{ asset('js/inspinia.js') }}"></script>

            <!-- Load custom Script here -->
            @yield('scripts')
        </div>
    </div>

</body>
</html>