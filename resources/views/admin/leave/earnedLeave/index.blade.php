@extends('admin.master')
@section('content')
@section('title')
    @lang('salary.earned_leave_list')
@endsection

<script>
    jQuery(function() {
        $("#earnedLeave").validate();
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
                            <div class="col-md-3  col-sm-2"></div>
                            {{ Form::open([
                                'route' => 'earnedLeave.index',
                                'id' => 'earnedLeaveReport',
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
                                                    >{{ $value->first_name . ' ' . $value->last_name . '(' . $value->finger_id . ')' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-sm-2" style="margin-left:24px;">
                                    <div class="form-group">
                                        <label class="control-label" for="email">@lang('common.month')<span
                                                class="validateRq">*</span>:</label>
                                        <input type="text" class="form-control monthField" style="height: 35px;"
                                            required readonly placeholder="@lang('common.month')" id="month"
                                            name="month"
                                            value="@if (isset($month)) {{ $month }}@else {{ date('Y-m') }} @endif">
                                    </div>
                                </div>
                                <div class="col-sm-0"></div>
                                <div class="col-sm-1">
                                    <label class="control-label col-sm-1 text-white"
                                        for="email">@lang('common.month')</label>
                                    <input type="submit" id="filter" style="margin-top: 2px; width: 100px;"
                                        class="btn btn-info " value="@lang('common.filter')">
                                </div>
                            </div>
                            {{ Form::close() }}

                        </div>
                        @if (isset($employee_id) || isset($month))
                            <div class="row" style="margin-right: 0px;">
                                <a href="{{ route('earnedLeave.download', ['employee_id' => $employee_id, 'month' => $month]) }}"
                                    class="btn btn-success btn-sm pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"
                                    style="color:white;"><i class="fa fa-download" aria-hidden="true"></i>
                                    @lang('common.download')</a>
                            </div><br>
                            <div class="table-responsive">
                                @if (isset($month))
                                    <table id="earnedLeave" class="table table-bordered" style="font-size: 12px">
                                        <thead class="tr_header">
                                            <tr>
                                                <th>Sl.No</th>
                                                <th>Month</th>
                                                <th>Employee Id</th>
                                                <th>Name of the Employee</th>
                                                <th>Accumulated EL</th>
                                                <th>Total EL</th>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
                                    </table>
                                @endif
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
    $(document).ready(function() {
        $('#tblAttendance').DataTable({
            order: [
                [2, 'asc'],
                [1, 'asc']
            ],
            rowGroup: {
                dataSrc: [2, 1],
                startRender: function(rows, group) {
                    return $('<tr/>')
                        .append('<td class="td-left" >' + group + '</td>');

                }
            },
            "columnDefs": [{
                    "targets": [1],
                    "visible": false
                },
                {
                    "targets": [2],
                    "visible": false
                },
                {
                    "className": "dt-center",
                    "targets": "_all"
                },
                {
                    "width": "10%",
                    "targets": 0
                },
                {
                    "width": "10%",
                    "targets": 14
                },
            ],

            scrollY: "600px",
            scrollX: true,
            paging: false,
            dom: 'Bfrtip',
            buttons: [
                'copy',
                'csv',
                'excel',
                {
                    extend: 'pdfHtml5',
                    orientation: 'landscape',
                    pageSize: 'LEGAL'
                }

            ]

        });
    });


    $(function() {
        $('#earnedLeave').DataTable({
            paging: true,
            processing: true,
            serverSide: true,
            ordering: false,
            searching: true,
            // scrollY: 300,
            ajax: {
                url: "{{ route('earnedLeave.report') }}",
                data: function(d) {
                    d.employee_id = '<?php echo isset($_GET['employee_id']) ? $_GET['employee_id'] : ''; ?>';
                    d.month = '<?php echo isset($_GET['month']) ? $_GET['month'] : ''; ?>';
                },
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'month',
                    name: 'month'
                },
                {
                    data: 'finger_print_id',
                    name: 'finger_print_id'
                },
                {
                    data: 'employee_name',
                    name: 'employee_name'
                },
                {
                    data: 'el_balance',
                    name: 'el_balance'
                },
                {
                    data: 'el',
                    name: 'el'
                },

                // { data:'action', name: 'action', orderable: false, searchable: false},
            ],
            //responsive: !0,
        });
    });
</script>
@endsection('page_scripts')
