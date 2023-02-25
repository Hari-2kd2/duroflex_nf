@extends('admin.master')
@section('content')
@section('title')
    @lang('payroll_setup.workingday_list')
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
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <a href="{{ route('monthlyWorkingDay.create') }}"
                class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"> <i
                    class="fa fa-plus-circle" aria-hidden="true"></i> @lang('payroll_setup.add_workingday')</a>
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
                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('payroll_setup.year')</th>
                                        <th>@lang('payroll_setup.jan')</th>
                                        <th>@lang('payroll_setup.feb')</th>
                                        <th>@lang('payroll_setup.mar')</th>
                                        <th>@lang('payroll_setup.apr')</th>
                                        <th>@lang('payroll_setup.may')</th>
                                        <th>@lang('payroll_setup.jun')</th>
                                        <th>@lang('payroll_setup.jul')</th>
                                        <th>@lang('payroll_setup.aug')</th>
                                        <th>@lang('payroll_setup.sep')</th>
                                        <th>@lang('payroll_setup.oct')</th>
                                        <th>@lang('payroll_setup.nov')</th>
                                        <th>@lang('payroll_setup.dec')</th>
                                        <th>@lang('payroll_setup.updated_by')</th>
                                        <th>@lang('common.action')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (count($results) > 0)
                                        {!! $sl = null !!}
                                        @foreach ($results as $value)
                                            <tr class="{!! $value->working_id !!}">
                                                <td style="width: 100px;">{!! ++$sl !!}</td>
                                                <td>{!! $value->year !!}</td>
                                                <td>{!! $value->jan !!}</td>
                                                <td>{!! $value->feb !!}</td>
                                                <td>{!! $value->mar !!}</td>
                                                <td>{!! $value->apr !!}</td>
                                                <td>{!! $value->may !!}</td>
                                                <td>{!! $value->jun !!}</td>
                                                <td>{!! $value->july !!}</td>
                                                <td>{!! $value->aug !!}</td>
                                                <td>{!! $value->sep !!}</td>
                                                <td>{!! $value->oct !!}</td>
                                                <td>{!! $value->nov !!}</td>
                                                <td>{!! $value->dec !!}</td>
                                                @php
                                                    $name = App\Model\Employee::where('user_id', $value->updated_by)->first();
                                                @endphp
                                                <td>{!! $name->first_name . ' ' . $name->last_name !!} <br>{!! $value->updated_at !!}</td>
                                                <td style="width: 100px;">
                                                    <a href="{!! route('monthlyWorkingDay.edit', $value->working_id) !!}"
                                                        class="btn btn-success btn-xs btnColor">
                                                        <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                                                    </a>
                                                    <a href="{!! route('monthlyWorkingDay.delete', $value->working_id) !!}"
                                                        data-token="{!! csrf_token() !!}"
                                                        data-id="{!! $value->working_id !!}"
                                                        class="delete btn btn-danger btn-xs deleteBtn btnColor"><i
                                                            class="fa fa-trash-o" aria-hidden="true"></i></a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
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
