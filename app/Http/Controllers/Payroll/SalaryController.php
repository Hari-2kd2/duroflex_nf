<?php

namespace App\Http\Controllers\Payroll;

use App\Exports\SalaryReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollRequest;
use App\Lib\Enumerations\OvertimeStatus;
use App\Lib\Enumerations\PayrollConstant;
use App\Lib\Enumerations\ServiceCharge;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\CostCenter;
use App\Model\Department;
use App\Model\EarnedLeave;
use App\Model\Employee;
use App\Model\EmployeeFoodAndTelephoneDeduction;
use App\Model\EmployeeInOutData;
use App\Model\FoodAllowanceDeductionRule;
use App\Model\MonthlyWorkingDay;
use App\Model\PayRoll;
use App\Model\PayRollSetting;
use App\Model\SubDepartment;
use App\Repositories\AttendanceRepository;
use App\Repositories\PayrollRepository;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

// use Itstructure\GridView\DataProviders\EloquentDataProvider;

class SalaryController extends Controller
{

    protected $attendanceRepository;
    protected $payrollRepository;

    public function __construct(AttendanceRepository $attendanceRepository, PayrollRepository $payrollRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
        $this->payrollRepository = $payrollRepository;
    }

    public function index(Request $request)
    {
        \set_time_limit(0);

        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        $payrollSetting = PayRollSetting::first();

        return \view('admin.payroll.salary.index', compact('branchList', 'departmentList', 'date', 'branch_id', 'department_id', 'attendance_status', 'employeeList', 'payrollSetting'));
    }

