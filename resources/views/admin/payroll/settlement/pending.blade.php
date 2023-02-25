@extends('admin.master')
@section('content')
@section('title')
    @lang('payroll.pending_settlements')
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

                        <div id="searchBox" hidden>
                            <div class="col-md-2"></div>
                            {{ Form::open([
                                'route' => 'settlementInfo.index',
                                'id' => 'settlementDetails',
                                'class' => 'form-horizontal',
                                'method' => 'GET',
                            ]) }}
                            <div class="form-group">

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('salary.employee_name')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control employee_id select2 required" name="employee_id">
                                            <option value="">---- @lang('common.please_select') ----</option>
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
                                            <option value="">--- @lang('common.please_select') ---</option>
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
                                            <option value="">--- @lang('common.please_select') ---</option>
                                            @foreach ($departmentList as $value)
                                                <option value="{{ $value->department_id }}"
                                                    @if ($value->department_id == $department_id) {{ 'selected' }} @endif>
                                                    {{ $value->department_name }}</option>
                                            @endforeach
                                        </select>
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

                        <br>

                        <div class="table-responsive">
                            <table id="settlement" class="table table-bordered table-striped " style="font-size: 12px">
                                <thead class="tr_header">
                                    <tr>
                                        <th>Sl.No</th>
                                        <th>Employee Name</th>
                                        <th>Bonus Amount</th>
                                        <th>Earn Leave Amount</th>
                                        <th>Service Charge</th>
                                        <th>TotalAmount</th>
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

        $('#settlement').DataTable({
            processing: true,
            serverSide: true,
            ordering: false,


            ajax: {
                url: "{{ route('settlementPendingInfo.pendingdetails') }}",
                data: function(d) {
                    d.employee = '<?php echo isset($_GET['employee_id']) ? $_GET['employee_id'] : ''; ?>';
                    d.branch = '<?php echo isset($_GET['branch_id']) ? $_GET['branch_id'] : ''; ?>';
                    d.department = '<?php echo isset($_GET['department_id']) ? $_GET['department_id'] : ''; ?>';
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'employee',
                    name: 'employee'
                },
                {
                    data: 'bonus_amount',
                    name: 'bonus_amount'
                },
                {
                    data: 'leave_amount',
                    name: 'leave_amount'
                },
                {
                    data: 'service_charge',
                    name: 'service_charge'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                },
            ],
            responsive: !0,
        });
    });
</script>
@endsection('page_scripts')
