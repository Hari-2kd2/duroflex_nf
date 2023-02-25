@extends('admin.master')
@section('content')
@section('title')
    @lang('payroll.settlement_form') (
    {{ $data->finger_id }} )
@endsection

<style type="text/css">
    td {
        padding: 4px !important
    }

    .r-align {
        text-align: right;
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

                        {{ Form::open(['route' => 'settlementInfo.store', 'enctype' => 'multipart/form-data', 'id' => 'salaryForm', 'class' => 'form-horizontal']) }}
                        <input type="hidden" name="employee" value="{{ $_GET['employee_id'] }}">
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
                            <div class="col-md-6">
                                <h3>Employee Information</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>
                                        <tr>
                                            <td><b>Name of the Employee</b></td>
                                            <td>{{ $data->first_name . ' ' . $data->last_name }}
                                            </td>
                                            <td><b>Contactor</b></td>
                                            <td>{{ $data->branch->branch_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Sub Unit</b></td>
                                            <td>{{ $data->subdepartment->sub_department_name }}</td>
                                            <td><b>Cost Center</b></td>
                                            <td>{{ $data->costcenter->cost_center_number }}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Department</b></td>
                                            <td>{{ $data->department->department_name }}</td>
                                            <td><b>Designation</b></td>
                                            <td>{{ $data->designation->designation_name }}</td>
                                        </tr>

                                        <tr>
                                            <td><b>Date of Joining</b></td>
                                            <td>{{ date('d-m-Y', strtotime($data->date_of_joining)) }}</td>
                                        </tr>

                                    </tbody>
                                </table>
                                <h3>Monthly Information</h3>
                                <hr style="margin-top:-12px;margin-bottom:10px">
                                <table class="table table-bordered table-hover table-striped">
                                    <tbody>
                                        <tr class="tr_header" style="text-align: center;font-weight:500">
                                            <td>Month</td>
                                            <td>Year</td>
                                            <td>Bonus Amount</td>
                                            <td>Earn Leave Amount</td>
                                            <td>Service Charge</td>
                                            <td>Total</td>
                                        </tr>
                                        @php $service=$bonus=$el=$total=0; @endphp
                                        @foreach ($payroll as $Data)
                                            <tr>
                                                <td class="text-center">
                                                    {{ DATE('M', strtotime($Data->year . '-' . $Data->month . '-' . '01')) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ DATE('Y', strtotime($Data->year . '-' . $Data->month . '-' . '01')) }}
                                                </td>
                                                <td class="r-align">{{ $Data->bonus_amount }}</td>
                                                <td class="r-align">{{ $Data->leave_amount }}</td>
                                                <td class="r-align">{{ $Data->service_charge }}</td>
                                                <td class="r-align">
                                                    {{ $Data->leave_amount + $Data->bonus_amount + $Data->service_charge }}
                                                </td>
                                            </tr>
                                            @php
                                                $bonus += $Data->bonus_amount;
                                                $el += $Data->leave_amount;
                                                $service += $Data->service_charge;
                                                $total += $Data->bonus_amount + $Data->leave_amount + $Data->service_charge;
                                            @endphp
                                        @endforeach
                                        <tr style="text-align: center;font-size: 14px;font-weight: bold;">
                                            <td></td>
                                            <td>Total</td>
                                            <td class="r-align">{{ $bonus }}</td>
                                            <td class="r-align">{{ $el }}</td>
                                            <td class="r-align">{{ $service }}</td>
                                            <td class="r-align">{{ $total }}</td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            @if ($total > 0)
                                <div class="col-md-6" style="padding-right:28px;">
                                    <h3 class="text-right">Settlement Information</h3>
                                    <hr style="margin-top:-12px;margin-bottom:10px;">
                                    <div class="row">
                                        <label class="control-label col-md-6" for="email"> Settlement Amount:<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="amount"
                                                    value="{{ $total }}" readonly id="amount">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-md-6" for="email">Deduction Amount:<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="number" class="form-control" name="deduction_amount"
                                                    value="{{ old('deduction_amount') }}" id="deduction_amount">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-md-6" for="email">Payable Amount:<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="net_amount"
                                                    value="{{ old('net_amount') }}" readonly id="net_amount">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-md-6" for="email">Date:<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="paid_at"
                                                    value="{{ old('paid_at') }}" autocomplete="off" id="paid_at">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <label class="control-label col-md-6" for="email">Remarks:<span
                                                class="validateRq">*</span></label>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <textarea type="text" class="form-control" name="remarks" value="{{ old('remarks') }}" id="remarks"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <label class="control-label col-md-6" for="email"></label>
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-info btn_style"><i
                                                    class="fa fa-check"></i> Submit</button>
                                        </div>
                                    </div>


                                </div>
                            @endif
                        </div>
                        <br>


                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page_scripts')
<script>
    jQuery(function() {

        $('#paid_at').datetimepicker({
            format: 'DD-MM-YYYY'
        });

        $(document).ready(function() {

            $('#deduction_amount').keyup(function() {

                var amount = parseFloat($('#amount').val());
                var deduction_amount = parseFloat($('#deduction_amount').val());
                var net_amount = amount - deduction_amount;

                if (net_amount > 0) {
                    $('#net_amount').val(Math.round(net_amount, 2));
                } else {
                    $('#net_amount').val(0);
                }


            });



        });

    });
</script>
@endsection
