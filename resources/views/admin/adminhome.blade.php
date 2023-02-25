@extends('admin.master')
@section('content')
@section('title')
    @lang('dashboard.dashboard')
@endsection
<style>
    .dash_image {
        width: 60px;
    }

    .my-custom-scrollbar {
        position: relative;
        height: 280px;
        overflow: auto;
    }

    .table-wrapper-scroll-y {
        display: block;
    }

    tbody {
        display: block;
        height: 300px;
        overflow: auto;
    }

    thead,
    tbody tr {
        display: table;
        width: 100%;
        table-layout: fixed;
    }

    thead {
        width: calc(100% - 1em)
    }

    .leaveApplication {
        overflow-x: hidden;
        height: 210px;
    }

    .noticeBord {
        overflow-x: hidden;
        height: 210px;
    }

    .preloader {
        position: fixed;
        left: 0px;
        top: 0px;
        z-index: 9999;
        /* background: url('../images/timer.gif') 50% 50% no-repeat rgb(249, 249, 249); */
        opacity: 0.1;
    }

    /* Hide scrollbar for Chrome, Safari and Opera */
    .scroll-hide::-webkit-scrollbar {
        display: none;
    }

    /* Hide scrollbar for IE, Edge and Firefox */
    .scroll-hide {
        -ms-overflow-style: none;
        /* IE and Edge */
        scrollbar-width: none;
        /* Firefox */
    }

    */
</style>

