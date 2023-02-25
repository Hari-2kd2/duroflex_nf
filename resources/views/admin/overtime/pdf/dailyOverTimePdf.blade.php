<!DOCTYPE html>
<html lang="en">

<head>
    <title>@lang('overtime.daily_overtime')</title>
    <meta charset="utf-8">
</head>
<style>
    table {
        margin: 0 0 40px 0;
        width: 100%;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        display: table;
        border-collapse: collapse;
    }

    .printHead {
        width: 35%;
        margin: 0 auto;
    }

    table,
    td,
    th {
        border: 1px solid black;
    }

    td {
        padding: 5px;
    }

    th {
        padding: 5px;
    }

</style>

<body>
    <div class="printHead">
        @if ($printHead)
            {!! $printHead->description !!}
        @endif
        <p style="margin-left: 42px;margin-top: 10px"><b>@lang('overtime.daily_overtime')</b></p>
    </div>
    <div class="container">
        <b>@lang('common.date') : </b>{{ $date }}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>@lang('common.serial')</th>
                    <th>@lang('common.employee_name')</th>
                    <th>@lang('overtime.in_time')</th>
                    <th>@lang('overtime.out_time')</th>
                    <th>@lang('overtime.working_time')</th>
                    <th>@lang('overtime.over_time')</th>
                </tr>
            </thead>
            <tbody>
                @if (count($results) > 0)
                    @foreach ($results as $key => $data)
                        <tr>
                            <td colspan="6" class="text-center"><strong>{{ $key . '-Department' }}</strong></td>
                        </tr>
                        @foreach ($data as $key1 => $value)
                            @if ($value->workingHour < $value->working_time)
                                <tr>
                                    <td>{{ ++$key1 }}</td>
                                    <td>{{ $value->fullName }}</td>
                                    <td>{{ $value->in_time }}</td>
                                    <td>
                                        <?php
                                        if ($value->out_time != '') {
                                            echo $value->out_time;
                                        } else {
                                            echo '--';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($value->working_time != '00:00:00') {
                                            echo date('H:i', strtotime($value->working_time));
                                        } else {
                                            echo 'One Time Punch';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $workingHour = new DateTime($value->workingHour);
                                        $workingTime = new DateTime($value->working_time);
                                        if ($workingHour < $workingTime) {
                                            $interval = $workingHour->diff($workingTime);
                                            echo $interval->format('%H:%I');
                                        } else {
                                            echo '00:00';
                                        }
                                        ?>
                                    </td>
                                </tr>
                                {{-- @else
                                <tr>
                                    <td colspan="6">@lang('common.no_data_available') !</td>
                                </tr> --}}
                            @endif
                        @endforeach
                    @endforeach
                @else
                    <tr>
                        <td colspan="6"><strong>@lang('common.no_data_available') !</strong></td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</body>

</html>
