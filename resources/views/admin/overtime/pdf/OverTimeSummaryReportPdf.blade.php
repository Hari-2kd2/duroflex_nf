<!DOCTYPE html>
<html lang="en">

<head>
    <title>@lang('overtime.overtime_summary_report')</title>
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
        font-size: 10px;
        border: 1px solid black;
    }

    td {
        font-size: 8px;
        padding: 3px;
    }

    th {
        padding: 3px;
    }

    .present {
        color: #7ace4c;
        font-weight: 700;
    }

    .absence {
        color: #f33155;
        font-weight: 700;
    }

    .leave {
        color: #41b3f9;
        font-weight: 700;
    }

    .bolt {
        font-weight: 700;
    }

</style>

<body>
    <div class="printHead">
        @if ($printHead)
            {!! $printHead->description !!}
        @endif
        <p style="margin-left: 42px;margin-top: 10px"><b>@lang('overtime.overtime_summary_report')</b></p>
    </div>
    <div class="container">
        <b>Month : </b>{{ $month }}
        @php
            $colCount = count($monthToDate);
            $colCount += count($leaveTypes) + 3;
        @endphp

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    @php
                        $colCount = count($monthToDate);
                    @endphp
                    <tr>
                        <th>@lang('common.serial')</th>
                        <th>@lang('common.year')</th>
                        <th colspan={{ $colCount + 3 }} class="totalCol">@lang('common.month')
                        </th>
                    </tr>
                    <tr>
                        <th>#</th>
                        <th>
                            @if (isset($month))
                                @php
                                    
                                    $exp = explode('-', $month);
                                    echo $exp[0];
                                @endphp
                            @else
                                {{ date('Y') }}
                            @endif
                        </th>
                        <th>{{ $monthName }}</th>
                        @foreach ($monthToDate as $head)
                            <th>{{ $head['day_name'] }}</th>
                        @endforeach
                        <th>@lang('common.total_overtime') (Hours)</th>
                        <th>@lang('common.total_days')</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sl = null;
                        $over_time = 0;
                        $total_time = 0;
                        $total_days = 0;
                        $totalCol = 0;
                        $totalHour = 0;
                        $totalMinit = 0;
                        $sum = 0;
                        $overtime = '-';
                    @endphp
                    @foreach ($results as $key => $value)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $key }}</td>
                            <td>{{ $value[0]['designation_name'] }}</td>

                            @foreach ($value as $v)
                                <?php
                                if ($sl == 1) {
                                    $totalCol++;
                                }
                                if ($v['status'] == 'true') {
                                    if ($v['format_expected_hour'] < $v['working_time']) {
                                        $interval = $v['format_expected_hour']->diff($v['working_time']);
                                        $overtime = $interval->format('%H:%I');
                                        $explode = explode(':', $overtime);
                                        $totalHour = (int) $explode[0] * 60;
                                        $totalMinit = (int) $explode[1];
                                        $total_time = $totalHour + $totalMinit;
                                        $sum += $total_time;
                                        $total_days++;
                                        echo "<td><span class='true' title='true'>$overtime</span></td>";
                                    } else {
                                        echo '<td>-</td>';
                                    }
                                } else {
                                    echo '<td>-</td>';
                                }
                                ?>
                            @endforeach
                            @php
                                $totaltime = $sum / 60;
                                $totalHour = floor($sum / 60);
                                $roundHour = $totalHour;
                                $totalMinit = ($totaltime - $totalHour) * 60;
                                $total_overtime = sprintf('%02d', $totalHour) . ':' . sprintf('%02d', $totalMinit);
                            @endphp
                            <td><span class="bolt">{{ $total_overtime }}</span></td>
                            <td><span class="bolt">{{ $total_days }}</span></td>
                            @php
                                $total_days = 0;
                                $total_time = 0;
                                $overtime = 0;
                                $sum = 0;
                            @endphp
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>
