<!DOCTYPE html>
<html lang="en">
@php
    $front_setting = getFrontData();
@endphp

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    {{-- <link rel="shortcut icon" href="{!! asset('icon.png') !!}" type="image/x-icon" /> --}}
    {{-- <link rel="shortcut icon" href="{!! asset('admin_assets/img/logo.png') !!}" type="image/x-icon" /> --}}
    <title>Time-Check</title>
    <title>@yield('title')</title>
    @include('admin.layout.css')
    <script type="text/javascript">
        var base_url = "{{ url('/') . '/' }}";
    </script>
    <style>
        /*for yellow bg*/

        @media only screen and (max-width: 650px) {
            .logo-visiability {
                display: contents;
            }
        }

        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: #F3F3F3;
            z-index: 9999;
            backdrop-filter: blur(2px);
            /* display: none; */
        }

        @-webkit-keyframes spin {
            from {
                -webkit-transform: rotate(0deg);
            }

            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        #cover-spin::after {
            content: '';
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
            width: 40px;
            height: 40px;
            border-style: solid;
            border-color: black;
            border-top-color: transparent;
            border-width: 4px;
            border-radius: 50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }

        .img-custom {
            border-radius: 50%;
            border: 1px solid #394a5a;
        }

        .btn-info {
            background: #3f729b;
            border: none;
        }

        .btn-info:hover {
            background: #3f729b;
            border: none;
        }

        .btn-info:disabled:hover {
            background: #3f729b;
            border: none;
        }

        .dataTables_paginate .paging_simple_numbers .paginate_button .current {
            background: #3f729b;
            border: none;
        }

        .paging_simple_numbers .paginate_button .current {
            background: #3f729b;
            border: none;
        }

        .sidebar {
            font-size: 12px;
            font-weight: 200;
            position: fixed;
            /* background: #416982; */
            background: #222d32;
            /* background: #27333e; */
            /* background: #27333e; */
        }

        .sidebar #side-menu ul li {
            font-size: 12px;
            font-weight: 200
        }

        .sidebar #side-menu ul li i {
            font-size: 12px;
            font-weight: 200;
        }

        .sidebar #side-menu li i {
            font-size: 16px;
            /* text-shadow: 2px 3px 6px #3f729b; */
        }

        .navbar-header ul li i {
            font-size: 12px;
            /* text-shadow: 2px 3px 6px #3f729b; */
        }

        .sidebar .nav-second-level {
            font-size: 11px;
            font-weight: 200;
            background: #2c3b41;
            /* background: #394a5a; */
            /* text-shadow: 2px 3px 6px #3f729b; */
        }

        .sidebar .nav-third-level {
            font-size: 11px;
            font-weight: 200;
            background: #2c3b41;
            /* background: #485b6d; */
        }

        .navbar-header {
            /* background: #112B3C; */
            /* background: #1b384e; */
            /* background: #222d32; */
            background: #27333e;
            /* background: #222a48; */
            /* background: #336186; */
            /* background: #1d405c; */
            /* background: #07293e; */
            /* border-bottom: 1px solid #3f729b; */
        }

        #side-menu li a {
            color: #fff;
            border-left: 0px solid #2f323e;
        }

        .top-left-part .dark-logo {
            display: block;

        }

        .tiMenu {
            color: #fff;
        }

        /* .sidebar {
            background: #27333e;
            box-shadow: 1px 0px 20px rgba(0, 0, 0, 0.08);
        } */

        .hideMenu {
            color: #fff;
        }

        #side-menu ul>li>a.active {
            /* color: #41b3f9; */
            color: #EDDF10;
            font-weight: 400;
        }

        #side-menu ul>li>a:hover {
            color: #fff;
        }

        /*for yellow bg*/

        .bg-title .breadcrumb {
            background: 0 0;
            margin-bottom: 0;
            float: none;
            padding: 0;
            margin-bottom: 9px;
            font-weight: 700;
            color: #777;
            font-size: 13px;
        }


        .select2-container .select2-selection--single .select2-selection__rendered {
            height: auto;
            margin-top: -6px;
            padding-left: 0;
            padding-right: 0;
        }

        .select2-container .select2-selection--single {
            box-sizing: border-box;
            cursor: pointer;
            display: block;
            height: 35px;
        }

        .select2-container--default .select2-selection--single,
        .select2-selection .select2-selection--single {
            border: 1px solid #d2d6de;
            border-radius: 0;
            padding: 8px 11px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px;
            position: absolute;
            top: 4px;
            right: 1px;
            width: 20px;
        }

        .breadcrumbColor a {
            color: #3f729b !important;
            /* color: #27333e !important; */
            /* color: #41b3f9 !important; */
        }

        tr td {
            color: black !important;
            font-size: 12px;
        }

        th td {
            color: black !important;
            font-size: 11px;
        }

        .tr_header {
            background-color: #EDF1F5 !important;
            text-transform: uppercase;
            font-size: 11px;
            font-style: :normal;
        }

        .sidebar #side-menu li a:hover {
            background: #3f729b
        }

        .sidebar ul li a:hover {
            background: #3f729b;
        }

        table.dataTable thead th,
        table.dataTable thead td {
            padding: 10px 18px;
            border-bottom: 1px solid #e4e7ea;
        }

        .btnColor {
            color: #fff !important;
        }

        .validateRq {
            color: red;
        }

        .panel .panel-heading {
            border-radius: 0;
            font-weight: 500;
            font-size: 13px;
            padding: 10px 25px;
            /* background-color: #485b6d !important; */
            /* background-color: #41b3f9 !important; */
            background-color: #3f729b !important;
            /* background-color: #27333e !important; */
            border: none
        }

        .btn_style {
            width: 106px;
        }

        .error {
            color: red;
        }


        /*!
        // 3. Loader
        // --------------------------------------------------*/
        .loader {
            top: 0;
            left: 0;
            position: fixed;
            opacity: 0.8;
            z-index: 10000000;
            background: Black;
            height: 100%;
            width: 100%;
            margin: auto;
        }

        .strip-holder {
            top: 50%;
            -webkit-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            transform: translateY(-50%);
            left: 50%;
            margin-left: -50px;
            position: relative;
        }

        .strip-1,
        .strip-2,
        .strip-3 {
            width: 20px;
            height: 20px;
            /* background: #0072bc; */
            background: #27333e;
            position: relative;
            -webkit-animation: stripMove 2s ease infinite alternate;
            animation: stripMove 2s ease infinite alternate;
            -moz-animation: stripMove 2s ease infinite alternate;
        }

        .strip-2 {
            -webkit-animation-duration: 2.1s;
            animation-duration: 2.1s;
            background-color: #27333e;
            /* background-color: #23a8ff; */
        }

        .strip-3 {
            -webkit-animation-duration: 2.2s;
            animation-duration: 2.2s;
            /* background-color: #89d1ff; */
            background-color: #27333e;
        }

        @-webkit-keyframes stripMove {
            0% {
                transform: translate3d(0px, 0px, 0px);
                -webkit-transform: translate3d(0px, 0px, 0px);
                -moz-transform: translate3d(0px, 0px, 0px);
            }

            50% {
                transform: translate3d(0px, 0px, 0px);
                -webkit-transform: translate3d(0px, 0px, 0px);
                -moz-transform: translate3d(0px, 0px, 0px);
                transform: scale(4, 1);
                -webkit-transform: scale(4, 1);
                -moz-transform: scale(4, 1);
            }

            100% {
                transform: translate3d(-50px, 0px, 0px);
                -webkit-transform: translate3d(-50px, 0px, 0px);
                -moz-transform: translate3d(-50px, 0px, 0px);
            }
        }

        @-moz-keyframes stripMove {
            0% {
                transform: translate3d(-50px, 0px, 0px);
                -webkit-transform: translate3d(-50px, 0px, 0px);
                -moz-transform: translate3d(-50px, 0px, 0px);
            }

            50% {
                transform: translate3d(0px, 0px, 0px);
                -webkit-transform: translate3d(0px, 0px, 0px);
                -moz-transform: translate3d(0px, 0px, 0px);
                transform: scale(4, 1);
                -webkit-transform: scale(4, 1);
                -moz-transform: scale(4, 1);
            }

            100% {
                transform: translate3d(50px, 0px, 0px);
                -webkit-transform: translate3d(50px, 0px, 0px);
                -moz-transform: translate3d(50px, 0px, 0px);
            }
        }

        @keyframes stripMove {
            0% {
                transform: translate3d(-50px, 0px, 0px);
                -webkit-transform: translate3d(-50px, 0px, 0px);
                -moz-transform: translate3d(-50px, 0px, 0px);
            }

            50% {
                transform: translate3d(0px, 0px, 0px);
                -webkit-transform: translate3d(0px, 0px, 0px);
                -moz-transform: translate3d(0px, 0px, 0px);
                transform: scale(4, 1);
                -webkit-transform: scale(4, 1);
                -moz-transform: scale(4, 1);
            }

            100% {
                transform: translate3d(50px, 0px, 0px);
                -webkit-transform: translate3d(50px, 0px, 0px);
                -moz-transform: translate3d(50px, 0px, 0px);
            }
        }
    </style>
</head>

<body class="fix-header" onload="addMenuClass()">
    <!-- ============================================================== -->
    <!-- Preloader -->
    <!-- ============================================================== -->
    <div class="preloader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2"
                stroke-miterlimit="10" />
        </svg>
    </div>

    <!-- ============================================================== -->
    <!-- Wrapper -->
    <!-- ============================================================== -->
    <div id="wrapper">
        @include('admin.layout.navbar')
        @include('admin.layout.sidebar')
        <div id="page-wrapper">
            @yield('content')
        </div>
    </div>
    @include('admin.layout.javascript')
    @yield('page_scripts')
</body>

</html>
