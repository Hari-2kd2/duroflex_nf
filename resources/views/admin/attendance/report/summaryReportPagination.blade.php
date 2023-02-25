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
        <p style="margin-left: 32px;margin-top: 10px"><b>@lang('attendance.attendance_summary_report')</b></p>
    </div>
    <div class="container">
        @php
            $colCount = count($monthToDate) + count($leaveTypes) + 4;
        @endphp
        <b aria-colspan="{{ $colCount }}">Month : </b>{{ $month }}
        <div class="table-responsive" style="font-size: 12px">
            <table id="" class="table table-bordered table-striped table-hover" style="font-size: 12px">
                <thead>
                    <tr>
                        <th>@lang('common.serial')</th>
                        <th>@lang('common.year')</th>
                        <th colspan="1" class="totalCol">@lang('common.month')

                        </th>
                        <th colspan="{{ count($monthToDate) + 2 }}">
                            {{ 'FP - Full Present,' }}&nbsp;
                            {{ 'HP - Half Present,' }}&nbsp;
                            {{ ' LOP - Loss of Pay,' }}&nbsp;
                            {{ ' AA - Absent,' }}&nbsp;
                            {{ ' HD - Holiday,' }}&nbsp;
                            {{ ' OTP - One time Punch.' }}
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
                        <th>#</th>
                        @foreach ($monthToDate as $head)
                            <th>{{ $head['day_name'] }}</th>
                        @endforeach
                        <th>@lang('attendance.worked_days')</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>#</td>
                        <th>@lang('common.employee_name')</th>
                        <th>@lang('common.employee_id')</th>
                        <th>@lang('common.branch')</th>
                        @foreach ($monthToDate as $head)
                            <th>{{ $head['day'] }}</th>
                        @endforeach
                        <th>#</th>
                    </tr>

                    @php
                        $sl = null;
                        $totalPresent = 0;
                        $leaveData = [];
                        $totalCol = 0;
                        $totalWorkHour = 0;
                        $totalGovDayWorked = 0;
                    @endphp
                    @foreach ($results as $key => $value)
                        <tr>
                            <td>{{ ++$sl }}</td>
                            <td>{{ $value[0]['fullName'] }}</td>
                            <td>{{ $value[0]['finger_id'] }}</td>
                            <td>{{ $value[0]['branch_name'] }}</td>
                            @foreach ($value as $v)
                                @php
                                    // dd($v);
                                    if ($sl == 1) {
                                        $totalCol++;
                                    }
                                    if ($v['attendance_status'] == 'present') {
                                        // $totalPresent++;
                                        if (isset($v['working_time'])) {
                                            $workingTime = new DateTime($v['working_time']);
                                            $fullDay = new DateTime(\App\Lib\Enumerations\PayrollConstant::$FULL_DAY);
                                            $halfDay = new DateTime(\App\Lib\Enumerations\PayrollConstant::$HALF_DAY);
                                            if ($workingTime >= $fullDay) {
                                                (float) ($totalPresent += 1);
                                                echo "<td><span style='color:#7ace4c ;font-weight:bold'>FP</span></td>";
                                            } elseif ($workingTime >= $halfDay) {
                                                (float) ($totalPresent += 0.5);
                                                echo "<td><span style='color:#7ace4c ;font-weight:bold'>HP</span></td>";
                                            } elseif ($workingTime < $halfDay && $workingTime != null) {
                                                (float) ($totalPresent += 0);
                                                echo "<td><span style='color:#f33155 ;font-weight:bold'>LOP</span></td>";
                                            }
                                        } else {
                                            // $totalGovDayWorked++;
                                            echo "<td><span style='color: #7ace4c ;font-weight:bold'>OTP</span></td>";
                                        }
                                    } elseif ($v['attendance_status'] == 'absence') {
                                        echo "<td><span style='color:#f33155 ;font-weight:bold'>AA</span></td>";
                                    } elseif ($v['attendance_status'] == 'leave') {
                                        $leaveData[$key][$v['leave_type']][] = $v['leave_type'];
                                        echo "<td><span style='color:#41b3f9 ;font-weight:bold'>LL</span></td>";
                                    } elseif ($v['attendance_status'] == 'holiday') {
                                        echo "<td><span style='color:turquoise ;font-weight:bold'>HD</span></td>";
                                    } else {
                                        echo '<td></td>';
                                    }
                                @endphp
                            @endforeach
                            <td><span class="bolt">{{ $totalPresent }}</span></td>
                            @php
                                $totalPresent = 0;
                                $totalGovDayWorked = 0;
                            @endphp
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
