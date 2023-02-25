<div class="table-responsive">
    <table class="table table-hover manage-u-table">
        <thead>
            <tr class="tr_header">
                <th class="text-center">#</th>
                <th>@lang('dashboard.photo')</th>
                <th>@lang('common.name')</th>
                <th>Date</th>
                <th>@lang('dashboard.in_time')</th>
                <th>@lang('dashboard.out_time')</th>
                <th>@lang('common.action')</th>

            </tr>
        </thead>
        <tbody>
            @if (count($attendanceData) > 0)
                {{ $dailyAttendanceSl = null }}
                @foreach ($attendanceData as $key => $dailyAttendance)
                    @php
                        // dd($attendanceData);
                    @endphp
                    {{-- @if ($dailyAttendance->in_time != null && $dailyAttendance->out_time != null) --}}
                    <tr class="{!! $dailyAttendance->employee_id !!}">
                        <td class="text-center">{{ ++$dailyAttendanceSl }}</td>
                        <td>
                            @if ($dailyAttendance->photo != '')
                                <img style=" width: 50px; " src="{!! asset('uploads/employeePhoto/' . $dailyAttendance->photo) !!}" alt="user-img" class="img-circle">
                            @else
                                <img style=" width: 50px; " src="{!! asset('admin_assets/img/default.png') !!}" alt="user-img"
                                    class="img-circle">
                            @endif
                        </td>
                        @if ($dailyAttendance->fullName)
                            <td>{{ $dailyAttendance->fullName }}
                                <br /><span class="text-muted">{{ $dailyAttendance->department_name }}</span>
                            </td>
                        @else
                            <td>{{ $dailyAttendance->first_name . ' ' . $dailyAttendance->last_name }}
                                <br /><span class="text-muted">{{ $dailyAttendance->department_name }}</span>
                            </td>
                        @endif

                        <td>{{ $dailyAttendance->date }} </td>
                        <td>{{ $dailyAttendance->in_time }} </td>
                        <td>
                            <?php
                            if ($dailyAttendance->out_time != '') {
                                echo $dailyAttendance->out_time;
                            } else {
                                echo '--';
                            }
                            ?>
                        </td>
                        <td style="width: 100px;">
                            <a href="{!! route('mobileAttendance.mobile_attendance', [
                                'employee_id' => $dailyAttendance->employee_id,
                                'date' => $dailyAttendance->date,
                            ]) !!}" data-token="{!! csrf_token() !!}" {{-- <a href="{!! url('mobile_attendance/?employee_id=' . $dailyAttendance->employee_id . 'date=' . $dailyAttendance->date) !!}" data-token="{!! csrf_token() !!}" --}}
                                data-id="{!! $dailyAttendance->employee_id !!}" class="btn btn-success btn-xs btnColor"><i
                                    class="fa fa-eye" aria-hidden="true"></i></a>
                        </td>

                    </tr>
                    {{-- @endif --}}
                @endforeach
            @else
                <tr>
                    <td colspan="8">@lang('common.no_data_available')</td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
