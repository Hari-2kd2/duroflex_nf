@extends('admin.master')
@section('content')
    <div id="preloader" class="preloader hidden"></div>
@section('title')
    @lang('attendance.calculate_attendance')
@endsection
<style>
    .departmentName {
        position: relative;
    }

    #department_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%;
        width: 100%;
        height: 100%;
    }

    /* .loader {
        border: 6px solid #f3f3f3;
        border-radius: 50%;
        border-top: 6px solid #3E729A;
        width: 60px;
        height: 60px;
        -webkit-animation: spin 2s linear infinite;
        animation: spin 2s linear infinite;
    }

    @-webkit-keyframes spin {
        0% {
            -webkit-transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    } */
</style>
<script>
    jQuery(function() {
        $("#employeeAttendance").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">ï¿½</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">ï¿½</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif
                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'generateReport', 'id' => 'generateReport', 'method' => 'GET']) }}
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.from_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required from_date" readonly
                                            placeholder="@lang('common.from_date')" name="from_date"
                                            value="@if (isset($_REQUEST['from_date'])) {{ $_REQUEST['from_date'] }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.to_date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required to_date" readonly
                                            placeholder="@lang('common.to_date')" name="to_date"
                                            value="@if (isset($_REQUEST['to_date'])) {{ $_REQUEST['to_date'] }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter"
                                            style="margin-top: 25px;height:36px;width: 150px;"
                                            class="btn btn-instagram btn-md" value="Recompute">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="loader"></div> --}}
                <div id="cover-spin" class="hidden"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    $(".RecomputeBtn").click(function() {
        $(".fix-header").addClass('hidden');
        $("#preloader").removeClass('hidden');
        setInterval(() => {
            $(".fix-header").removeClass('hidden');
            $("#preloader").addClass('hidden');
        }, 5000);
    });

    $(".Recompute").click(function() {
        var from_date = $('.from_date').val();
        var to_date = $('.to_date').val();
        $("#cover-spin").removeClass('hidden');
        $.ajax({
            type: "GET",
            url: "generateReport",
            data: {
                from_date: from_date,
                to_date: to_date,
                _token: $('input[name=_token]').val()
            },
            success: function(data) {
                $("#cover-spin").addClass('hidden');
                if (data != 'success') {
                    $.toast({
                        heading: 'Warning',
                        text: 'Something Error Found !, Please try again. !',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'success',
                        hideAfter: 1000,
                        stack: 1
                    });
                } else {

                    $.toast({
                        heading: 'success',
                        text: 'Attendance calculation in Progress!',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'success',
                        hideAfter: 5000,
                        stack: 1
                    });

                }

            }
        });
    });
</script>
@endsection
