<?php

namespace App\Http\Controllers\Payroll;

use App\Exports\BulkPayrollExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Payroll\SalaryController;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\Department;
use App\Model\EarnedLeave;
use App\Model\Employee;
use App\Model\FoodAllowanceDeductionRule;
use App\Model\MonthlyWorkingDay;
use App\Model\PayRoll;
use App\Model\PayRollSetting;
use App\Repositories\PayrollRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class RegeneratePayrollController extends Controller
{

    protected $payrollRepository;
    protected $salaryController;

    public function __construct(PayrollRepository $payrollRepository, SalaryController $salaryController)
    {
        $this->payrollRepository = $payrollRepository;
        $this->salaryController = $salaryController;
    }

    public function index(Request $request)
    {
        \set_time_limit(0);

        $fdate = $request->fdate;
        $tdate = $request->tdate;
        $month = $request->month;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $preview = $request->preview;
        $dataSetCount = 0;

        $departmentList = Department::get();
        $branchList = Branch::get();
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->whereNotIn('branch_id', ['', null, '0'])->select('employee_id', 'finger_id', 'first_name', 'last_name')->get();
        $payrollSetting = PayRollSetting::first();
        $canteenSetting = FoodAllowanceDeductionRule::first();
        $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime(monthConvertFormtoDB($request->month))))->first();
        $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . dateConvertFormtoDB($request->fdate) . '","' . dateConvertFormtoDB($request->tdate) . '")'));

        if ($preview != true) {

            $qry_payroll = '';
            $qry = 'status=' . UserStatus::$ACTIVE;

            if ($branch_id != '' && $branch_id != null) {
                $qry .= ' AND branch_id=' . $branch_id;
                $qry_payroll .= ' AND branch=' . $branch_id;
            }

            if ($department_id != '' && $department_id != null) {
                $qry .= ' AND department_id=' . $department_id;
                $qry_payroll .= ' AND department=' . $department_id;

            }

            $employeePayroll = PayRoll::whereRaw('month= ' . date('m', strtotime(monthConvertFormtoDB($request->month))) . ' and year= ' . date('Y', strtotime(monthConvertFormtoDB($request->month))) . $qry_payroll . '')->pluck('employee')->toArray();
            $dataSetCount = Employee::whereNotIn('employee_id', $employeePayroll)->whereRaw($qry)->count();
        }

        $wageSheet = [];

        if ($_POST && $preview == true) {
            $wageSheet = $this->verifyWageSheet($branch_id, dateConvertFormtoDB($request->fdate), dateConvertFormtoDB($request->tdate), monthConvertFormtoDB($request->month), $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails, $department_id);
        }

        return \view('admin.payroll.regeneratePayroll.index', compact('branchList', 'dataSetCount', 'department_id', 'departmentList', 'wageSheet', 'month', 'preview', 'branch_id', 'tdate', 'fdate', 'employeeList', 'payrollSetting'));
    }

    public function verifyWageSheet($branch_id = '', $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails, $department_id = '')
    {
        \set_time_limit(0);
        // dd($branch_id, $fdate, $tdate, $month);

        $dataFormat = [];
        $qry_payroll = '1 ';
        $qry = 'status=' . UserStatus::$ACTIVE;

        if ($branch_id != '' && $branch_id != null) {
            $qry .= ' AND branch_id=' . $branch_id;
            $qry_payroll .= ' AND branch=' . $branch_id;
        }

        if ($department_id != '' && $department_id != null) {
            $qry .= ' AND department_id=' . $department_id;
            $qry_payroll .= ' AND department=' . $department_id;

        }
        $employeePayroll = [];
        $employeePayroll = PayRoll::whereRaw('month= ' . date('m', strtotime($month)) . ' and year= ' . date('Y', strtotime($month)) . $qry_payroll . '')->pluck('employee')->toArray();

        // $employeeAllInfo = Employee::whereRaw($qry)->get();
        // $duplicateEmployeeAllInfo = Employee::whereIn('employee_id', $employeePayroll)->whereRaw($qry)->get();
        // $payrollSetting = PayRollSetting::first();

        $freshEmployeeAllInfo = Employee::whereNotIn('employee_id', $employeePayroll)->whereRaw($qry)->get();

        if ($fdate && $tdate && $month) {

            foreach ($freshEmployeeAllInfo as $key => $employee) {

                $employeeSalaryDetails = $this->salaryController->getEmployeeSalaryDetails($employee, $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails);

                $employeeSalaryDetails['fdate'] = $fdate;
                $employeeSalaryDetails['tdate'] = $tdate;
                $employeeSalaryDetails['month'] = $month;

                $data = [
                    'employeeAllInfo' => $employee,
                    'monthOfSalary' => $month,
                    'employeeSalaryDetails' => $employeeSalaryDetails,
                    'payrollSetting' => $payrollSetting,
                ];

                $dataFormat[$key] = $this->payrollRepository->makeSalaryDataFormat($data, true);
                $dataFormat[$key]['duplicate'] = false;
                if (in_array($employee->employee_id, $employeePayroll)) {
                    $dataFormat[$key]['duplicate'] = true;
                }

            }
        }
        return $dataFormat;

    }
    public function storeWageReport(Request $request)
    {

        DB::beginTransaction();

        $payroll = json_decode($request->wageSheet);

        if ($request->preview == false) {

            $fdate = dateConvertFormtoDB($request->fdate);
            $tdate = dateConvertFormtoDB($request->tdate);
            $month = monthConvertFormtoDB($request->month);
            $branch_id = isset($request->branch_id) ? $request->branch_id : "";
            $department_id = isset($request->department_id) ? $request->department_id : "";

            $payrollSetting = PayRollSetting::first();
            $canteenSetting = FoodAllowanceDeductionRule::first();
            $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($month)))->first();
            $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $fdate . '","' . $tdate . '")'));

            $payroll = $this->verifyWageSheet($branch_id, $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails, $department_id);

            $payroll = json_decode(json_encode($payroll));

        }

        foreach ($payroll as $key => $input) {

            try {
                $bug = null;
                $payrollDataFormat = [];
                $el = $input->earned_leave;
                $el_balance = $input->earned_leave_balance;

                $elDataset = [
                    'employee_id' => $input->employee,
                    'month' => $input->month,
                    'year' => $input->year,
                    'el_balance' => $el_balance,
                    'el' => $el,
                    'status' => 1,
                ];

                $emp = Employee::find($input->employee);

                $input->status = 1;
                $input->created_by = $request->auth_id;
                $input->updated_by = $request->auth_id;
                $input->created_at = Carbon::now();
                $input->updated_at = Carbon::now();
                $input->status = 1;

                $payrollDataFormat = (array) $input;

                // dd(gettype($payrollDataFormat), gettype($elDataset));

                PayRoll::create($payrollDataFormat);
                EarnedLeave::create($elDataset);
                $emp->update(['leave_balance' => number_format((float) $el + $el_balance, 2, '.', ''), 'leave_updated_at' => Carbon::now()]);

                $bug = 0;
            } catch (\Exception $e) {
                $bug = $e->getMessage();
                info($bug);
            }

        }

        if ($bug == 0) {
            DB::commit();
            return 'success';
            // return redirect('regeneratePayroll')->with('success', 'WageSheet created successfully');
        } else {
            DB::rollBack();
            return 'error';
            // return redirect('regeneratePayroll')->with('error', 'Something went wrong! Please trry again!');
        }
    }

    public function storeAllEmployeeWageReport(Request $request)
    {
        $branch = '';
        $qry = 'status=' . UserStatus::$ACTIVE;

        if ($request->branch_id) {
            $qry .= ' AND branch_id=' . $request->branch_id;
            $branch = ' AND branch=' . $request->branch_id;
        }

        $employeeAllInfo = Employee::whereRaw($qry)->get();
        // $employeePayroll = [];
        // $employeePayroll = PayRoll::whereRaw('month= ' . date('m', strtotime($request->month)) . ' and year= ' . date('Y', strtotime($request->month)) . $branch . '')->pluck('employee')->toArray();
        // $freshEmployeeAllInfo = Employee::whereNotIn('employee_id', $employeePayroll)->whereRaw($qry)->get();

        $payrollSetting = PayRollSetting::first();
        $canteenSetting = FoodAllowanceDeductionRule::first();
        $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($request->month)))->first();
        $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $request->fdate . '","' . $request->tdate . '")'));

        foreach ($employeeAllInfo as $key => $employee) {

            $employeeSalaryDetails = $this->salaryController->getEmployeeSalaryDetails($employee, $request->fdate, $request->tdate, $request->month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails);
            $employeeSalaryDetails['fdate'] = $request->fdate;
            $employeeSalaryDetails['tdate'] = $request->tdate;
            $employeeSalaryDetails['month'] = $request->month;

            $data = [
                'employeeAllInfo' => $employee,
                'monthOfSalary' => $request->month,
                'employeeSalaryDetails' => $employeeSalaryDetails,
                'payrollSetting' => $payrollSetting,
            ];

            $input = $this->payrollRepository->makeSalaryDataFormat($data);

            try {

                DB::beginTransaction();

                $bug = null;

                $ifPayrollExists = PayRoll::where('employee', $input['employee'])->where('month', $input['month'])->where('year', $input['year'])->first();

                $el = $input['earned_leave'];
                $el_balance = $input['earned_leave_balance'];

                $elDataset = [
                    'employee_id' => $input['employee'],
                    'month' => $input['month'],
                    'year' => $input['year'],
                    'el_balance' => $el_balance,
                    'el' => $el,
                    'status' => 1,
                ];

                $emp = Employee::find($input['employee']);

                if (!$ifPayrollExists) {
                    $input['status'] = 1;
                    $payroll_data = PayRoll::create($input);
                    $el_data = EarnedLeave::create($elDataset);
                    $upd_el = $emp->update(['leave_balance' => number_format((float) $el + $el_balance, 2, '.', ''), 'leave_updated_at' => Carbon::now()]);
                }

                DB::commit();

                echo 'success';
            } catch (\Exception $e) {
                DB::rollBack();
                $bug = $e->getMessage();
                info($bug);
                echo 'error';
            }

        }

    }

    public function WageReportDataFormat($wageSheet, $auth_id, $branch_id, $fdate, $tdate, $month, $department_id, $preview)
    {
        \set_time_limit(0);

        $defaultData = [
            'created_by' => $auth_id,
            'updated_by' => $auth_id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
            'status' => 1,
        ];

        $WageReportDataFormat = json_decode($wageSheet);

        if ($preview == false) {

            $payrollSetting = PayRollSetting::first();
            $canteenSetting = FoodAllowanceDeductionRule::first();
            $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($month)))->first();
            $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $fdate . '","' . $tdate . '")'));

            $payroll = $this->verifyWageSheet($branch_id, $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails, $department_id);
            $payroll = array_merge($payroll, $defaultData);
            $WageReportDataFormat = json_decode(json_encode($payroll));
        }

    }

    public function WageReportExcel(Request $request)
    {
        $dataset = [];

        $payroll = json_decode($request->wageSheet);

        if ($request->preview == false) {
            $fdate = dateConvertFormtoDB($request->fdate);
            $tdate = dateConvertFormtoDB($request->tdate);
            $month = monthConvertFormtoDB($request->month);
            $branch_id = isset($request->branch_id) ? $request->branch_id : "";
            $department_id = isset($request->department_id) ? $request->department_id : "";

            $payrollSetting = PayRollSetting::first();
            $canteenSetting = FoodAllowanceDeductionRule::first();
            $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($month)))->first();
            $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $fdate . '","' . $tdate . '")'));

            $payroll = $this->verifyWageSheet($branch_id, $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails, $department_id);
            $payroll = json_decode(json_encode($payroll));
        }

        $inc = 1;

        foreach ($payroll as $key => $Data) {

            $otHours = explode(':', $Data->ot_hours);
            $otHours = $otHours[0] . '.' . $otHours[1];
            $manHours = explode(':', $Data->manhours);
            $manHours = $manHours[0] . '.' . $manHours[1];

            $dataset[] = [
                (int) $inc,
                DATE('M-Y', strtotime($request->month . "-01")),
                $Data->contractor_name,
                '-',
                '-',
                $Data->finger_print_id,
                $Data->fullName,
                $Data->cost_center_number,
                $Data->department_name,
                (string) $Data->no_day_wages,
                (string) $Data->company_holiday,
                (string) $Data->ph,
                (string) $Data->total_days,
                (string) $Data->per_day_basic_da,
                (string) $Data->per_day_hra,
                (string) $Data->per_day_wages,
                (string) $Data->basic_da_amount,
                (string) $Data->hra_amount,
                (string) $Data->wages_amount,
                (string) $Data->attendance_bonus,
                (string) $Data->ot_hours,
                (string) $Data->ot_per_hours,
                (string) $Data->ot_amount,
                (string) $Data->gross_salary,
                (string) $Data->employee_pf,
                (string) $Data->employee_esic,
                (string) $Data->canteen,
                (string) $Data->net_salary,
                (string) $Data->employer_pf,
                (string) $Data->employer_esic,
                (string) $Data->service_charge,
                (string) $Data->bonus_amount,
                (string) $Data->earned_leave_balance,
                (string) $Data->earned_leave,
                (string) $Data->leave_amount,
                (string) $Data->manhours,
                (string) $Data->manhour_days,
                (string) $Data->salary,
            ];

            $inc++;
        }

        // dump($dataset);
        // dd($payroll);

        $filename = 'Wage-sheet-' . DATE('dmY-', strtotime($request->fdate)) . strtotime($request->tdate) . '.xlsx';
        $date = $request->month . "-01";
        $extraData = ['subtitle2' => 'Detailed Wage Sheet Report Between ' . $request->fdate . ' - ' . $request->tdate . '.', 'subtitle3' => 'Month / Year ' . DATE('M-Y', strtotime($date)) . ' '];

        $heading = [
            ['Duroflex Pvt. Ltd.'],
            [$extraData['subtitle2']],
            [$extraData['subtitle3']],
            [
                'Sr.No.',
                'Month',
                'Unit',
                'TOM ',
                'Service Provider',
                'Employee.No',
                'Name of the Labour',
                'Cost Centrer',
                'Department',
                'No. of W.days',
                'C.D.H ',
                'P.H ',
                'Total DAYS',
                'Basic & DA @ Per Day',
                'HRA per Day',
                'Per Day Wages',
                'Basic & DA Earned ',
                'HRA Earned',
                'Wages Earned',
                'Attendance Bonus',
                'OT hours',
                'OT Per hrs',
                'OT Earned',
                'Gross salary',
                'PF Deduct Employee',
                'ESI Deduct Employee',
                'Canteen',
                'Net Salary',
                'PF Employer',
                'ESI Employer',
                'Service Charges',
                'Bonus',
                'Accumulated EL days',
                'Earned Leave days',
                'Earned Leave amount',
                'Manhours',
                'Mandays',
                'CTC',
            ],
        ];
        $extraData['heading'] = $heading;
        // dd($dataset, $extraData);
        return Excel::download(new BulkPayrollExport($dataset, $extraData), $filename);
    }

}
