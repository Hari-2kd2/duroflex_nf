<div>
    <div class="table-responsive">
        <table class="table table-bordered" style="font-size: 12px">
            <thead class="tr_header bg-title">
                <tr>
                    <td style="font-size: 14px;font-weight:bold" colspan="15" class="text-center">
                        {{ 'Daily Attendance Report' }}
                    </td>
                </tr>
                <tr>
                    <th style="width:50px;">SL.NO</th>
                    <th style="font-size:12px;">DATE</th>
                    <th style="font-size:12px;width:200px;">NAME OF THE EMPLOYEE</th>
                    <th style="font-size:12px;">EMPLOYEE ID</th>
                    <th style="font-size:12px;">CONTRACTOR</th>
                    <th style="font-size:12px;">DEPARTMENT</th>
                    <th style="font-size:12px;">SHIFT</th>
                    <th style="font-size:12px;">IN TIME</th>
                    <th style="font-size:12px;">OUT TIME</th>
                    <th style="font-size:12px;">DURATION</th>
                    <th style="font-size:12px;">EARLY BY</th>
                    <th style="font-size:12px;">LATE BY</th>
                    <th style="font-size:12px;">OVERTIME</th>
                    <th style="font-size:12px;width:350px;">IN/OUT RECORDS</th>
                    <th style="font-size:12px;width:50px;">STATUS</th>
                </tr>
            </thead>

            <tbody>
                @php
                    $sl = 0;
                @endphp
                @forelse ($results as $key => $data)
                    {{-- <tr>
                        <td colspan="14">
                            <strong>{{ $key }}</strong>
                        </td>
                    </tr> --}}

                    @forelse ($data as $key1 => $value)
                        @php
                            $zero = '00:00';
                            $isHoliday = false;
                            $holidayDate = '';
                        @endphp
                        <tr>
                            <td style="font-size:12px;">{{ ++$sl }}</td>
                            <td style="font-size:12px;">{{ $value->date }}</td>
                            <td style="font-size:12px;">{{ $value->fullName }}</td>
                            <td style="font-size:12px;">{{ $value->finger_print_id }}</td>
                            <td style="font-size:12px;">{{ $value->branch_name }}</td>
                            <td style="font-size:12px;">{{ $key }}</td>
                            <td style="font-size:12px;">
                                @php
                                    if ($value->shift_name != '' && $value->shift_name != null) {
                                        echo $value->shift_name;
                                    } else {
                                        echo 'NA';
                                    }
                                @endphp
                            </td>
                            <td style="font-size:12px;">
                                @php
                                    if ($value->in_time != '') {
                                        echo $value->in_time;
                                    } else {
                                        echo '00:00';
                                    }
                                @endphp
                            </td>
                            <td style="font-size:12px;">
                                @php
                                    if ($value->out_time != '') {
                                        echo $value->out_time;
                                    } else {
                                        echo '00:00';
                                    }
                                @endphp
                            </td>

                            <td style="font-size:12px;">
                                @php
                                    if ($value->working_time != null) {
                                        echo date('H:i', strtotime($value->working_time));
                                        // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                    } else {
                                        echo '00:00';
                                    }
                                @endphp
                            </td>

                            <td style="font-size:12px;">
                                @php
                                    if ($value->early_by != null) {
                                        echo date('H:i', strtotime($value->early_by));
                                        // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                    } else {
                                        echo '00:00';
                                    }
                                @endphp
                            </td>
                            <td style="font-size:12px;">
                                @php
                                    if ($value->late_by != null) {
                                        echo date('H:i', strtotime($value->late_by));
                                        // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                    } else {
                                        echo '00:00';
                                    }
                                @endphp
                            </td>
                            <td style="font-size:12px;">
                                @php
                                    if ($value->over_time != null) {
                                        echo date('H:i', strtotime($value->over_time));
                                        // echo "<b style='color: black'>" . date('H:i', strtotime($value->working_time)) . '</b>';
                                    } else {
                                        echo '00:00';
                                    }
                                @endphp
                            </td>

                            <td style="font-size:12px;">
                                @php
                                    if ($value->in_out_time != null) {
                                        echo $value->in_out_time;
                                        // echo "<b style='color: green'>" . date('H:i', strtotime($value->late_by)) . '</b>';
                                    } else {
                                        echo '00/00/00 00:00:NA';
                                        // echo "<b style='color: blue'>" . $zero . '</b>';
                                    }
                                @endphp
                            </td>

                            <td style="font-size:12px;">
                                <?php
                                echo attStatus($value->attendance_status);
                                ?>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19">@lang('common.no_data_available') !</td>
                        </tr>
                    @endforelse
                @empty
                    <tr>
                        <td colspan="19">@lang('common.no_data_available') !</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
