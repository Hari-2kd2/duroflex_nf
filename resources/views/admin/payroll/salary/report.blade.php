@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.salary_report')
@endsection

<script>
    jQuery(function() {
        $("#attendanceRecord").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-7 col-md-7 col-sm-7 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>

    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div id="searchBox">
                            <div class="col-md-1"></div>
                            {{ Form::open([
                                'route' => 'salaryReport.report',
                                'id' => 'salaryReport',
                                'class' => 'form-horizontal',
                                'method' => 'GET',
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('salary.employee_name')</label>
                                        <select class="form-control employee_id select2" name="employee_id">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($employeeList as $value)
                                                <option value="{{ $value->employee_id }}"
                                                    @if (isset($_REQUEST['employee_id'])) @if ($_REQUEST['employee_id'] == $value->employee_id) {{ 'selected' }} @endif
                                                    @endif
                                                    >{{ $value->first_name . ' ' . $value->last_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="branch_id">@lang('common.branch'):</label>
                                        <select name="branch_id" class="form-control branch_id  select2">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($branchList as $value)
                                                <option value="{{ $value->branch_id }}"
                                                    @if ($value->branch_id == $branch_id) {{ 'selected' }} @endif>
                                                    {{ $value->branch_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="department_id">@lang('common.department'):</label>
                                        <select name="department_id" class="form-control department_id  select2">
                                            <option value="">--- @lang('common.all') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.date')<span
                                                class="validateRq">*</span>:</label>
                                        <input type="text" class="form-control monthField" style="height: 35px;"
                                            required readonly placeholder="@lang('common.date')" id="date"
                                            name="date"
                                            value="@if (isset($date)) {{ $date }}@else {{ date('Y-m') }} @endif">
                                    </div>
                                </div>
                                <div class="col-sm-0"></div>
                                <div class="col-sm-1">
                                    <label class="control-label col-sm-1 text-white"
                                        for="email">@lang('common.date')</label>
                                    <input type="submit" id="filter" style="margin-top: 2px; width: 100px;"
                                        class="btn btn-info " value="@lang('common.filter')">
                                </div>
                            </div>
                            {{ Form::close() }}

                        </div>
                        @if (isset($_GET['employee_id']))
                            <div class="row" style="margin-right: 0px;">
                                <a href="{{ route('salaryReport.reportdownload', ['employee' => $_GET['employee_id'], 'branch' => $_GET['branch_id'], 'department' => $_GET['department_id'], 'date' => $_GET['date']]) }}"
                                    class="btn btn-success btn-sm pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"
                                    style="color:white;"><i class="fa fa-download" aria-hidden="true"></i>
                                    @lang('salary.download_salaryreport')</a>
                            </div><br>
                            <div class="table-responsive">
                                <table id="salary" class="table table-bordered" style="font-size: 12px">
                                    <thead class="tr_header">
                                        <tr>
                                            <th>Sl.No</th>
                                            <th>Month</th>
                                            <th>Employee Id</th>
                                            <th>Employee Name</th>
                                            <th>Contractor Name</th>
                                            <th>Unit</th>
                                            <th>Department</th>
                                            <th>Allowance</th>
                                            <th>Deduction</th>
                                            <th>Net Salary</th>
                                            {{-- <th>Action</th> --}}
                                        </tr>
                                    </thead>
                                    <tbody>
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
<script type="text/javascript">
    $(function() {
       
        $('#salary').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,

            ajax: {
                url: "{{ route('salaryReport.reportdetails') }}",
                data: function(d) {
                    d.employee = '<?php echo isset($_GET['employee_id']) ? $_GET['employee_id'] : ''; ?>';
                    d.branch = '<?php echo isset($_GET['branch_id']) ? $_GET['branch_id'] : ''; ?>';
                    d.department = '<?php echo isset($_GET['department_id']) ? $_GET['department_id'] : ''; ?>';
                    d.date = '<?php echo isset($_GET['date']) ? $_GET['date'] : ''; ?>';
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'month_year',
                    name: 'month_year'
                },
                {
                    data: 'finger_print_id',
                    name: 'finger_print_id'
                },
                {
                    data: 'employee',
                    name: 'employee'
                },
                {
                    data: 'branch',
                    name: 'branch'
                },
                {
                    data: 'subunit',
                    name: 'subunit'
                },
                {
                    data: 'costcenter',
                    name: 'costcenter'
                },
                {
                    data: 'allowance',
                    name: 'allowance'
                },
                {
                    data: 'deduction',
                    name: 'deduction'
                },
                {
                    data: 'net_salary',
                    name: 'net_salary'
                },
                // { data:'action', name: 'action', orderable: false, searchable: false},
            ],
            //responsive: !0,
        });
    });
</script>
@endsection('page_scripts')
