@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.salary_details')
@endsection
<style>
    .employeeName {
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

    table.dataTable thead .sorting,
    table.dataTable thead .sorting_asc,
    table.dataTable thead .sorting_desc {
        background: none;
    }

    table.dataTable thead th.sorting::after,
    table.dataTable thead th.sorting_asc::after,
    table.dataTable thead th.sorting_desc::after {
        background: none;
    }

    /*
  tbody {
   display:block;
   height:500px;
   overflow:auto;
  }
  thead, tbody tr {
   display:table;
   width:100%;
   table-layout:fixed;
  }
  thead {
   width: calc( 100% - 1em )
  }*/
</style>

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
                                <i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong
                                    style="font-size: 12px;padding-left:12px;">{{ session()->get('success') }}</strong>
                            </div>
                        @endif
                        @if (session()->has('error'))
                            <div class="alert alert-danger alert-dismissable">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                <i class="glyphicon glyphicon-remove"></i>&nbsp;<strong
                                    style="font-size: 12px;padding-left:12px;">{{ session()->get('error') }}</strong>
                            </div>
                        @endif

                        <div id="searchBox">
                            <div class="col-md-1"></div>
                            {{ Form::open([
                                'route' => 'salaryInfo.index',
                                'id' => 'salaryDetails',
                                'class' => 'form-horizontal',
                                'method' => 'GET',
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('salary.employee_name')</label>
                                        <select class="form-control employee_id select2" name="employee_id">
                                            <option value="">---- @lang('common.all') ----</option>
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
                                        <label class="control-label" for="email">@lang('common.month')<span
                                                class="validateRq">*</span>:</label>
                                        <input type="text" class="form-control monthField" style="height: 35px;"
                                            required readonly placeholder="@lang('common.month')" id="date"
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
                        @if (isset($_GET['date']))
                            <h4 class="text-right">
                                <a class="btn btn-success btn-sm" target="_blank" style="color: #fff"
                                    href="{{ route('payslip.payslipCollection', ['month' => $date]) }}"><i
                                        class="fa fa-download fa-lg" aria-hidden="true"></i> @lang('common.download')
                                    PDF</a>
                            </h4>
                        @endif


                        <br>
                        <div class="table-responsive">
                            <table id="salary" class="table table-bordered table-striped " style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th class="text-center" colspan="1">#</th>
                                        <th class="text-center" colspan="7">Employee Info</th>
                                        <th class="text-center" colspan="4">Working Days</th>
                                        <th class="text-center" colspan="7">Allowances</th>
                                        <th class="text-center" colspan="5">Deduction</th>
                                        <th class="text-center" colspan="2">#</th>
                                        <th class="text-center" colspan="4">Pay Later / Settlement</th>
                                        <th class="text-center" colspan="2">#</th>
                                        <th class="text-center" colspan="1">#</th>
                                        <th class="text-center" colspan="1">Payslip</th>
                                    </tr>
                                    <tr>
                                        <th>Sl.No</th>
                                        <th>Month</th>
                                        <th>Unit</th>
                                        <th>Contractor</th>
                                        <th>Emp.Id</th>
                                        <th>Name</th>
                                        <th>CostCenter</th>
                                        <th>Department</th>
                                        <th>No.of.W.Days</th>
                                        <th>P.H</th>
                                        <th>C.D.H</th>
                                        <th>Tot.Days</th>
                                        <th>Basic&DA</th>
                                        <th>HRA</th>
                                        <th>Att.Bonus</th>
                                        <th>OT.Hour</th>
                                        <th>OTP/Hour</th>
                                        <th>OT.Earned</th>
                                        <th>WagesEarned</th>
                                        <th>Canteen</th>
                                        <th title="Basic&Da *{{ $payrollSetting->employee_pf }}%">EPF</th>
                                        <th title="Gross Salary *{{ $payrollSetting->employee_esic }}%">ESIC</th>
                                        <th title="Basic&Da *{{ $payrollSetting->employer_pf }}%">EPF</th>
                                        <th title="Gross Salary *{{ $payrollSetting->employer_esic }}%">ESIC</th>
                                        <th>GrossSalary</th>
                                        <th>NetSalary</th>
                                        <th title="Service Charge *{{ $payrollSetting->service_charge }}%">Ser.Charge
                                        </th>
                                        <th
                                            title="Basic&DA>7000,7000*{{ $payrollSetting->bonus }}%, Basic&DA*{{ $payrollSetting->bonus }} %">
                                            Bonus</th>
                                        {{-- <th title="Accumulated Earned Leave Days">Acc.EL.Days</th> --}}
                                        <th title="Earned Leave Days">EL.Days</th>
                                        <th title="EL.Days * Basic & Da Amount">EL.Amount</th>
                                        <th title="Total Working Hours">Manhours</th>
                                        <th title="Total Present">Mandays</th>
                                        <th title="Bonus + ServiceCharge + EPF + ESIC + GrossSalary">CTC</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
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
                url: "{{ route('salaryInfo.details') }}",
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
                    data: 'subunit',
                    name: 'subunit'
                },
                {
                    data: 'branch',
                    name: 'branch'
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
                    data: 'costcenter',
                    name: 'costcenter'
                },
                {
                    data: 'department',
                    name: 'department'
                },
                {
                    data: 'no_day_wages',
                    name: 'no_day_wages'
                },
                {
                    data: 'ph',
                    name: 'ph'
                },
                {
                    data: 'company_holiday',
                    name: 'company_holiday'
                },
                {
                    data: 'total_days',
                    name: 'total_days'
                },
                {
                    data: 'basic_da_amount',
                    name: 'basic_da_amount'
                },
                {
                    data: 'hra_amount',
                    name: 'hra_amount'
                },
                {
                    data: 'attendance_bonus',
                    name: 'attendance_bonus'
                },
                {
                    data: 'ot_hours',
                    name: 'ot_hours'
                },
                {
                    data: 'ot_per_hours',
                    name: 'ot_per_hours'
                },
                {
                    data: 'ot_amount',
                    name: 'ot_amount'
                },
                {
                    data: 'wages_amount',
                    name: 'wages_amount'
                },
                {
                    data: 'canteen',
                    name: 'canteen'
                },
                {
                    data: 'employee_pf',
                    name: 'employee_pf'
                },
                {
                    data: 'employee_esic',
                    name: 'employee_esic'
                },
                {
                    data: 'employer_pf',
                    name: 'employer_pf'
                },
                {
                    data: 'employer_esic',
                    name: 'employer_esic'
                },
                {
                    data: 'gross_salary',
                    name: 'gross_salary'
                },
                {
                    data: 'net_salary',
                    name: 'net_salary'
                },
                {
                    data: 'service_charge',
                    name: 'service_charge'
                },
                {
                    data: 'bonus_amount',
                    name: 'bonus_amount'
                },
                // {
                //     data: 'earned_leave_balance',
                //     name: 'earned_leave_balance'
                // },
                {
                    data: 'earned_leave',
                    name: 'earned_leave'
                },
                {
                    data: 'leave_amount',
                    name: 'leave_amount'
                },
                {
                    data: 'manhours',
                    name: 'manhours'
                },
                {
                    data: 'man_days',
                    name: 'man_days'
                },
                {
                    data: 'salary_oh',
                    name: 'salary_oh'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                },
            ],
            //responsive: !0,
        });
    });
</script>
@endsection('page_scripts')
