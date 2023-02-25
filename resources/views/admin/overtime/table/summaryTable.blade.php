<div class="table-responsive">
    <table class="table table-bordered table-striped table-hover">
        <thead class="tr_header">
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
                <th>@lang('common.total_overtime')</th>
                <th>@lang('common.total_days')</th>
            </tr>
            <tr>
                <td>#</td>
                <th>@lang('common.name')</th>
                <th>@lang('employee.designation')</th>
                @foreach ($monthToDate as $head)
                    <th>{{ $head['day'] }}</th>
                @endforeach
                <th class="text-center">#</th>
                <th class="text-center">#</th>
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
                            // $explodeString = explode(':', $value->workingHour);
                            // $time1 = $explodeString[0];
                            // $time2 = $explodeString[1];
                            // $time3 = $explodeString[2];
                            // $dateString = abs($time1) . ':' . $time2 . ':' . $time3;
                            // dd($dateString);
                            if ($v['working_hour'] < $v['working_time']) {
                                $interval = $v['working_hour']->diff($v['working_time']);
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
            <script>
                {!! "$('.totalCol').attr('colspan',$totalCol+3);" !!}
            </script>
        </tbody>
    </table>
</div>
