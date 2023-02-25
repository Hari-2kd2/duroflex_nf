@extends('admin.master')
@section('content')

@section('title')
    @lang('payroll.add_payroll_settings')
@endsection
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>@yield('title')</li>
            </ol>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                            {{ Form::model($editModeData, ['route' => ['payrollSettings.update', $editModeData->payset_id], 'method' => 'PUT', 'files' => 'true']) }}
                        <div class="panel-body">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible" role="alert">
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                            aria-hidden="true">×</span></button>
                                    @foreach ($errors->all() as $error)
                                        <strong>{!! $error !!}</strong><br>
                                    @endforeach
                                </div>
                            @endif

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
                                    <i
                                        class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
                                </div>
                            @endif

                            <div class="row">
                                <h3>Allowance Setting</h3>
                            </div>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.bonus')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control bonus" id="bonus"
                                            placeholder="@lang('payroll.bonus')" name="bonus" type="number" step="any"
                                            value="{{ $editModeData->bonus }}">
                                    </div>
                                </div>

                                <div class="col-md-3" hidden>
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.esic_consideration_limit')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control esic_consideration_limit" id="el_amount"
                                            placeholder="@lang('payroll.esic_consideration_limit')" name="esic_consideration_limit"
                                            type="number" value="{{ old('esic_consideration_limit') }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.el_day_limit')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control el_day_limit" id="el_day_limit"
                                            placeholder="@lang('payroll.el_day_limit')" name="el_day_limit" type="number" step="any"
                                            value="{{ $editModeData->el_day_limit }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.overtime_perhour')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control ot_per_hour" id="ot_per_hour"
                                            placeholder="@lang('payroll.updated_at')" name="ot_per_hour" type="number" step="any"
                                            value="{{ $editModeData->ot_per_hour }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.attendance_bonus')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control hra" id="attendance_bonus"
                                            placeholder="@lang('payroll.attendance_bonus')" name="attendance_bonus" type="number" step="any"
                                            value="{{ $editModeData->attendance_bonus }}">
                                    </div>

                                </div>

                            </div>

                            <div class="row">

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.service_charge')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control service_charge" id="service_charge"
                                            placeholder="@lang('payroll.service_charge')" name="service_charge" type="number" step="any"
                                            value="{{ $editModeData->service_charge }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.other_allowance')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control other_allowance" id="other_allowance"
                                            placeholder="@lang('payroll.other_allowance')" name="other_allowance" type="number" step="any"
                                            value="{{ $editModeData->other_allowance }}">
                                    </div>
                                </div>

                                <div class="col-md-3" hidden>
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.el_amount')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control el_amount" id="el_amount"
                                            placeholder="@lang('payroll.el_amount')" name="el_amount" type="number" step="any"
                                            value="{{ $editModeData->el_amount }}">
                                    </div>
                                </div>
                                
                            </div>

                            <hr>

                            <div class="row">
                                <h3>Deduction Setting</h3>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.employee_esic')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control employee_esic" id="employee_esic"
                                            placeholder="@lang('payroll.employee_esic')" name="employee_esic" type="number" step="any"
                                            value="{{ $editModeData->employee_esic }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.employee_pf')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control employee_pf" id="employee_pf"
                                            placeholder="@lang('payroll.employee_pf')" name="employee_pf" type="number" step="any"
                                            value="{{ $editModeData->employee_pf }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.employer_esic')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control employer_esic" id="employer_esic"
                                            placeholder="@lang('payroll.employee_esic')" name="employer_esic" type="number" step="any"
                                            value="{{ $editModeData->employer_esic }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.employer_pf')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control employer_pf" id="employer_pf"
                                            placeholder="@lang('payroll.employer_pf')" name="employer_pf" type="number" step="any"
                                            value="{{ $editModeData->employer_pf }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.lwf')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control lwf" id="lwf"
                                            placeholder="@lang('payroll.lwf')" name="lwf" type="number" step="any"
                                            value="{{ $editModeData->lwf }}">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.other_deduction')<span
                                                class="validateRq">*</span></label>
                                        <input class="form-control other_deduction" id="other_deduction"
                                            placeholder="@lang('payroll.other_deduction')" name="other_deduction" type="number" step="any"
                                            value="{{ $editModeData->other_deduction }}">
                                    </div>
                                </div>

                                <div class="col-md-3" hidden>
                                    <label class="control-label" for="email">@lang('payroll.year_closing')<span
                                            class="validateRq">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <input type="text" step="any" class="form-control year_closing monthField required"
                                            readonly placeholder="@lang('payroll.year_closing')" name="year_closing_field"
                                            value="@if (isset($from_date)) {{ $from_date }}@else {{ date('Y-m') }} @endif">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInput">@lang('payroll.year_closing')<span
                                                class="validateRq">*</span></label>
                                        <select class="form-control year_closing select2  required"
                                            name="year_closing" step="any">
                                            <option
                                                value="{{ 1 }}@if ($editModeData->year_closing == 1) ) selected @endif">
                                                Finantial Year</option>
                                            <option
                                                value="{{ 0 }}"@if ($editModeData->year_closing == 0) ) selected @endif>
                                                End of the year </option>
                                        </select>
                                    </div>
                                </div>

                            </div>
                            <hr>
                            <div class="row">
                                <h3>Recently Updated By</h3>
                            </div>

                            <div class="row">

                                @if (isset($editModeData))
                                    @php
                                        $name = App\Model\Employee::where('user_id', $editModeData->updated_by)->first();
                                    @endphp

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="exampleInput">@lang('payroll.updated_by')</label>
                                            <input class="form-control updated_by" id="updated_by"
                                                placeholder="@lang('payroll.updated_by')" name="updated_by" type="text"
                                                value="{{ $name->first_name . ' ' . $name->last_name }}" readonly>
                                        </div>
                                    </div>
                                @endif

                                @if (isset($editModeData))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="exampleInput">@lang('payroll.updated_at')</label>
                                            <input class="form-control updated_at" id="updated_at"
                                                placeholder="@lang('payroll.updated_at')" name="updated_at" type="text"
                                                value="{{ $editModeData->updated_at }}" readonly>
                                        </div>
                                    </div>
                                @endif

                            </div>
                        </div>

                        <br>
                        <div class="form-actions">
                            <div class="row  text-center">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i>
                                        @lang('common.save')</button>
                                </div>
                            </div>
                        </div>

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