    public function details(Request $request)
    {
        $data = [];
        $qry = "1 ";
        if ($request->employee) {
            $qry .= " AND employee=" . $request->employee;
        }

        if ($request->branch) {
            $qry .= " AND branch=" . $request->branch;
        }

        if ($request->department) {
            $qry .= " AND department=" . $request->department;
        }

        if ($request->date) {
            $qry .= " AND month=" . date('m', strtotime($request->date));
            $qry .= " AND year=" . date('Y', strtotime($request->date));
        }

        $i = 0;
        if ($request->date) {
            $data = PayRoll::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->with('employeeinfo', 'departmentinfo', 'branchInfo', 'costcenterInfo', 'subunitInfo')->orderBy('updated_at', 'DESC');
        }

        // return true;
        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return
                '<a href="' . route('payslip.generation', ['id' => $data->payroll_id]) . '" class="btn btn-xs btn-info" title="Payslip" target="_blank" data-id="' . $data->payroll_id . '"><i style="color: #fff" class="fa fa-file-pdf-o btn-info"></i></a>';
            })

            ->editColumn('employee', function ($data) {
                return $data->employeeinfo->first_name . " " . $data->employeeinfo->last_name;
            })
            ->editColumn('month', function ($data) {
                return DATE('M', strtotime($data->month));
            })
            ->editColumn('date', function ($data) {
                return $data->date;
            })
            ->editColumn('year', function ($data) {
                return $data->year;
            })
            ->addColumn('branch', function ($data) {
                return $data->branchInfo->branch_name;
            })
            ->addColumn('subunit', function ($data) {
                return $data->subunitInfo->sub_department_name;

            })
            ->addColumn('costcenter', function ($data) {
                return $data->costcenterInfo->cost_center_number;
            })
            ->addColumn('department', function ($data) {
                return $data->departmentinfo->department_name;
            })
            ->addColumn('salary_oh', function ($data) {
                $salary_oh = number_format((float) $data->gross_salary + $data->employer_pf + $data->employer_esic + $data->service_charge + $data->bonus_amount + $data->leave_amount, 2, '.', '');
                return $salary_oh;
            })
            ->addColumn('month_year', function ($data) {
                $month_year = "01-" . $data->month . "-" . $data->year;
                return DATE('M-y', strtotime($month_year));
            })
            ->addColumn('man_days', function ($data) {
                $manDays = $data->manhour_days + $data->ph + $data->company_holiday;
                return $manDays;
            })
            ->rawColumns(['action'])
            ->addColumn('sl.no', function ($data) use ($i) {
                $i++;
                return $i;
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function generation(Request $request)
    {
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();
        return view('admin.payroll.salary.generation', ['employeeList' => $employeeList]);
    }

    public function generationPayrollForAllEmployee($fdate, $tdate, $month)
    {

        $employeeAllInfo = Employee::where('status', UserStatus::$ACTIVE)->get();
        $payrollSetting = PayRollSetting::first();
        $canteenSetting = FoodAllowanceDeductionRule::first();
        $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($month)))->first();
        $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $fdate . '","' . $tdate . '")'));

        foreach ($employeeAllInfo as $key => $employee) {
            $employeeSalaryDetails = $this->getEmployeeSalaryDetails($employee, $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails);
            $employeeSalaryDetails['fdate'] = $fdate;
            $employeeSalaryDetails['tdate'] = $tdate;
            $employeeSalaryDetails['month'] = $month;

            $data = [
                'employeeAllInfo' => $employee,
                'monthOfSalary' => $month,
                'employeeSalaryDetails' => $employeeSalaryDetails,
                'payrollSetting' => $payrollSetting,
            ];

            $input = $this->payrollRepository->makeSalaryDataFormat($data);

            try {

                $bug = null;

                $ifPayrollExists = PayRoll::where('employee', $input['employee'])->where('month', $input['month'])->where('year', $input['year'])->first();
                $ifEarnedLeaveExists = EarnedLeave::where('employee_id', $input['employee'])->where('month', $input['month'])->where('year', $input['year'])->first();

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
                    PayRoll::create($input);

                    EarnedLeave::create($elDataset);

                    $emp->update(['leave_balance' => number_format((float) $el + $el_balance, 2, '.', ''), 'leave_updated_at' => Carbon::now()]);
                } else {

                    $ifEarnedLeaveExists->update(['el' => $el + $ifEarnedLeaveExists->el_balance, 'status' => 2]);

                    $emp->update(['leave_balance' => number_format((float) $ifEarnedLeaveExists->el_balance + $el, 2, '.', ''), 'leave_updated_at' => Carbon::now()]);

                    $input['status'] = 2;
                    $ifPayrollExists->update($input);
                }
            } catch (\Exception $e) {
                $bug = $e->getMessage();
                info($bug);
            }
        }
    }

    public function store(PayrollRequest $request)
    {

        $input = $request->all();
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;
        // dd($input);
        try {

            $bug = null;

            $ifPayrollExists = PayRoll::where('employee', $input['employee'])->where('month', $input['month'])->where('year', $input['year'])->first();
            $ifEarnedLeaveExists = EarnedLeave::where('employee_id', $input['employee'])->where('month', $input['month'])->where('year', $input['year'])->first();

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

                PayRoll::create($input);

                EarnedLeave::create($elDataset);

                $emp->update(['leave_balance' => number_format((float) $el + $el_balance, 2, '.', ''), 'leave_updated_at' => Carbon::now()]);

                $bug = 0;
            } else {

                $ifEarnedLeaveExists->update(['el' => $el + $ifEarnedLeaveExists->el_balance, 'status' => 2]);

                $emp->update(['leave_balance' => number_format((float) $ifEarnedLeaveExists->el_balance + $el, 2, '.', ''), 'leave_updated_at' => Carbon::now()]);

                $ifPayrollExists->update($input);

                $bug = 1;
            }
        } catch (\Exception $e) {
            $bug = $e->getMessage();
        }

        if ($bug == 0) {
            return redirect('salaryInfo')->with('success', 'Payroll Details Saved Successfully saved.');
        } elseif ($bug == 1) {
            return redirect('salaryInfo')->with('success', 'Payroll Details Updated  Successfully saved.');
        } else {
            return redirect('salaryInfo')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function sheet(Request $request)
    {
        // dd($request->all());

        $employeePayroll = [];
        $employeePayroll = PayRoll::whereRaw('month= ' . date('m', strtotime($request->month)) . ' and year= ' . date('Y', strtotime($request->month)) . ' and employee=' . $request->employee_id . '')->first();

        $employeeAllInfo = Employee::where('employee_id', $request->employee_id)->where('status', UserStatus::$ACTIVE)->first();
        $fdate = dateConvertFormtoDB($request->fdate);
        $tdate = dateConvertFormtoDB($request->tdate);

        if ($employeePayroll) {
            return redirect()->back()->with('error', 'Trying to generate duplicate entry for an Employee ' . trim($employeeAllInfo->first_name . ' ' . $employeeAllInfo->last_name) . ' on ' . date('F Y', strtotime($request->month)));
        }

        $payrollSetting = PayRollSetting::first();
        $canteenSetting = FoodAllowanceDeductionRule::first();
        $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($request->month)))->first();
        $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $fdate . '","' . $tdate . '")'));

        $employeeSalaryDetails = $this->getEmployeeSalaryDetails($employeeAllInfo, $fdate, $tdate, $request->month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails);

        $employeeSalaryDetails['fdate'] = $fdate;
        $employeeSalaryDetails['tdate'] = $tdate;

        if ($employeeSalaryDetails['status'] == true) {
            $data = [
                'employeeAllInfo' => $employeeAllInfo,
                'monthOfSalary' => $request->month,
                'employeeSalaryDetails' => $employeeSalaryDetails,
                'payrollSetting' => $payrollSetting,
            ];

            return view('admin.payroll.salary.sheet', $data);
        } else {
            return redirect('wageSheet')->with('error', 'Something Error Found !' . 'Reason- ' . $employeeSalaryDetails['message']);
        }

    }

    public function getEmployeeSalaryDetails($employeeAllInfo, $fdate, $tdate, $month, $payrollSetting, $canteenSetting, $monthlyWorkingDays, $holidayDetails)
    {

        try {
            (float) $totalPresent = 0.0;

            (float) $publicHolidays = 0.0;

            (float) $companyHolidays = 0.0;

            (float) $paidHolidays = 0.0;

            $salaryToBePaid = 0;

            $overTimeArray = [];

            $workingTimeArray = [];

            $compHolidayArr = [];

            $monthName = date('F', strtotime($month));

            $wageFrom = date('F', strtotime($fdate));

            $wageTo = date('F', strtotime($tdate));

            // $monthlyWorkingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($month)))->first();

            // $payrollSetting = PayRollSetting::first();

            $monthName = strtolower(substr($monthName, 0, 3));

            $totalWorkingDays = $monthlyWorkingDays->$monthName;

            $getDate = $this->getMonthToStartDateAndEndDate($month);

            $workingDates = $this->number_of_working_days_date($fdate, $tdate);

            // $employeeInOutData = EmployeeInOutData::where('finger_print_id', $employeeAllInfo->finger_id)->whereBetween('date', [$fdate, $tdate])->where('attendance_status', AttendanceStatus::$PRESENT)->get();
            $employeeInOutData = EmployeeInOutData::where('finger_print_id', $employeeAllInfo->finger_id)->whereBetween('date', [$fdate, $tdate])->get();

            // $holidayDetails = DB::select(DB::raw('call SP_getHoliday("' . $fdate . '","' . $tdate . '")'));

            $companyHolidayDetails = DB::select(DB::raw('call SP_getCompanyHoliday("' . $fdate . '","' . $tdate . '","' . $employeeAllInfo->employee_id . '")'));

            foreach ($workingDates as $key => $value) {

                $isHoliday = $this->ifHoliday($holidayDetails, $value);

                $ifCompanyHoliday = $this->ifCompanyHoliday($companyHolidayDetails, $value);

                if ($ifCompanyHoliday) {
                    $companyHolidays += 1;
                    $compHolidayArr['days'] = $companyHolidays;
                    $compHolidayArr['date'][] = $value;
                }

                if ($isHoliday['status'] == true) {

                    $publicHolidays += 1;

                    $dateOfPH = date('Y-m-d', strtotime($isHoliday['date']));
                    $PreHolidayDate = Carbon::createFromFormat('Y-m-d', $dateOfPH)->subDays(1)->format('Y-m-d');
                    $suffHolidayDate = Carbon::createFromFormat('Y-m-d', $dateOfPH)->addDays(1)->format('Y-m-d');

                    $hasPresent = EmployeeInOutData::where('finger_print_id', $employeeAllInfo->finger_id)->whereIn('date', [$PreHolidayDate, $suffHolidayDate])->get();

                    foreach ($hasPresent as $key => $present) {
                        if ($present->working_time != null) {
                            $paidHolidays += 1;
                            break;
                        }
                    }
                }
            }

            foreach ($employeeInOutData as $key => $inOutData) {

                if (isset($inOutData->working_time)) {
                    $workingTime = new DateTime($inOutData->working_time);
                    $fullDay = new DateTime(PayrollConstant::$FULL_DAY);
                    $halfDay = new DateTime(PayrollConstant::$HALF_DAY);
                    if ($workingTime >= $fullDay) {
                        (float) $totalPresent += 1;
                    } elseif ($workingTime >= $halfDay) {
                        (float) $totalPresent += 0.5;
                    } elseif ($workingTime < $halfDay) {
                        (float) $totalPresent += 0;
                    }
                }

                if ($inOutData->over_time != null && $inOutData->over_time != PayrollConstant::$EMPTY_TIME && $inOutData->over_time_status == OvertimeStatus::$OT_FOUND_AND_APPROVED) {
                    $overTimeArray[] = $inOutData->over_time;
                }

                if ($inOutData->working_time != null) {
                    $workingTimeArray[] = $inOutData->working_time;
                }
            }

            $salaryToBePaid = $totalPresent + $paidHolidays + $companyHolidays;

            $overtime_rate = (float) ($employeeAllInfo->daily_wage / 8.00) * $payrollSetting->ot_per_hour;

            $overTime = $this->calculateEmployeeTotalOverTime($overTimeArray, $overtime_rate);

            $workingHour = $this->calculateEmployeeTotalWorkingHour($workingTimeArray);

            $allowances = $this->calculateEmployeeAllowance($employeeAllInfo, $salaryToBePaid, $payrollSetting, $totalWorkingDays, $overTime, $month, $paidHolidays, $companyHolidays);

            $deductions = $this->calculateEmployeeDeduction($allowances['basicDaEarned'], $month, $payrollSetting, $employeeAllInfo->finger_id, $allowances['grossSalary'], $employeeAllInfo, $canteenSetting);

            $data = [
                'totalWorkingDays' => $totalWorkingDays,
                'publicHolidays' => $publicHolidays,
                'companyHolidays' => $companyHolidays,
                'compHolidayArr' => $compHolidayArr,
                'paidHolidays' => $paidHolidays,
                'totalPresent' => $totalPresent,
                'salaryToBePaid' => $totalPresent + $publicHolidays,
                'oneDaysSalary' => number_format((float) $employeeAllInfo->daily_wage, 2, '.', ''),
                'overtime_rate' => number_format((float) $overtime_rate, 2, '.', ''),
                'totalOverTimeHour' => $overTime['totalOverTimeHour'],
                'totalOvertimeAmount' => number_format((float) $overTime['overtimeAmount'], 2, '.', ''),
                'totalWorkingHour' => $workingHour['totalWorkingHour'],
            ];

            return array_merge($data, $allowances, $deductions, ['status' => true]);
        } catch (\Throwable $th) {
            // throw $th;
            return ['status' => false, 'message' => $th->getMessage()];
        }
    }

    public function calculateEmployeeAllowance($employeeAllInfo, $totalPresent, $payrollSetting, $totalWorkingDays, $overTime, $month, $paidHolidays, $companyHolidays)
    {
        $perDayBasicDaEarned = 0.00;
        $perDayBasicEarned = 0.00;
        $perDayDaEarned = 0.00;
        $perDayHraEarned = 0.00;
        $basicEarned = 0.00;
        $daEarned = 0.00;
        $basicDaEarned = 0.00;
        $bonusEarning = 0.00;
        $hraEarned = 0.00;
        $attendanceBonus = 0.00;
        $overTimeAmount = 0.00;
        $otherAllowance = 0.00;
        $earnLeaveAmount = 0.00;
        $retainedBonusAmount = 0.00;
        $retainedLeaveAmount = 0.00;
        $payroll = null;
        $retainedLeaveAmount = 0.00;
        $retainedBonusAmount = 0.00;
        $retainedAttendanceBonusAmount = 0.00;
        $retainedServiceChargeAmount = 0.00;
        $BonusEarnLeaveAttendanceBonusServiceChargeTotal = 0.00;
        $bonusAndEarnLeaveTotal = 0.00;
        $earnLeaveDays = 0.00;
        $earnLeaveBalance = 0.00;
        $retainedLeaveAmount = 0.00;
        $retainedBonusAmount = 0.00;
        $retainedAttendanceBonusAmount = 0.00;
        $retainedServiceChargeAmount = 0.00;
        $BonusEarnLeaveAttendanceBonusServiceChargeTotal = 0.00;

        // dd($totalPresent);

        $perDayBasicDaEarned = number_format((float) $employeeAllInfo->basic_amt + $employeeAllInfo->da_amt, 2, '.', '');
        $basicDaEarned = number_format((float) $totalPresent * $perDayBasicDaEarned, 2, '.', '');

        $perDayBasicEarned = number_format((float) $employeeAllInfo->basic_amt, 2, '.', '');
        $basicEarned = number_format((float) $perDayBasicEarned * $totalPresent, 2, '.', '');

        $perDayDaEarned = number_format((float) $employeeAllInfo->da_amt, 2, '.', '');
        $daEarned = number_format((float) $perDayDaEarned * $totalPresent, 2, '.', '');

        $perDayHraEarned = $employeeAllInfo->hra_amt;
        $hraEarned = number_format((float) $totalPresent * $perDayHraEarned, 2, '.', '');

        $bonusEarning = $basicDaEarned > 7000 ? number_format((float) 7000 * ($payrollSetting->bonus / 100), 2, '.', '') : number_format((float) $basicDaEarned * ($payrollSetting->bonus / 100), 2, '.', '');

        $wagesEarned = number_format((float) $basicDaEarned + $hraEarned, 2, '.', '');

        $overTimeAmount = number_format((float) $overTime['overtimeAmount'], 2, '.', '');

        $otherAllowance = number_format((float) $payrollSetting->other_allowance, 2, '.', '');

        $thisPayroll = PayRoll::where('employee', $employeeAllInfo->employee_id)->where('year', date('Y', strtotime($month)))->where('month', date('m', strtotime($month)))->where('el_bonus', 0)->orderByDesc('payroll_id')->first();
        $thisEarnedLeave = EarnedLeave::where('employee_id', $employeeAllInfo->employee_id)->where('year', date('Y', strtotime($month)))->where('month', date('m', strtotime($month)))->orderByDesc('earned_leave_id')->first();

        $earnLeaveDays = ($totalPresent - $paidHolidays - $companyHolidays) >= $payrollSetting->el_day_limit ? ($totalPresent - $paidHolidays - $companyHolidays) / $payrollSetting->el_day_limit : 0;

        $earnLeaveDays = number_format((float) $earnLeaveDays, 2, '.', '');

        if ($thisEarnedLeave) {
            $earnLeaveBalance = number_format((float) $thisEarnedLeave->el_balance, 2, '.', '');
        }

        $earnLeaveAmount = $earnLeaveDays * $perDayBasicDaEarned;

        $earnLeaveAmount = number_format((float) $earnLeaveAmount, 2, '.', '');

        if ($totalPresent >= $totalWorkingDays) {
            $attendanceBonus = $payrollSetting->attendance_bonus;
        }

        if (!$thisPayroll) {
            $payroll = PayRoll::where('employee', $employeeAllInfo->employee_id)->where('year', '<=', date('Y', strtotime($month)))->where('month', '<', date('m', strtotime($month)))->where('el_bonus', 0)->orderByDesc('payroll_id')->first();
        }

        $allowances = [
            'perDayBasicEarned' => $perDayBasicEarned,
            'perDayDaEarned' => $perDayDaEarned,
            'perDayBasicDaEarned' => $perDayBasicDaEarned,
            'perDayHraEarned' => $perDayHraEarned,
            'basicDaEarned' => $basicDaEarned,
            'basicEarned' => $basicEarned,
            'daEarned' => $daEarned,
            'hraEarned' => $hraEarned,
            'wagesEarned' => $wagesEarned,
            'attendanceBonus' => $attendanceBonus,
            'earnLeaveDays' => $earnLeaveDays,
            'earnedLeaveBalance' => $earnLeaveBalance,
            'earnedLeaveTotal' => number_format((float) $employeeAllInfo->leave_balance + $earnLeaveDays, 2, '.', ''),
            'earnLeaveAmount' => $earnLeaveAmount,
            'bonusEarning' => $bonusEarning,
            'otherAllowance' => $otherAllowance,
            'bonusAndEarnLeaveTotal' => $bonusAndEarnLeaveTotal,
        ];

        $allowances['grossSalary'] = $basicEarned + $daEarned + $hraEarned + $allowances['attendanceBonus'] + $overTimeAmount + $otherAllowance;

        if ($employeeAllInfo->service_charge == ServiceCharge::$ENABLED) {
            $allowances['serviceCharge'] = number_format((float) $allowances['grossSalary'] * ($payrollSetting->service_charge / 100), 2, '.', '');
        } else {
            $allowances['serviceCharge'] = 0.00;
        }

        if ($payroll) {
            $retainedBonusAmount = number_format((float) $payroll->retained_bonus, 2, '.', '');
            $retainedLeaveAmount = number_format((float) $payroll->retained_leave_amount, 2, '.', '');
            $retainedAttendanceBonusAmount = number_format((float) $payroll->retained_attendance_bonus, 2, '.', '');
            $retainedServiceChargeAmount = number_format((float) $payroll->retained_service_charge, 2, '.', '');
            $BonusEarnLeaveAttendanceBonusServiceChargeTotal = number_format((float) $retainedLeaveAmount + $retainedBonusAmount + $retainedAttendanceBonusAmount + $retainedServiceChargeAmount + $retainedServiceChargeAmount, 2, '.', '');
        } elseif ($thisEarnedLeave && $thisPayroll) {
            $retainedLeaveAmount = number_format((float) $thisEarnedLeave->el_balance * $perDayBasicDaEarned, 2, '.', '');
            $BonusEarnLeaveAttendanceBonusServiceChargeTotal = number_format((float) $retainedLeaveAmount, 2, '.', '');
        } else {
            $retainedLeaveAmount = number_format((float) $employeeAllInfo->leave_balance * $perDayBasicDaEarned, 2, '.', '');
            $BonusEarnLeaveAttendanceBonusServiceChargeTotal = number_format((float) $retainedLeaveAmount, 2, '.', '');
        }

        $bonusAndEarnLeaveTotal = number_format((float) ($earnLeaveAmount + $bonusEarning + $allowances['serviceCharge']), 2, '.', '');

        $allowances['retainedBonusAmount'] = $retainedBonusAmount;
        $allowances['retainedLeaveAmount'] = $retainedLeaveAmount;
        $allowances['retainedAttendanceBonusAmount'] = $retainedAttendanceBonusAmount;
        $allowances['retainedServiceChargeAmount'] = $retainedServiceChargeAmount;
        $allowances['BonusEarnLeaveAttendanceBonusServiceChargeTotal'] = $BonusEarnLeaveAttendanceBonusServiceChargeTotal;
        $allowances['bonusAndEarnLeaveTotal'] = $bonusAndEarnLeaveTotal;

        return $allowances;
    }

    public function calculateEmployeeDeduction($basicDaAllowance, $month, $payrollSetting, $finger_id, $grossSalary, $employeeAllInfo, $canteenSetting)
    {
        $employeeLWF = 0.00;
        $employeeEPF = 0.00;
        $employeeESIC = 0.00;
        $employerEPF = 0.00;
        $employerESIC = 0.00;
        $canteenDeduction = 0.00;
        $yearClosing = 0.00;
        $otherDeduction = 0.00;
        $currentMonth = date('Y-m-01', strtotime($month));
        $dateOfJoining = $employeeAllInfo->date_of_joining;

        $employeeEPF = number_format((float) $basicDaAllowance * ($payrollSetting->employee_pf / 100), 2, '.', '');

        $employerEPF = number_format((float) $basicDaAllowance * ($payrollSetting->employer_pf / 100), 2, '.', '');

        if ($grossSalary < 21000) {
            $employeeESIC = number_format((float) $grossSalary * ($payrollSetting->employee_esic / 100), 2, '.', '');
            $employerESIC = number_format((float) $grossSalary * ($payrollSetting->employer_esic / 100), 2, '.', '');
        }

        $employeeCanteenDetail = EmployeeFoodAndTelephoneDeduction::where('month_of_deduction', $month)->where('finger_print_id', $finger_id)->first();

        if ($employeeCanteenDetail) {

            $breakfastCost = $canteenSetting->breakfast_cost;
            $lunchCost = $canteenSetting->lunch_cost;
            $dinnerCost = $canteenSetting->dinner_cost;

            $breakfastCount = $employeeCanteenDetail->breakfast_count;
            $lunchCount = $employeeCanteenDetail->lunch_count;
            $dinnerCount = $employeeCanteenDetail->dinner_count;

            $canteenDeduction = number_format((float) (($breakfastCost * $breakfastCount) + ($lunchCost * $lunchCount) + ($dinnerCost * $dinnerCount)), 2, '.', '');
        }

        if ($payrollSetting->year_closing == 1) {
            $yearClosing = date('Y-03-01', strtotime($month));
        } else {
            $yearClosing = date('Y-12-01', strtotime($month));
        }

        if (strtotime($yearClosing) == strtotime($currentMonth)) {
            $employeeLWF = 20.00;
        }

        $otherDeduction = number_format((float) $payrollSetting->other_deduction, 2, '.', '');

        $totalDeduction = number_format((float) $canteenDeduction + $employeeEPF + $employeeESIC + $otherDeduction + $employeeLWF, 2, '.', '');

        $netSalary = number_format((float) ($grossSalary - $totalDeduction), 2, '.', '');

        $deductions = [
            'employeeEPF' => $employeeEPF,
            'employeeESIC' => $employeeESIC,
            'employerEPF' => $employerEPF,
            'employerESIC' => $employerESIC,
            'canteenDeduction' => $canteenDeduction,
            'totalDeduction' => $totalDeduction,
            'netSalary' => $netSalary,
            'employeeLWF' => $employeeLWF,
            'otherDeduction' => $otherDeduction,
            'yearClosing' => $yearClosing,
        ];

        return $deductions;
    }

    public function calculateEmployeeTotalOverTime($overTime, $overtime_rate)
    {

        $totalMinute = 0;
        $minuteWiseAmount = 0;
        $hour = 0;
        $minutes = 0;

        foreach ($overTime as $key => $value) {

            $value = explode(':', $value);
            $hour += $value[0];
            $minutes += $value[1];
            if ($minutes >= 60) {
                $minutes -= 60;
                $hour++;
            }
        }

        // $hours       = $hour . ':' . (($minutes < 10) ? '0' . $minutes : $minutes);

        if ($minutes < 30) {
            $hours = $hour . ':00';
        } elseif ($minutes < 60) {
            $hours = $hour . ':30';
        }

        $value = explode(':', $hours);
        $totalMinute = $value[1];
        if ($totalMinute != 0 && $overtime_rate != 0) {

            $perMinuteAmount = $overtime_rate / 60;
            $minuteWiseAmount = $perMinuteAmount * $totalMinute;
        }

        $overtimeAmount = ($value[0] * $overtime_rate) + $minuteWiseAmount;

        return ['totalOverTimeHour' => $hours, 'overtimeAmount' => $overtimeAmount];
    }

    public function calculateEmployeeTotalWorkingHour($workingHour)
    {

        $totalMinute = 0;
        $hour = 0;
        $minutes = 0;

        foreach ($workingHour as $key => $value) {
            $value = explode(':', $value);
            $hour += $value[0];
            $minutes += $value[1];
            if ($minutes >= 60) {
                $minutes -= 60;
                $hour++;
            }
        }

        if ($minutes < 30) {
            $hours = $hour . ':00';
        } elseif ($minutes < 60) {
            $hours = $hour . ':30';
        }

        return ['totalWorkingHour' => $hours];
    }

    public function getEmployeeMonthlyAttendance($from_date, $to_date, $employee_id)
    {
        $monthlyAttendanceData = DB::select("CALL `SP_monthlyAttendance`('" . $employee_id . "','" . $from_date . "','" . $to_date . "')");
        $workingDates = $this->number_of_working_days_date($from_date, $to_date);

        $dataFormat = [];
        $tempArray = [];
        $present = null;

        //   dd($monthlyAttendanceData);

        if ($workingDates && $monthlyAttendanceData) {

            foreach ($workingDates as $data) {

                $flag = 0;

                foreach ($monthlyAttendanceData as $value) {
                    if ($data == $value->date && $value->working_time != '') {
                        $flag = 1;
                        break;
                    }
                }

                $tempArray['total_present'] = null;

                if ($flag == 0) {
                    $tempArray['employee_id'] = $value->employee_id;
                    $tempArray['fullName'] = $value->fullName;
                    $tempArray['department_name'] = $value->department_name;
                    $tempArray['finger_print_id'] = $value->finger_print_id;
                    $tempArray['date'] = $data;
                    $tempArray['working_time'] = '';
                    $tempArray['in_time'] = '';
                    $tempArray['out_time'] = '';
                    $tempArray['workingHour'] = '';
                    $tempArray['total_present'] = $present;
                    $tempArray['action'] = 'Absence';
                    $dataFormat[] = $tempArray;
                } else {
                    $tempArray['total_present'] = $present += 1;
                    $tempArray['employee_id'] = $value->employee_id;
                    $tempArray['fullName'] = $value->fullName;
                    $tempArray['department_name'] = $value->department_name;
                    $tempArray['finger_print_id'] = $value->finger_print_id;
                    $tempArray['date'] = $value->date;
                    $tempArray['working_time'] = $value->working_time;
                    $tempArray['in_time'] = $value->in_time;
                    $tempArray['out_time'] = $value->out_time;
                    $tempArray['workingHour'] = $value->workingHour;
                    $tempArray['action'] = 'Present';
                    $dataFormat[] = $tempArray;
                }
            }
        }
        // dd($dataFormat);
        return $dataFormat;
    }

    public function number_of_working_days_date($from_date, $to_date)
    {
        $target = strtotime($from_date);

        $workingDate = [];

        while ($target <= strtotime(date("Y-m-d", strtotime($to_date)))) {

            \array_push($workingDate, date('Y-m-d', $target));

            if (date('Y-m-d') <= date('Y-m-d', $target)) {
                break;
            }

            $target += (60 * 60 * 24);
        }
        return $workingDate;
    }

    public function getMonthToStartDateAndEndDate($month)
    {

        $month = explode('-', $month);
        $current_year = $month[0];
        $lastMonth = $month[1];

        $firstDate = $current_year . "-" . $lastMonth . "-01";
        $lastDateOfMonth = date('t', strtotime($firstDate));
        $lastDate = $current_year . "-" . $lastMonth . "-" . $lastDateOfMonth;

        return ['firstDate' => $firstDate, 'lastDate' => $lastDate];
    }

    public function ifCompanyHoliday($compHolidays, $date)
    {

        $comp_holidays = [];
        foreach ($compHolidays as $holidays) {
            $start_date = $holidays->fdate;
            $end_date = $holidays->tdate;
            // dump($start_date, $end_date, $date);
            while (strtotime($start_date) <= strtotime($end_date)) {
                $comp_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($comp_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }

        return false;
    }

    public function ifHoliday($govtHolidays, $date)
    {
        $govt_holidays = [];
        $holidayData = [];
        $holidayData['status'] = false;
        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            // dump('Holiday -'.$val);
            // dump('date -'.$date);
            if ($val == $date) {
                $holidayData['status'] = true;
                $holidayData['date'] = $val;
                return $holidayData;
            }
        }
        return $holidayData;
    }

    public function report(Request $request)
    {
        \set_time_limit(0);
        // $dataProvider = new EloquentDataProvider(Payroll::query());

        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $employeeList = Employee::get();

        return \view('admin.payroll.salary.report', compact('branchList', 'departmentList', 'date', 'branch_id', 'department_id', 'attendance_status', 'employeeList'));
    }

    public function reportdetails(Request $request)
    {

        $qry = "1 ";
        if ($request->employee) {
            $qry .= " AND employee=" . $request->employee;
        }

        if ($request->branch) {
            $qry .= " AND branch=" . $request->branch;
        }

        if ($request->department) {
            $qry .= " AND department=" . $request->department;
        }

        if ($request->date) {
            $qry .= " AND month=" . date('m', strtotime($request->date));
            $qry .= " AND year=" . date('Y', strtotime($request->date));
        }

        $i = 0;
        $data = Payroll::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->orderBy('created_at', 'DESC');
        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return '';
                // '<a href="' . route('wageSheet.generation', ['id' => $data->payroll_id]) . '" class="btn btn-xs btn-primary" title="Payslip" target="_blank" data-id="' . $data->payroll_id . '"><i class="fa fa-file-pdf-o"></i></a>';
            })

            ->editColumn('employee', function ($data) {
                $emp = Employee::find($data->employee);
                if ($emp) {
                    return $emp->first_name . " " . $emp->last_name;
                }
            })
            ->editColumn('month', function ($data) {
                $month = "01-" . $data->month . "-" . DATE('Y');
                return DATE('M', strtotime($month));
            })
            ->editColumn('employee', function ($data) {
                $emp = Employee::find($data->employee);
                if ($emp) {
                    return $emp->first_name . " " . $emp->last_name;
                }
            })
            ->editColumn('month', function ($data) {
                $month = "01-" . $data->month . "-" . DATE('Y');
                return DATE('M', strtotime($data->month));
            })
            ->editColumn('date', function ($data) {
                return $data->date;
            })
            ->editColumn('year', function ($data) {
                return $data->year;
            })
            ->addColumn('branch', function ($data) {
                $branch = Branch::where('branch_id', $data->branch)->first();
                if ($branch) {
                    return $branch->branch_name;
                } else {
                    return 'NA';
                }
            })
            ->addColumn('subunit', function ($data) {
                $subunit = SubDepartment::where('sub_department_id', $data->unit)->first();
                if ($subunit) {
                    return $subunit->sub_department_name;
                } else {
                    return 'NA';
                }
            })
            ->addColumn('costcenter', function ($data) {
                $costcenter = CostCenter::where('cost_center_id', $data->costcenter)->first();
                if ($costcenter) {
                    return $costcenter->cost_center_number;
                } else {
                    return 'NA';
                }
            })
            ->addColumn('department', function ($data) {
                $department = Department::where('department_id', $data->department)->first();
                if ($department) {
                    return $department->department_name;
                }
            })
            ->addColumn('allowance', function ($data) {
                $allowance = number_format((float) $data->basic_da_amount + $data->hra_amount + $data->attendance_bonus + $data->ot_amount + $data->other_allowance, 2, '.', '');
                return $allowance;
            })
            ->addColumn('deduction', function ($data) {
                $allowance = number_format((float) $data->employee_pf + $data->employee_esic + $data->canteen + $data->lwf + $data->other_deduction, 2, '.', '');
                return $allowance;
            })
            ->addColumn('month_year', function ($data) {
                $month_year = "01-" . $data->month . "-" . $data->year;
                return DATE('M-y', strtotime($month_year));
            })
            ->addColumn('sl.no', function ($data) use ($i) {
                $i++;
                return $i;
            })
            ->rawColumns(['action'])
            ->addIndexColumn()
            ->make(true);
    }

    public function download(Request $request)
    {
        $dataset = [];
        $branchName = '';

        $qry = "1 ";
        if ($request->employee) {
            $qry .= " AND employee=" . $request->employee;
        }

        if ($request->branch_id) {
            $qry .= " AND branch=" . $request->branch;
        }

        if ($request->department_id) {
            $qry .= " AND department=" . $request->department;
        }

        if ($request->date) {
            $qry .= " AND month=" . (int) date('m', strtotime($request->date));
            $qry .= " AND year=" . date('Y', strtotime($request->date));
        }

        $payroll = Payroll::whereRaw("('" . $qry . "')")->get();
        // dump($request->all());
        // dd($payroll);
        $inc = 1;
        foreach ($payroll as $key => $Data) {

            $branchName = $request->branch ? $Data->branchInfo->branch_name : 'ALL Contractors';

            $dataset[] = [
                $inc,
                DATE('M-Y', strtotime($request->date . "-01")),
                $Data->branchInfo->branch_name,
                '-',
                '-',
                $Data->finger_print_id,
                $Data->employeeinfo->first_name . " " . $Data->employeeinfo->last_name,
                $Data->costcenterInfo->cost_center_number,
                $Data->departmentinfo->department_name,
                $Data->no_day_wages,
                $Data->company_holiday,
                $Data->ph,
                $Data->total_days,
                $Data->per_day_basic_da,
                $Data->per_day_hra,
                $Data->per_day_wages,
                $Data->basic_da_amount,
                $Data->hra_amount,
                $Data->wages_amount,
                $Data->attendance_bonus,
                $Data->ot_hours,
                $Data->ot_per_hours,
                $Data->ot_amount,
                $Data->gross_salary,
                $Data->employee_pf,
                $Data->employee_esic,
                $Data->canteen,
                $Data->net_salary,
                $Data->employer_pf,
                $Data->employer_esic,
                $Data->service_charge,
                $Data->bonus_amount,
                $Data->earned_leave_balance,
                $Data->earned_leave,
                $Data->leave_amount,
                $Data->manhours,
                $Data->manhour_days,
                $Data->salary,
            ];

            $inc++;
        }

        $filename = 'salary-report-' . DATE('d-m-Y-h-i-A') . '.xlsx';
        $date = $request->date . "-01";
        $extraData = ['subtitle2' => 'Name of the Contractor -' . $branchName, 'subtitle3' => 'Month / Year ' . DATE('M-Y', strtotime($date)) . ' '];

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
                'Cost Centre',
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
        //dd($dataset);
        return Excel::download(new SalaryReport($dataset, $extraData), $filename);
    }
}