<script>
    function loadingAjax(div_id) {
        $("#" + div_id).html('<img src="ajax-loader.gif"> saving...');
        $.ajax({
            type: "GET",
            url: "script.php",
            data: "name=John&id=28",
            success: function(msg) {
                $("#" + div_id).html(msg);
            }
        });
    }
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="#"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a>
                </li>
            </ol>
        </div>
        <div class="pull-right" style="margin-right:12px;" hidden>
            <input data-id="{{ $setting_sync_live->id }}" class="toggle-class" type="checkbox" data-onstyle="info"
                data-offstyle="#3f729b" data-toggle="toggle" data-on="LIVE ON" data-off="LIVE OFF"
                {{ $setting_sync_live->status ? 'checked' : '' }}>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title"> @lang('dashboard.total_employee') </h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/employee.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-success"></i> <span
                            class="counter text-success">{{ $totalEmployee }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('common.designation')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span
                            class="counter text-purple">{{ $totalDesignation }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.total_department')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span
                            class="counter text-purple">{{ $totalDepartment }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('common.sub_department')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span
                            class="counter text-purple">{{ $totalUnit }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('common.cost_center')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span
                            class="counter text-purple">{{ $totalCostcenter }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('common.branch')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/department.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-purple"></i> <span
                            class="counter text-purple">{{ $totalContractor }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.total_present')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/present.png') }}">
                    </li>
                    <li class="text-right"><i class="ti-arrow-up text-info"></i> <span
                            class="counter text-info">{{ $totalAttendance }}</span></li>
                </ul>
            </div>
        </div>

        <div class="col-lg-3 col-sm-6 col-xs-12">
            <div class="white-box analytics-info">
                <h3 class="box-title">@lang('dashboard.total_absent')</h3>
                <ul class="list-inline two-part">
                    <li>
                        <img class="dash_image" height="40" src="{{ asset('admin_assets/img/absent.png') }}">
                    </li>
                    <li class="text-right"><i id="absentDetail" class="ti-arrow-down text-danger"></i>
                        <span class="counter text-danger">{{ $totalAbsent }}</span>
                    </li>
                </ul>

            </div>
        </div>

    </div>


    <div id="preloaders" class="preloader"></div>
    <div class="row" style="display: none">
        <!-- manual attendance  -->
        <div class="row" style="margin-left: 14px;margin-right: 14px">
            <div class="panel">
                <div class="panel-heading"><span style="color: white "><i
                            class="mdi mdi-clipboard-text fa-fw"></i>Generate Attendance Report :</span></div>
                <div class="text-left" style="font-size: 13px;margin:12px">
                    @if ($message = Session::get('success'))
                        <div class="alert alert-success alert-block alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert">x</button>
                            <strong>{{ $message }}</strong>
                        </div>
                    @endif
                    @if ($message = Session::get('error'))
                        <div class="alert alert-danger alert-block alert-dismissable">
                            <button type="button" class="close" data-dismiss="alert">x</button>
                            <strong>{{ $message }}</strong>
                        </div>
                    @endif
                </div>
                <div class="panel-body">
                    <form action="{{ url('cronjob/manualAttendance') }}">
                        <div class="col-md-2" style="margin-left: -10px">
                            <label cLass="form-label">From Date :</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="col-md-2" style="margin-left: -10px">
                            <label cLass="form-label">To Date :</label>
                            <input type="date" name="date1" class="form-control" required>
                        </div>
                        <div class="col-md-1" style="margin-top: 28px">
                            <button onclick="loading(false);" type="submit" class="btn btn-info">Generate
                                Report</button>
                        </div>
                    </form>
                    <div class="text-right" style="margin-top: 28px;">
                        <a href="{{ route('access.log', ['redirect' => 1]) }}"><button type="submit"
                                class="btn btn-info">Import
                                Attendance Log</button></a>
                    </div>
                    <div style="margin-left: 12px;margin-right: 16px;" class="text-right">
                        @php
                            $datetime = date('Y-m-d 10:00:00', strtotime('-24 HOURS'));
                            $accepted_datetime = new DateTime($datetime);
                            $log_date = new DateTime($last_log_date);
                            $bool = $log_date >= $accepted_datetime;
                            // dd($bool, $log_date, $accepted_datetime, $datetime);
                        @endphp
                        <p style="margin-top: 12px;"><b class="text-right" style="font-size: 12px;">
                                <?php
                                if (!$bool) {
                                    echo "<b class='text-right' style='color: red;ont-size: 12px;'>" . 'Attendance Log Update on' . '  ' . '(' . $last_log_date . ')' . '.' . '</b>';
                                } else {
                                    echo "<b style='color: green'>" . 'Attendance Log Update on' . '  ' . '(' . $last_log_date . ')' . ',' . '</b>';
                                }
                                ?>
                            </b>
                        </p>
                        <p style="font-size: 12px;margin-top: -12px;margin-bottom: -12px;">Note: Report and cannot
                            generate report for current date.</p>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if ($ip_attendance_status == 1)
            <!-- employe attendance  -->
            @php
                $logged_user = employeeInfo();
            @endphp
            <div class="col-md-6">
                <div class="white-box">
                    <h3 class="box-title">Hey {!! $logged_user[0]->user_name !!} please Check in/out your attendance</h3>
                    <hr>
                    <div class="noticeBord">
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">×</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert"
                                    aria-hidden="true">×</button>
                                <strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif
                        <form action="{{ route('ip.attendance') }}" method="POST">
                            {{ csrf_field() }}
                            <p>Your IP is {{ \Request::ip() }}</p>
                            {{-- <p>Your IP is {{ getIp() }}</p> --}}
                            <input type="hidden" name="employee_id" value="{{ $logged_user[0]->user_name }}">

                            <input type="hidden" name="ip_check_status" value="{{ $ip_check_status }}">
                            <input type="hidden" name="finger_id" value="{{ $logged_user[0]->finger_id }}">
                            @if ($count_user_login_today > 0 && $count_user_login_today % 2 == 0)
                                <button class="btn btn-danger">
                                    <i class="fa fa-clock-o"> </i>
                                    Check Out
                                </button>
                            @else
                                <button class="btn btn-primary">
                                    <i class="fa fa-clock-o"> </i>
                                    Check In
                                </button>
                            @endif

                        </form>
                    </div>
                </div>
            </div>

            <!-- end attendance  -->
        @endif

        <div class="col-md-12 col-lg-12 col-sm-12" style="display:inline-table;">
            <div class="white-box">
                <div class="box-title"> @lang('dashboard.today_attendance') </div>
                <div class="table-responsive scroll-hide" style="padding: 4px;">
                    <table class="table table-hover table-borderless manage-u-table">
                        <thead>
                            <tr class="">
                                <td class="text-center">#</td>
                                <td>@lang('dashboard.photo')</td>
                                <td>Employee ID</td>
                                <td>@lang('common.name')</td>
                                <td>Time</td>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($attendanceData) > 0)
                                {{ $dailyAttendanceSl = null }}
                                @foreach ($attendanceData as $dailyAttendance)
                                    <tr>
                                        <td class="text-center">{{ ++$dailyAttendanceSl }}</td>
                                        <td>
                                            @if ($dailyAttendance->photo != '')
                                                <img style=" width: 70px; " src="{!! asset('uploads/employeePhoto/' . $dailyAttendance->photo) !!}"
                                                    alt="user-img" class="img-circle">
                                            @else
                                                <img style=" width: 70px; " src="{!! asset('admin_assets/img/default.png') !!}"
                                                    alt="user-img" class="img-circle">
                                            @endif
                                        </td>
                                        <td>{{ $dailyAttendance->finger_id }}</td>
                                        <td>{{ $dailyAttendance->first_name . ' ' . $dailyAttendance->last_name }}
                                            <br /><span
                                                class="text-muted">{{ App\Model\Department::where('department_id', $dailyAttendance->department_id)->first()->department_name }}</span>
                                        </td>
                                        <td>{{ date('H:i', strtotime($dailyAttendance->datetime)) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="text-center" colspan="8">@lang('common.no_data_available')</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        @if (count($notice) > 0)
            <div class="col-md-6">
                <div class="white-box">
                    <h3 class="box-title">@lang('dashboard.notice_board')</h3>
                    <hr>
                    <div class="noticeBord scroll-hide">
                        @foreach ($notice as $row)
                            @php
                                $noticeDate = strtotime($row->publish_date);
                            @endphp
                            <div class="comment-center p-t-10">
                                <div class="comment-body">
                                    <div class="user-img"><i style="font-size: 31px"
                                            class="fa fa-flag-checkered text-info"></i>
                                    </div>
                                    <div class="mail-contnet">
                                        <h5 class="text-danger">{{ substr($row->title, 0, 70) }}..</h5><span
                                            class="time">Published Date:
                                            {{ date(' d M Y ', $noticeDate) }}</span>
                                        <br /><span class="mail-desc">
                                            @lang('notice.published_by'): {{ $row->createdBy->first_name }}
                                            {{ $row->createdBy->last_name }}<br>
                                            @lang('notice.description'): {!! substr($row->description, 0, 80) !!}..
                                        </span>
                                        <a href="{{ url('notice/' . $row->notice_id) }}"
                                            class="btn m-r-5 btn-rounded btn-outline btn-info">@lang('common.read_more')</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif



        @if (count($upcoming_birtday) > 0)
            <div class="col-md-6">
                <div class="white-box">
                    <h3 class="box-title">@lang('dashboard.upcoming_birthday')</h3>
                    <hr>
                    <div class="leaveApplication scroll-hide">
                        @foreach ($upcoming_birtday as $employee_birthdate)
                            <div class="comment-center p-t-10">
                                <div class="comment-body">
                                    @if ($employee_birthdate->photo != '')
                                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $employee_birthdate->photo) !!}" alt="user"
                                                class="img-circle"></div>
                                    @else
                                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user"
                                                class="img-circle"></div>
                                    @endif
                                    <div class="mail-contnet">

                                        @php
                                            $date_of_birth = $employee_birthdate->date_of_birth;
                                            $separate_date = explode('-', $date_of_birth);
                                            
                                            $date_current_year = date('Y') . '-' . $separate_date[1] . '-' . $separate_date[2];
                                            
                                            $create_date = date_create($date_current_year);
                                        @endphp

                                        <h5>{{ $employee_birthdate->first_name }}
                                            {{ $employee_birthdate->last_name }}</h5><span
                                            class="time">{{ date_format(date_create($employee_birthdate->date_of_birth), 'D dS F Y') }}</span>
                                        <br />

                                        <span class="mail-desc">
                                            @if ($date_current_year == date('Y-m-d'))
                                                <b>Today is
                                                    @if ($employee_birthdate->gender == 'Male')
                                                        His
                                                    @else
                                                        Her
                                                    @endif
                                                    Birtday Wish
                                                    @if ($employee_birthdate->gender == 'Male')
                                                        Him
                                                    @else
                                                        Her
                                                    @endif
                                                </b>
                                            @else
                                                Wish
                                                @if ($employee_birthdate->gender == 'Male')
                                                    Him
                                                @else
                                                    Her
                                                @endif
                                                on {{ date_format($create_date, 'D dS F Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @if (count($leaveApplication) > 0)
            <div class="col-md-6">
                <div class="white-box">
                    <h3 class="box-title">@lang('dashboard.recent_leave_application')</h3>
                    <hr>
                    <div class="leaveApplication scroll-hide">
                        @foreach ($leaveApplication as $leaveApplication)
                            <div class="comment-center p-t-10 {{ $leaveApplication->leave_application_id }}">
                                <div class="comment-body">
                                    @if ($leaveApplication->employee->photo != '')
                                        <div class="user-img"> <img src="{!! asset('uploads/employeePhoto/' . $leaveApplication->employee->photo) !!}" alt="user"
                                                class="img-circle"></div>
                                    @else
                                        <div class="user-img"> <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user"
                                                class="img-circle"></div>
                                    @endif
                                    <div class="mail-contnet">
                                        @php
                                            $d = strtotime($leaveApplication->created_at);
                                        @endphp
                                        <h5>{{ $leaveApplication->employee->first_name }}
                                            {{ $leaveApplication->employee->last_name }}</h5><span
                                            class="time">{{ date('d M Y h:i: a', $d) }}</span>
                                        <span class="label label-rouded label-info">PENDING</span>
                                        <br /><span class="mail-desc" style="max-height: none">
                                            @lang('leave.leave_type') :
                                            {{ $leaveApplication->leaveType->leave_type_name }}<br>
                                            @lang('leave.request_duration') :
                                            {{ dateConvertDBtoForm($leaveApplication->application_from_date) }}
                                            To
                                            {{ dateConvertDBtoForm($leaveApplication->application_to_date) }}<br>
                                            @lang('leave.number_of_day') : {{ $leaveApplication->number_of_day }}
                                            <br>
                                            @lang('leave.purpose') : {{ $leaveApplication->purpose }}
                                        </span>

                                        <a href="javacript:void(0)" data-status=2
                                            data-leave_application_id="{{ $leaveApplication->leave_application_id }}"
                                            class="btn remarksForLeave btn btn-rounded btn-success btn-outline m-r-5"><i
                                                class="ti-check text-success m-r-5"></i>@lang('common.approve')</a>
                                        <a href="javacript:void(0)" data-status=3
                                            data-leave_application_id="{{ $leaveApplication->leave_application_id }}"
                                            class="btn-rounded remarksForLeave btn btn-danger btn-outline"><i
                                                class="ti-close text-danger m-r-5"></i>@lang('common.reject')</a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('page_scripts')
{{-- <script>
    $(window).load(function() {
        $("#preloaders").fadeOut(2000);
    });
</script> --}}

<script type="text/javascript">
    document.onreadystatechange = function() {
        switch (document.readyState) {
            case "loading":
                window.documentLoading = true;
                break;
            case "complete":
                window.documentLoading = false;
                break;
            default:
                window.documentLoading = false;
        }
    }

    function loading($bool) {
        // $("#preloaders").fadeOut(1000);
        if ($bool == true) {
            $.toast({
                heading: 'success',
                text: 'Processing Please Wait !',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 1
            });
            window.setTimeout(function() {
                location.reload()
            }, 3000);
        }
        $("#preloaders").fadeOut(1000);
    }


    // if (window.documentLoading = true) {
    //     $("#preloaders").fadeOut(1000);
    // }

    // $(document).on('click', '.loading', function() {
    //     $("#preloaders").fadeOut(1000);
    // });
</script>

<link href="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/css/site.css') !!}" rel="stylesheet" type="text/css" />
<script src="{!! asset('admin_assets/plugins/bower_components/news-Ticker-Plugin/scripts/jquery.bootstrap.newsbox.min.js') !!}"></script>
<script type="text/javascript">
    (function() {

        $(".demo1").bootstrapNews({
            newsPerPage: 2,
            autoplay: true,
            pauseOnHover: true,
            direction: 'up',
            newsTickerInterval: 4000,
            onToDo: function() {
                //console.log(this);
            }
        });

    })();

    // $(document).on('click', '.importLog', function(event) {
    //     var action = "{{ URL::to('cronjob/manualLogrun') }}";
    //     $.ajax({
    //         type: 'GET',
    //         url: action,
    //         data: {
    //             '_token': $('input[name=_token]').val()
    //         },
    //         success: function() {
    //             $.toast({
    //                 heading: 'success',
    //                 text: 'Peocessing Please Wait !',
    //                 position: 'top-right',
    //                 loaderBg: '#ff6849',
    //                 icon: 'success',
    //                 hideAfter: 3000,
    //                 stack: 6
    //             });
    //             window.setTimeout(function() {
    //                 location.reload()
    //             }, 3000);
    //         }
    //     });
    // });

    $(document).on('click', '.remarksForLeave', function() {

        var actionTo = "{{ URL::to('approveOrRejectLeaveApplication') }}";
        var leave_application_id = $(this).attr('data-leave_application_id');
        var status = $(this).attr('data-status');

        if (status == 2) {
            var statusText = "Are you want to approve leave application?";
            var btnColor = "#2cabe3";
        } else {
            var statusText = "Are you want to reject leave application?";
            var btnColor = "red";
        }

        swal({
                title: "",
                text: statusText,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: btnColor,
                confirmButtonText: "Yes",
                closeOnConfirm: false
            },
            function(isConfirm) {
                var token = '{{ csrf_token() }}';
                if (isConfirm) {
                    $.ajax({
                        type: 'POST',
                        url: actionTo,
                        data: {
                            leave_application_id: leave_application_id,
                            status: status,
                            _token: token
                        },
                        success: function(data) {
                            if (data == 'approve') {
                                swal({
                                        title: "Approved!",
                                        text: "Leave application approved.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                        }
                                    });

                            } else {
                                swal({
                                        title: "Rejected!",
                                        text: "Leave application rejected.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + leave_application_id).fadeOut();
                                        }
                                    });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;

    });

    /* document.getElementById('absentDetail').addEventListener('click', function() {
        document.getElementById('show_details').classList.toggle('hidden');
    }); */
    /* 
        if ($('.pagination').find('li.active span').html() != 1) {
            $('#absentDetail').trigger('click');
        } */
</script>
<script>
    $(function() {
        $('.toggle-class').change(function() {
            var status = $(this).prop('checked') == true ? 1 : 0;
            var id = $(this).data('id');
            var action = "{{ URL::to('admin/pushSwitch') }}";
            $.ajax({
                type: "GET",
                dataType: "json",
                url: action,
                data: {
                    'status': status,
                    'id': id,
                    // '_token': $('input[name=_token]').val()
                },
                success: function(data) {
                    console.log(data.success)
                }
            });
        })
    })
</script>
@endsection
