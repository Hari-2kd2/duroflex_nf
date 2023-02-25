@extends('admin.master')
@section('content')
@section('title')
    @lang('payroll_setup.add_workingday')
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
        <div class="col-lg-9 col-md-8 col-sm-8 col-xs-12">
            <a href="{{ route('monthlyWorkingDay.index') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i
                    class="fa fa-list-ul" aria-hidden="true"></i> @lang('payroll_setup.view_workingday') </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        {{ Form::open(['route' => 'monthlyWorkingDay.store', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal', 'id' => 'monthlyWorkingDayForm']) }}
                        <div class="form-body">
                            <div class="row">
                                <div class="col-md-offset-2 col-md-6">
                                    @if ($errors->any())
                                        <div class="alert alert-danger alert-dismissible" role="alert">
                                            <button type="button" class="close" data-dismiss="alert"
                                                aria-label="Close"><span aria-hidden="true">×</span></button>
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
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.year'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="text" class="form-control year yearPicker required" readonly
                                            placeholder="@lang('payroll.year')" name="year"
                                            value="@if (isset($year)) {{ $year }}@else {{ date('Y') }} @endif">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.jan'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control jan required" placeholder="@lang('payroll.no_of_days')"
                                            name="jan" value="{{ old('jan') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.feb'):<span class="validateRq">*</span></label>
                                <div class=" col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control feb required" placeholder="@lang('payroll.no_of_days')"
                                            name="feb" value="{{ old('feb') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.mar'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control mar required" placeholder="@lang('payroll.no_of_days')"
                                            name="mar" value="{{ old('mar') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.apr'):<span class="validateRq">*</span></label>
                                <div class=" col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control apr required" placeholder="@lang('payroll.no_of_days')"
                                            name="apr" value="{{ old('apr') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.may'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control may required" placeholder="@lang('payroll.no_of_days')"
                                            name="may" value="{{ old('may') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.jun'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control jun required" placeholder="@lang('payroll.no_of_days')"
                                            name="jun" value="{{ old('jun') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.jul'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control july required" placeholder="@lang('payroll.no_of_days')"
                                            name="july" value="{{ old('july') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.aug'):<span class="validateRq">*</span></label>
                                <div class=" col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control aug required" placeholder="@lang('payroll.no_of_days')"
                                            name="aug" value="{{ old('aug') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.sep'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control sep required" placeholder="@lang('payroll.no_of_days')"
                                            name="sep" value="{{ old('sep') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.oct'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control oct required" placeholder="@lang('payroll.no_of_days')"
                                            name="oct" value="{{ old('oct') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2"
                                    for="email">@lang('payroll_setup.nov'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control nov required" placeholder="@lang('payroll.no_of_days')"
                                            name="nov" value="{{ old('nov') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <label class="control-label col-md-offset-2 col-md-2 "
                                    for="email">@lang('payroll_setup.dec'):<span class="validateRq">*</span></label>
                                <div class="col-md-4">
                                    <div class="form-group">

                                        <input type="number" max="31" min="0"
                                            class="form-control dec required" placeholder="@lang('payroll.no_of_days')"
                                            name="dec" value="{{ old('dec') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-full text-center">
                                    <button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i>
                                        @lang('common.save')</button>
                                </div>
                            </div>
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

@section('page_scripts')
<script>
    jQuery(function() {

        $(document).ready(function() {




        });

    });
</script>
@endsection
