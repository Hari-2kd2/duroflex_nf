<!DOCTYPE html>
<html lang="en">

<head>
    <title>@lang('overtime.my_overtime_report')</title>
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
        <br>
        <p style="margin-left: 42px;margin-top: 10px"><b>@lang('overtime.my_overtime_report')</b></p>
    </div>
    <div class="container">
        <b>@lang('common.name') : </b>{{ $employee_name }},<b>@lang('employee.department') :
        </b>{{ $department_name }}<b>,@lang('common.from_date') : </b>{{ $form_date }} , <b>@lang('common.to_date')
            :
        </b>{{ $to_date }}
        <table id="" class="table table-bordered">
            <thead class="tr_header">
                <tr>
                    <th style="width:100px;">@lang('common.serial')</th>
                    <th>@lang('common.date')</th>
                    <th>@lang('common.employee_name')</th>
                    <th>@lang('attendance.in_time')</th>
                    <th>@lang('attendance.out_time')</th>
                    <th>@lang('attendance.working_time')</th>
                    <th>@lang('attendance.over_time')</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalPresent = 0;
                $totalHour = 0;
                $totalMinit = 0;
                $totalAbsence = 0;
                ?>

                {{ $serial = null }}
                @if (count($results) > 0)
                    @foreach ($results as $value)
                        @if ($value['workingHour'] < $value['working_time'])
                            <tr>
                                <td style="width:100px;">{{ ++$serial }}</td>
                                <td>{{ $value['date'] }}</td>
                                <td>{{ $value['fullName'] }}</td>
                                <td>
                                    <?php
                                    if ($value['in_time'] != '') {
                                        echo $value['in_time'];
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($value['out_time'] != '') {
                                        echo $value['out_time'];
                                    } else {
                                        echo '--';
                                    }
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    if ($value['working_time'] == '') {
                                        echo '--';
                                    } else {
                                        if ($value['working_time'] != '00:00:00') {
                                            echo $d = date('H:i', strtotime($value['working_time']));
                                    
                                            $hour_minit = explode(':', $d);
                                    
                                            $totalHour += $hour_minit[0];
                                            $totalMinit += $hour_minit[1];
                                        } else {
                                            echo 'One Time Punch';
                                        }
                                    }
                                    
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    if ($value['workingHour'] == '') {
                                        echo '--';
                                    } else {
                                        $workingHour = new DateTime($value['workingHour']);
                                        $workingTime = new DateTime($value['working_time']);
                                        if ($workingHour < $workingTime) {
                                            $interval = $workingHour->diff($workingTime);
                                            $overtime = $interval->format('%H:%I');
                                            echo $overtime;
                                        } else {
                                            echo '--';
                                        }
                                    }
                                    ?>
                                </td>

                            </tr>
                        @endif
                    @endforeach
                    {{-- @else
                    <tr>
                        <td colspan="7">@lang('common.no_data_available') !</td>
                    </tr> --}}
                @endif
                <?php
                $total_working_hour = ($totalHour * 60 + $totalMinit) / 60;
                $total_work_minit = $totalHour * 60 + $totalMinit;
                $totaltime = $total_work_minit / 60;
                $totalHour = floor($total_work_minit / 60);
                $totalMinit = ($totaltime - $totalHour) * 60;
                $total_workHour = sprintf('%02d', $totalHour) . ':' . sprintf('%02d', $totalMinit);
                ?>
                @if (count($results) > 0 && $total_working_hour > $value['working_time'])
                    <?php
                    $totalPresent += 1;
                    ?>
                    <tr>
                        <td colspan="5"></td>
                        <td style="background: #eee"><b>@lang('overtime.expected_working_hour'):
                                &nbsp;</b></td>
                        <td style="background: #eee">
                            @php
                                $expected_hour = $totalPresent * 8.5 * $serial;
                                $actual_time = $totalPresent * 8.5 * $serial * 60;
                                $total_actual_time = $actual_time / 60;
                                $total_actual_Hour = floor($actual_time / 60);
                                $total_actual_Minit = ($total_actual_time - $total_actual_Hour) * 60;
                                $total_actual_workHour = sprintf('%02d', $total_actual_Hour) . ':' . sprintf('%02d', $total_actual_Minit);
                            @endphp
                            <b>{{ $total_actual_workHour }}</b>
                            @lang('common.hours')
                        </td>
                    </tr>
                    <tr>
                        <td colspan="5"></td>
                        <td style="background: #eee"><b>@lang('overtime.actual_working_hour'):
                                &nbsp;</b></td>
                        <td style="background: #eee"><b>{{ $total_workHour }}</b>
                            @lang('common.hours')</td>
                    </tr>
                    <?php
                    $overtime_hour = $totalHour - $total_actual_Hour;
                    $overtime_minit = $totalMinit - $total_actual_Minit;
                    $total_overtime_Hour = sprintf('%02d', $overtime_hour) . ':' . sprintf('%02d', $overtime_minit);
                    ?>
                    <tr>
                        <td colspan="5"></td>
                        <td style="background: #eee"><b>@lang('overtime.over_time_hour'):
                                &nbsp;</b>
                        </td>
                        <td style="background: #eee"><b>
                                @if ($total_actual_Hour > 0)
                                    {{ $total_overtime_Hour }}
                                @else
                                    0
                                @endif
                            </b> @lang('common.hours')</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>
