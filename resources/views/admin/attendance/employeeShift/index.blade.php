@extends('admin.master')
@section('content')
@section('title')
    @lang('attendance.shift_details')
@endsection


<style>
    .custom-file-upload {
        color: grey !important;
        display: inline-block;
        padding: 4px 4px 4px 4px;
        cursor: pointer;
        font-weight: normal;
        /* border: 2px solid #3f729b; */
        border-radius: 6px;
        width: 600px;
        height: 32px;

    }

    input::file-selector-button {
        display: inline-block;
        font-weight: bolder;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        background: #41b3f9;
        /* background: #3f729b; */
        /* background: #7ace4c; */
        border-width: 1px;
        border: none;
        font-size: 12px;
        overflow: hidden;
        -webkit-box-sizing: border-box;
        -moz-box-sizing: border-box;
        box-sizing: border-box;
        background-size: 12px 12px;
        padding: 4px 4px 4px 4px;
    }
</style>

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
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i> @yield('title')</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="text-left">
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
                            @if ($message = Session::get('success'))
                                <div class="alert alert-success alert-block">
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    <strong>{{ $message }}</strong>
                                </div>
                            @endif
                            @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-block">
                                    <button type="button" class="close" data-dismiss="alert">x</button>
                                    <strong>{{ $message }}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="row"
                            style="border: 1px solid #b9b8b5;border-radius:4px;margin:2px;padding:20px 0 0 0">
                            <p class="border" style="margin-left:30px">
                                <span><i class="fa fa-upload"></i></span>
                                <span style="margin-left: 4px"><b>Upload Document Here (.xlsx).</b></span>
                            </p>
                            <form class="col-md-8" action="{{ route('shiftDetails.import') }}" method="post"
                                enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="col-md-5 text-right">
                                    <input type="file" name="select_file" class="form-control custom-file-upload">
                                </div>
                                <div class="form-group col-md-3">
                                    <input class="form-control monthField" style="height: 35px;border-radius: 6px;"
                                        required readonly placeholder="@lang('common.month')" id="month" name="month"
                                        value="@if (isset($month)) {{ $month }}@else {{ date('Y-m') }} @endif">
                                </div>
                                <div class="col-md-1 pull-left" style="margin-top: 1px;">
                                    <button class="btn btn-success btn-sm" type="submit"><span><i class="fa fa-upload"
                                                aria-hidden="true"></i></span>
                                        Upload</button>
                                </div>
                            </form>
                            <form class="row" style="margin-right: 12px;padding:0 0px 0 12px;"
                                action="{{ route('shiftDetails.export') }}" method="post"
                                enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <button class="btn btn-instagram btn-sm pull-right" type="submit"
                                    style="margin-top: 2px;margin-right:12px;">
                                    <i class="fa fa-download" aria-hidden="true"></i><span>
                                        Sample Format</span>
                                </button>
                                <div class="form-group pull-right">
                                    <input class="form-control monthField"
                                        style="height: 35px;border-radius: 6px;width:100px;margin-right:36px;" required
                                        readonly placeholder="@lang('common.month')" id="month" name="month"
                                        value="@if (isset($month)) {{ $month }}@else {{ date('Y-m') }} @endif">
                                </div>
                            </form>
                        </div>
                        <hr>
                        <div class="row">
                            <form class="col-md-10" action="{{ route('shiftDetails.index') }}" method="post"
                                enctype="multipart/form-data">
                                {{ csrf_field() }}
                                <div class="col-md-3"></div>
                                <label class="col-md-1 control-label" style="padding-top: 6px"
                                    for="email">@lang('common.month')<span class="validateRq">*</span>:</label>
                                <div class="form-group col-sm-4">
                                    <input class="form-control monthField" style="height: 35px;" required readonly
                                        placeholder="@lang('common.month')" id="yearAndMonth" name="yearAndMonth"
                                        value="@if (isset($yearAndMonth)) {{ $yearAndMonth }}@else {{ date('Y-m') }} @endif">
                                </div>
                                <button class="btn btn-info btn-md col-md-2" value="Filter" type="submit"
                                    style="margin-top: 2px;margin-right: 10px;width:84px;">
                                    <i class="fa fa-download" aria-hidden="true"></i><span>
                                        {{ 'Filter' }}</span>
                                </button>
                            </form>
                            @if (isset($yearAndMonth))
                                <h4 class="text-right">
                                    <a class="btn btn-success btn-sm pull-right" style="color: #fff;margin-right: 16px"
                                        href="{{ URL('shiftDetails/download/?yearAndMonth=' . $yearAndMonth) }}"><i
                                            class="fa fa-download fa-lg" aria-hidden="true"></i> @lang('common.download')</a>
                                </h4>
                            @endif
                        </div>
                        <div class="table-responsive">
                            <table id="myTable" class="table table-bordered table-striped">
                                <thead>
                                    <tr class="tr_header">
                                        <th>@lang('common.serial')</th>
                                        <th>Emp.Id</th>
                                        <th>@lang('common.month')</th>
                                        <th>01</th>
                                        <th>02</th>
                                        <th>03</th>
                                        <th>04</th>
                                        <th>05</th>
                                        <th>06</th>
                                        <th>07</th>
                                        <th>08</th>
                                        <th>09</th>
                                        <th>10</th>
                                        <th>11</th>
                                        <th>12</th>
                                        <th>13</th>
                                        <th>14</th>
                                        <th>15</th>
                                        <th>16</th>
                                        <th>17</th>
                                        <th>18</th>
                                        <th>19</th>
                                        <th>20</th>
                                        <th>21</th>
                                        <th>22</th>
                                        <th>23</th>
                                        <th>24</th>
                                        <th>25</th>
                                        <th>26</th>
                                        <th>27</th>
                                        <th>28</th>
                                        <th>28</th>
                                        <th>30</th>
                                        <th>31</th>
                                        <th>Updated_By</th>
                                        {{-- <th style="text-align: center;">@lang('common.action')</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    {!! $sl = null !!}
                                    @foreach ($results as $value)
                                        <tr class="{!! $value->id !!}">
                                            <td style="width: 50px;">{!! ++$sl !!}</td>
                                            <td>{{ $value->finger_print_id }}</td>
                                            <td>{{ $value->month }}</td>
                                            <td>{{ $value->d_1 ? $shift[$value->d_1] : 'NA' }}</td>
                                            <td>{{ $value->d_2 ? $shift[$value->d_2] : 'NA' }}</td>
                                            <td>{{ $value->d_3 ? $shift[$value->d_3] : 'NA' }}</td>
                                            <td>{{ $value->d_4 ? $shift[$value->d_4] : 'NA' }}</td>
                                            <td>{{ $value->d_5 ? $shift[$value->d_5] : 'NA' }}</td>
                                            <td>{{ $value->d_6 ? $shift[$value->d_6] : 'NA' }}</td>
                                            <td>{{ $value->d_7 ? $shift[$value->d_7] : 'NA' }}</td>
                                            <td>{{ $value->d_8 ? $shift[$value->d_8] : 'NA' }}</td>
                                            <td>{{ $value->d_9 ? $shift[$value->d_9] : 'NA' }}</td>
                                            <td>{{ $value->d_10 ? $shift[$value->d_10] : 'NA' }}</td>
                                            <td>{{ $value->d_11 ? $shift[$value->d_11] : 'NA' }}</td>
                                            <td>{{ $value->d_12 ? $shift[$value->d_12] : 'NA' }}</td>
                                            <td>{{ $value->d_13 ? $shift[$value->d_13] : 'NA' }}</td>
                                            <td>{{ $value->d_14 ? $shift[$value->d_14] : 'NA' }}</td>
                                            <td>{{ $value->d_15 ? $shift[$value->d_15] : 'NA' }}</td>
                                            <td>{{ $value->d_16 ? $shift[$value->d_16] : 'NA' }}</td>
                                            <td>{{ $value->d_17 ? $shift[$value->d_17] : 'NA' }}</td>
                                            <td>{{ $value->d_18 ? $shift[$value->d_18] : 'NA' }}</td>
                                            <td>{{ $value->d_19 ? $shift[$value->d_19] : 'NA' }}</td>
                                            <td>{{ $value->d_20 ? $shift[$value->d_20] : 'NA' }}</td>
                                            <td>{{ $value->d_21 ? $shift[$value->d_21] : 'NA' }}</td>
                                            <td>{{ $value->d_22 ? $shift[$value->d_22] : 'NA' }}</td>
                                            <td>{{ $value->d_23 ? $shift[$value->d_23] : 'NA' }}</td>
                                            <td>{{ $value->d_24 ? $shift[$value->d_24] : 'NA' }}</td>
                                            <td>{{ $value->d_25 ? $shift[$value->d_25] : 'NA' }}</td>
                                            <td>{{ $value->d_26 ? $shift[$value->d_26] : 'NA' }}</td>
                                            <td>{{ $value->d_27 ? $shift[$value->d_27] : 'NA' }}</td>
                                            <td>{{ $value->d_28 ? $shift[$value->d_28] : 'NA' }}</td>
                                            <td>{{ $value->d_29 ? $shift[$value->d_29] : 'NA' }}</td>
                                            <td>{{ $value->d_30 ? $shift[$value->d_30] : 'NA' }}</td>
                                            <td>{{ $value->d_31 ? $shift[$value->d_31] : 'NA' }}</td>
                                            <td>{{ $value->updated_user->first_name . ' ' . $value->updated_user->last_name }}
                                                @<br>{{ date('d/m/y h:i A', strtotime($value->updated_at)) }}
                                            </td>

                                        </tr>
                                    @endforeach
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
<script type="text/javascript"></script>
@endsection
