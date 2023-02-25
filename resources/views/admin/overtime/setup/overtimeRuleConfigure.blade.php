@extends('admin.master')
@section('content')
@section('title')
    @lang('overtime.overtime_rule')
@endsection

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @lang('overtime.overtime_rule') </div>
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
                            <table class="table table-bordered">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>@lang('overtime.per_min')</th>
                                        <th>@lang('overtime.amount_of_deduction')</th>
                                        <th>@lang('common.update')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($data)
                                        <tr>
                                            <td>1</td>
                                            <td>
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                                <input type="hidden" class="form-control overtime_rule_id"
                                                    value="{{ $data->overtime_rule_id }}">
                                                <input type="number" class="form-control per_min"
                                                    value="{{ $data->per_min }}" readonly
                                                    placeholder="For Min EX:(1 Min)">
                                            </td>
                                            <td>
                                                <input type="number" class="form-control amount_of_deduction"
                                                    value="{{ $data->amount_of_deduction }}"
                                                    placeholder="Salary Deduction For Late Per Min EX:(2 Min)">
                                            </td>

                                            <td>
                                                <button type="button"
                                                    class="btn btn-sm btn-success updateOvertimeRule">
                                                    @lang('common.update')
                                                </button>
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td colspan="5">No Data Found</td>
                                        </tr>
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

@section('page_scripts')
<script type="text/javascript">
    jQuery(function() {



        $("body").on("click", ".updateOvertimeRule", function() {
            var overtime_rule_id = $('.overtime_rule_id ').val();
            var per_min = $('.per_min').val();
            var amount_of_deduction = $('.amount_of_deduction').val();

            var action = "{{ URL::to('overtimeRuleConfigure/updateOvertimeRuleConfigure') }}";
            $.ajax({
                type: "post",
                url: action,
                data: {
                    'overtime_rule_id': overtime_rule_id,
                    'per_min': per_min,
                    'amount_of_deduction': amount_of_deduction,
                    '_token': $('input[name=_token]').val()
                },
                success: function(data) {
                    if (data == 'success') {
                        $.toast({
                            heading: 'success',
                            text: 'Overtime leave rule update successfully!',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'success',
                            hideAfter: 3000,
                            stack: 6
                        });
                    } else {
                        $.toast({
                            heading: 'Problem',
                            text: 'Something error found !',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'error',
                            hideAfter: 3000,
                            stack: 6
                        });
                    }

                }
            });
        })
    });
</script>
@endsection
