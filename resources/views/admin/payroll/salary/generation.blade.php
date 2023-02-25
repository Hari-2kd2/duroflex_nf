@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.generation')
@endsection
<style>
    .departmentName {
        position: relative;
    }

    #employee_id-error {
        position: absolute;
        top: 66px;
        left: 0;
        width: 100%he;
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
                        @if ($errors->any())
                            <div class="alert alert-danger alert-block alert-dismissable">
                                <ul>
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($errors->any())
                            <div class="alert alert-danger alert-block alert-dismissable">
                                <ul>
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if (session()->has('success'))
                            <div class="alert alert-success alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i
                                    class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div class="row">
                            <div id="searchBox">
                                {{ Form::open(['route' => 'wageSheet.sheet', 'id' => 'salarySheet', 'method' => 'GET']) }}
                                <div class="col-md-1"></div>
                                <div class="col-md-3">
                                    <div class="form-group employeeName">
                                        <label class="control-label" for="email">@lang('salary.employee_name')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control employee_id select2 required" required
                                            name="employee_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if (isset($_REQUEST['employee_id'])) @if ($_REQUEST['employee_id'] == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif
                                                    >{{ $value->first_name . ' ' . $value->last_name . '(' . $value->finger_id . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="exampleInput">From @lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'fdate',
                                            isset($fdate) ? $fdate : '',
                                            $attributes = [
                                                'class' => 'form-control required dateField fdate',
                                                'id' => 'fdate',
                                                'placeholder' => __('common.date'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="exampleInput">To @lang('common.date')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'tdate',
                                            isset($tdate) ? $tdate : '',
                                            $attributes = [
                                                'class' => 'form-control required dateField tdate',
                                                'id' => 'tdate',
                                                'placeholder' => __('common.date'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="exampleInput">@lang('common.month')<span class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        {!! Form::text(
                                            'month',
                                            isset($month) ? $month : '',
                                            $attributes = [
                                                'class' => 'form-control required monthPicker',
                                                'id' => 'month',
                                                'placeholder' => __('common.month'),
                                                'autocomplete' => 'off',
                                            ],
                                        ) !!}
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <input type="submit" id="filter" style="margin-top: 25px; width: 100px;"
                                            class="btn btn-info " value="Generate">
                                    </div>
                                </div>
                                {{ Form::close() }}
                            </div>
                        </div>
                        <hr>
                        <div class="table-responsive"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script type="text/javascript">
    $(document).ready(function() {
        // bootstrap month picker
        $(".monthPicker").datepicker({

            // properties of datepicker 
            format: "yyyy-mm",
            minViewMode: "months",
            dateFormat: 'yyyy-mm',
            todayHighlight: true,

            // startDate: new Date(),
            // endDate: new Date(),

        }).on('changeDate', function(e) {

            // disable submit button
            $(':input[type="submit"]').prop('disabled', false);

            // split date string of input field
            var startDate = $('#fdate').val().split("/");
            var endDate = $('#tdate').val().split("/");

            // date object conversion from string
            var startDay = new Date(startDate[2], startDate[1] - 1, startDate[0]);
            var endDay = new Date(endDate[2], endDate[1] - 1, endDate[0]);

            // find month of selected date
            var month = e.format('yyyy/mm/dd');
            var month = month.split("/");

            // find no of days between date
            var days = daysdifference(startDay, endDay);

            $(this).datepicker('hide');

            // error message to show in toaster
            var e1 = 'Month is not valid. choose between seected dates. !';
            var e2 = days +
                ' Days are selected, selected days should not be more than 31 days. !';

            if ((month[1] - 1) != (startDate[1] - 1) && (month[1] - 1) != (endDate[1] -
                    1) || days >
                31) {
                // $('.monthPicker').datepicker('setDate', null);
                $(':input[type="submit"]').prop('disabled', true);

                // toasting error message 
                $.toast({
                    heading: 'Warning',
                    text: days <= 31 ? e1 : e2,
                    position: 'top-right',
                    loaderBg: '#ff6849',
                    icon: 'success',
                    hideAfter: 3000,
                    stack: 1
                });
            }
        });

        function daysdifference(startDay, endDay) {

            // Determine the time difference between two dates     
            var millisBetween = startDay.getTime() - endDay.getTime();

            // Determine the number of days between two dates  
            var days = millisBetween / (1000 * 3600 * 24);

            // Show the final number of days between dates     
            return Math.round(Math.abs(days));
        }
    });
    // $(".monthPicker").datepicker({
    //     format: "yyyy-mm",
    //     minViewMode: "months",
    //     dateFormat: 'yyyy-mm',
    //     todayHighlight: true,
    //     // startDate: new Date(),
    //     // endDate: new Date(),
    // }).on('changeDate', function(e) {
    //     $(this).datepicker('hide');
    // });
</script>
@endsection('page_scripts')
