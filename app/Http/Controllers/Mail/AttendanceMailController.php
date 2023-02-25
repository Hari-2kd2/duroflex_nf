<?php

namespace App\Http\Controllers\Mail;

use App\Exports\AttendanceReportExport;
use App\Exports\DailyAttendanceReportExport;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\AppConstants;
use App\Model\Branch;
use App\Model\Department;
use App\Repositories\AttendanceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceMailController extends Controller
{

    protected $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function index($date = '', $department_id = '', $branch_id = '', $status = '')
    {

        if ($date == '') {
            $date = date('Y-m-d', strtotime('-1 days'));
        }

        $completeDataset = DB::select("call `SP_DepartmentDailyAttendance`('" . $date . "', '" . $department_id . "','" . $branch_id . "','" . $status . "')");
        $extraDataset = ['subtitle2' => 'Daily Attendance Report', 'subtitle3' => ' ' . DATE('d/m/Y', strtotime($date)) . ' '];
        // dd($date,$completeDataset);
        if (count($completeDataset) > 0) {
            $filename_alldata = 'excel/daily-attendance-report-' . DATE('dmY', strtotime($date)) . '.xlsx';
            $this->excel($completeDataset, $extraDataset, $filename_alldata);
            $this->mailing($filename_alldata, 'emails/attendanceMail', $completeDataset, $date);
        }
    }

    public function mailing($filename, $type, $dataSet, $date)
    {
        $settings = ['email' => 'hari9578@gmail.com'];
        $emails = explode(",", $settings['email']);

        $email_notification = DB::table('email_notification')->first();
        $emails = explode(',', $email_notification->email);

        foreach ($emails as $Mail) {

            $data = [];
            $data = array('title' => AppConstants::$ATTREPORT);

            $mail = Mail::send($type, $data, function ($message) use ($filename, $settings, $Mail, $date) {
                $message->from(AppConstants::$SENDER, AppConstants::$ORGANIZATION . ' ' . AppConstants::$PLANT);
                $message->to($Mail, AppConstants::$ADMIN)->subject(AppConstants::$MAIL_TITLE . ' - ' . $date);
                $message->attach(base_path() . "/storage/app/" . $filename);
            });
        }

        unlink(base_path() . "/storage/app/" . $filename);
    }

    public function excel($attDataSet, $subTitle, $filename)
    {
        $dataset = [];
        $extraData = [];

        $inc = 1;

        foreach ($attDataSet as $key => $Data) {
            $dataset[] = [
                $inc,
                $Data->date ?? 'NA',
                $Data->fullName ?? 'NA',
                $Data->finger_print_id ?? 'NA',
                $Data->branch_name ?? 'NA',
                $Data->department_name ?? 'NA',
                $Data->shift_name ?? 'NA',
                $Data->in_time ?? '00:00',
                $Data->out_time ?? '00:00',
                $Data->working_time ?? '00:00',
                $Data->early_by ?? '00:00',
                $Data->late_by ?? '00:00',
                $Data->over_time ?? '00:00',
                $Data->in_out_time ?? '00/00/0000 00:00:00',
                attStatus($Data->attendance_status),
            ];
            $inc++;
        }

        $heading = [
            ['DUROFLEX Pvt. Ltd.'],
            [$subTitle['subtitle2'] . '-' . $subTitle['subtitle3']],
            [
                'Sr.No.',
                'Date',
                'Name of the Employee',
                'Employee Id',
                'Contractor',
                'Department',
                'Shift',
                'In Time',
                'Out Time',
                'Duration',
                'Early By',
                'Late By',
                'Over Time',
                'Biometric Reader Records',
                'Status',
            ],
        ];

        $extraData['heading'] = $heading;

        return Excel::store(new AttendanceReportExport($dataset, $extraData), $filename);
    }

    public function dailyExcel($date, $branch, $dept, $status)
    {
        \set_time_limit(0);
        $branchName = '';
        $deptName = '';

        $departmentList = Department::where('department_id', $dept)->first();
        $branchList = Branch::where('branch_id', $branch)->first();
        $results = $this->attendanceRepository->getEmployeeDailyAttendance($date, $dept, $branch, $status);

        $data = [
            'results' => $results,
            'date' => $date,
            'branch_id' => $branch,
            'branch_name' => isset($branchList) ? $branchList->branch_name : $branchName,
            'department_id' => $dept,
            'department_name' => isset($departmentList) ? $departmentList->department_name : $deptName,
        ];

        $excel = new DailyAttendanceReportExport('admin.attendance.report.dailyReportPagination', $data);

        $excelFile = Excel::download($excel, 'dailyAttendanceReport' . date('Ymd', strtotime($date)) . '.xlsx');

        return $excelFile;
    }

    public function test($date = '', $department_id = '', $branch_id = '', $status = '')
    {

        $departmentList = Department::all();

        $array = [];
        $presentDataSet = [];
        $absentDataSet = [];
        $lessHoursDataSet = [];
        $onlyInDataSet = [];
        $onlyOutDataSet = [];

        if ($date == '') {
            $date = date('d-m-Y', strtotime('-1 days'));
        }

        $email_notification = DB::table('email_notification')->first();
        $emails = explode(',', $email_notification->email);
        // dd($emails);

        $completeDataset = DB::select("call `SP_DepartmentDailyAttendance`('" . $date . "', '" . $department_id . "','" . $branch_id . "','" . $status . "')");
        $extraDataset = ['subtitle2' => 'Daily Attendance Report', 'subtitle3' => ' ' . DATE('d-m-Y', strtotime($date)) . ' '];

        if (count($completeDataset) > 0) {
            $filename_alldata = 'excel/dailyattendance-report-' . DATE('dmY') . '.xlsx';
            $this->excel($completeDataset, $extraDataset, $filename_alldata);
            $this->mailing($filename_alldata, 'emails/attendanceMail', $completeDataset, $date);
        }

        // foreach ($departmentList as $key => $value) {

        //     $present = $this->attendanceRepository->getEmployeeDailyAttendance($date, $value->department_id, '', AttendanceStatus::$PRESENT);
        //     $absent = $this->attendanceRepository->getEmployeeDailyAttendance($date, $value->department_id, '', AttendanceStatus::$ABSENT);
        //     $lessHours = $this->attendanceRepository->getEmployeeDailyAttendance($date, $value->department_id, '', AttendanceStatus::$LESSHOURS);
        //     $onlyIn = $this->attendanceRepository->getEmployeeDailyAttendance($date, $value->department_id, '', AttendanceStatus::$ONETIMEINPUNCH);
        //     $onlyOut = $this->attendanceRepository->getEmployeeDailyAttendance($date, $value->department_id, '', AttendanceStatus::$ONETIMEOUTPUNCH);

        //     if (!empty($present)) {
        //         $presentDataSet = $present[$value->department_name];
        //     }

        //     if (!empty($absent)) {
        //         $absentDataSet = $absent[$value->department_name];
        //     }

        //     if (!empty($lessHours)) {
        //         $lessHoursDataSet = $lessHours[$value->department_name];
        //     }

        //     if (!empty($onlyIn)) {
        //         $onlyInDataSet = $onlyIn[$value->department_name];
        //     }

        //     if (!empty($onlyOut)) {
        //         $onlyOutDataSet = $onlyOut[$value->department_name];
        //     }
        // }

        // $extraDataPresent = ['subtitle2' => 'Daily Present Report', 'subtitle3' => ' ' . DATE('d-m-Y', strtotime($date)) . ' '];
        // $extraDataAbsent = ['subtitle2' => 'Daily Absent Report', 'subtitle3' => ' ' . DATE('d-m-Y', strtotime($date)) . ' '];
        // $extraDataLessHours = ['subtitle2' => 'Less Hours - Daily Report ', 'subtitle3' => ' ' . DATE('d-m-Y', strtotime($date)) . ' '];
        // $extraDataOnlyIn = ['subtitle2' => 'Only In Punch - Daily Report', 'subtitle3' => ' ' . DATE('d-m-Y', strtotime($date)) . ' '];
        // $extraDataOnlyOut = ['subtitle2' => 'Only Out Punch - Daily Report', 'subtitle3' => ' ' . DATE('d-m-Y', strtotime($date)) . ' '];

        // if (count($presentDataSet) > 0) {
        //     $filename_present = 'excel/dailyreport-present' . DATE('d-m-Y') . '.xlsx';
        //     $this->excel($presentDataSet, $extraDataPresent, $filename_present);
        //     $this->mailing($filename_present, 'emails/attendanceMail', $presentDataSet);
        // }

        // if (count($absentDataSet) > 0) {
        //     $filename_absent = 'excel/dailyreport-absent' . DATE('d-m-Y') . '.xlsx';
        //     $this->excel($absentDataSet, $extraDataAbsent, $filename_absent);
        //     $this->mailing($filename_absent, 'emails/attendanceMail', $absentDataSet);
        // }

        // if (count($lessHoursDataSet) > 0) {
        //     $filename_lesshour = 'excel/dailyreport-lesshour' . DATE('d-m-Y') . '.xlsx';
        //     $this->excel($lessHoursDataSet, $extraDataLessHours, $filename_lesshour);
        //     $this->mailing($filename_lesshour, 'emails/attendanceMail', $lessHoursDataSet);
        // }

        // if (count($onlyInDataSet) > 0) {
        //     $filename_in = 'excel/dailyreport-onlyin' . DATE('d-m-Y') . '.xlsx';
        //     $this->excel($onlyInDataSet, $extraDataOnlyIn, $filename_in);
        //     $this->mailing($filename_in, 'emails/attendanceMail', $onlyInDataSet);
        // }

        // if (count($onlyOutDataSet) > 0) {
        //     $filename_out = 'excel/dailyreport-onlyout' . DATE('d-m-Y') . '.xlsx';
        //     $this->excel($onlyOutDataSet, $extraDataOnlyOut, $filename_out);
        //     $this->mailing($filename_out, 'emails/attendanceMail', $onlyOutDataSet);
        // }
    }
}
