@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.employee_attendance')
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
</style>

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
                                {{ Form::open(['route' => 'manualAttendance.filter', 'id' => 'employeeAttendance', 'method' => 'POST']) }}
                                <div class="col-md-2"></div>
                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.branch')<span
                                            class="validateRq">*</span></label>
                                    <div class="form-group">
                                        <select class="form-control employee_id select2 required" required
                                            name="branch_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($branchList as $key => $value)
                                                @if ($key > 0)
                                                    <option value="{{ $key }}"
                                                        @if (isset($_REQUEST['branch_id'])) @if ($_REQUEST['branch_id'] == $key) {{ 'selected' }} @endif
                                                        @endif>
                                                        {{ $value }}
                                                    </option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="control-label" for="email">@lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" class="form-control dateField required" readonly
                                            placeholder="@lang('common.date')" name="date" id="manualDate"
                                            value="@if (isset($_REQUEST['date'])) {{ $_REQUEST['date'] }}@else{{ dateConvertDBtoForm(date('Y-m-d')) }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <input type="submit" id="filter"
                                            style="margin-top: 25px; width: 100px;height:36px" class="btn btn-info "
                                            value="@lang('common.filter')">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>

                        @if (count($results) > 0)

                            <br>
                            <div class="table-responsive">

                                <table id="myTable" class="table table-bordered">

                                    <thead class="tr_header">
                                        <tr>
                                            <th style="width:60px;">@lang('common.serial')</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            <th>Employee Id</th>
                                            <th>@lang('attendance.in_time')</th>
                                            <th>@lang('attendance.out_time')</th>
                                            <th>@lang('attendance.updated_by')</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                        @foreach ($results as $key => $value)
                                            <tr class="{{ $value->finger_print_id }}">
                                                <td style="font-weight: 400">{{ 1 + $key }}</td>
                                                <td style="font-weight: 400">{{ $_REQUEST['date'] }}</td>
                                                <td style="font-weight: 400;">
                                                    {{ ucwords(trim($value->employee->first_name . ' ' . $value->employee->last_name)) }}
                                                </td>
                                                <td style="font-weight: 400;">
                                                    {{ $value->finger_print_id }}
                                                </td>
                                                <td style="width: 190px">
                                                    <div class="input-group">
                                                        <input
                                                            class="form-control datetime-local intime{{ $value->finger_print_id }}"
                                                            type="datetime-local" placeholder="@lang('attendance.in_time')"
                                                            name="in_time" value="{{ $value->in_time }}">
                                                    </div>
                                                </td>
                                                <td style="width: 190px">
                                                    <div class="input-group">
                                                        <input
                                                            class="form-control datetime-local outtime{{ $value->finger_print_id }}"
                                                            type="datetime-local" placeholder="@lang('attendance.out_time')"
                                                            name="out_time" value="{{ $value->out_time }}">
                                                    </div>
                                                </td>
                                                <td style="font-weight: 400">
                                                    @if ($value->updated_at != null)
                                                        {{ ucwords(trim($value->updatedBy->first_name . ' ' . $value->updatedBy->last_name)) }}
                                                        {{ '@ ' . date('Y-m-d h:i A', strtotime($value->updated_at)) }}
                                                    @else
                                                        {{ 'NA @' }}
                                                        {{ '0000-00-00 00:00:00' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (count($results) > 0)
                                                        <a type="submit" href="{!! route('manualAttendance.individualReport', [
                                                            'finger_print_id' => $value->finger_print_id,
                                                        ]) !!}"
                                                            data-token="{!! csrf_token() !!}"
                                                            data-id="{!! $value->finger_id !!}"
                                                            class="generateReportIndividually">
                                                            <button class="btn btn-instagram btn-sm" id="rptSave"><i
                                                                    class="fa fa-check"
                                                                    style="padding-right:4px"></i>@lang('common.save')</button></a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page_scripts')
<script>
    $('.datetime').datetimepicker({
        inline: true,
        format: 'YYYY-MM-DD HH:mm:ss',
        maxDate: new Date(),
        icons: {
            time: "fa fa-clock-o",
            date: "fa fa-calendar",
            up: "fa fa-arrow-up",
            down: "fa fa-arrow-down"
        }
    }).on('dp.change', function(e) {
        var formatedValue = e.date.format(e.date._f);
        console.log(formatedValue);
    });
</script>

<script>
    $(document).on('click', '.generateReportIndividually', function(e) {
        e.preventDefault();

        var actionTo = $(this).attr('href');

        var qs = actionTo.substring(actionTo.indexOf('?') + 1).split('&');
        for (var i = 0, result = {}; i < qs.length; i++) {
            qs[i] = qs[i].split('=');
            result[qs[i][0]] = decodeURIComponent(qs[i][1]);
        }

        var in_time = $('.intime' + result.finger_print_id).val();
        var out_time = $('.outtime' + result.finger_print_id).val();
        var token = $(this).attr('data-token');
        var id = $(this).attr('data-id');
        console.log(out_time);
        $.ajax({
            url: actionTo + '&in_time=' + in_time + '&out_time=' + out_time,
            type: 'POST',
            data: {
                _method: 'POST',
                _token: token
            },
            success: function(data) {
                console.log(data);
                if (data == 'success') {

                    // toasting success message 
                    $.toast({
                        heading: 'Success',
                        text: 'Manual attendance has been saved...!',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'success',
                        hideAfter: 2000,
                        stack: 6
                    });

                } else {
                    // toasting error message 
                    $.toast({
                        heading: 'Error',
                        text: 'Something went wrong!',
                        position: 'top-right',
                        loaderBg: '#ff6849',
                        icon: 'warning',
                        hideAfter: 3000,
                        stack: 6
                    });
                }

                setInterval(() => {
                    location.reload();
                }, 2000);

            }
        });
    });
</script>

<script>
    $('.data').on('click', '.pagination a', function(e) {
        getData($(this).attr('href').split('page=')[1]);
        e.preventDefault();
    });
</script>
@endsection
