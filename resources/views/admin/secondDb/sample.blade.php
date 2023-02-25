@extends('admin.master')
@section('content')
@section('title')
    Sample Datas
@endsection
<script>
    jQuery(function() {
        $("#dailyAttendanceReport").validate();
    });
</script>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <ol class="breadcrumb">
                <li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i>
                        @lang('dashboard.dashboard')</a></li>
                <li>Sample Datas</li>
            </ol>
        </div>
    </div>
    {{-- @php
		dd($departmentList);
		@endphp --}}
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-info">
                <div class="panel-heading"><i class="mdi mdi-table fa-fw"></i>Sample Datas</div>
                <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-md-0" style=""></div>
                            <div class="col-md-6 bg-white" style="padding: 2px;margin-left: 72px">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control dateField required" readonly
                                        placeholder="Select Month" name="month"
                                        value="@if (isset($date)) {{ $date }}@else {{ 'Month' }} @endif">
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="" class="table table-bordered">
                                <thead class="tr_header">
                                    <tr>
                                        @foreach ($columns as $column)
                                            <th style="width:100px;">{{ $column }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($results as $key => $data)
                                        <tr>
                                            <td>{{ $data->EVTLGUID }}</td>
                                            <td>{{ $data->SRVDT }}</td>
                                            <td>{{ $data->DEVDT }}</td>
                                            <td>{{ $data->DEVUID }}</td>
                                            <td>{{ $data->PKTDEVID }}</td>
                                            <td>{{ $data->DEVLGIDX }}</td>
                                            <td>{{ $data->IMGLGUID }}</td>
                                            <td>{{ $data->USRID }}</td>
                                            <td>{{ $data->USRGRUID }}</td>
                                            <td>{{ $data->EVT }}</td>
                                            <td>{{ $data->CRDSL }}</td>
                                            <td>{{ $data->TNAKEY }}</td>
                                            <td>{{ $data->DRUID }}</td>
                                            <td>{{ $data->ZNUID }}</td>
                                            <td>{{ $data->ELVTUID }}</td>
                                            <td>{{ $data->DRUID1 }}</td>
                                            <td>{{ $data->DRUID2 }}</td>
                                            <td>{{ $data->DRUID3 }}</td>
                                            <td>{{ $data->IS_DST }}</td>
                                            <td>{{ $data->TMZN_HALF }}</td>
                                            <td>{{ $data->TMZN_HOUR }}</td>
                                            <td>{{ $data->TMZN_NEGTV }}</td>
                                            <td>{{ $data->USRUDTBYDEV }}</td>
                                            <td>{{ $data->HINT }}</td>
                                            <td>{{ $data->TEMPER }}</td>
                                        </tr>
                                    @empty
                                        <tr>No data Found</tr>
                                    @endforelse
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
<script>
    $(function() {

        $('.data').on('click', '.pagination a', function(e) {
            getData($(this).attr('href').split('page=')[1]);
            e.preventDefault();
        });

        $(".dateField").change(function() {
            getData(1);
        });


    });

    function getData(page) {
        var dateField = $('.dateField').val();
        $.ajax({
            url: '?page=' + page + "&dateField=" + dateField,
            datatype: "html",
        }).done(function(data) {
            $('.data').html(data);
            $("html, body").animate({
                scrollTop: 0
            }, 150);
        }).fail(function() {
            $.toast({
                heading: 'Warning',
                text: 'Something Error Found !, data could not be loaded. !',
                position: 'top-right',
                loaderBg: '#ff6849',
                icon: 'success',
                hideAfter: 3000,
                stack: 6
            });
        });

    }
</script>
@endsection
