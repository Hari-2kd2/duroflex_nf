<?php

namespace App\Repositories;

use App\Lib\Enumerations\UserStatus;
use App\Model\AdvanceDeduction;
use App\Model\Employee;
use App\Model\EmployeeAttendanceApprove;
use App\Model\EmployeeFoodAndTelephoneDeduction;
use App\Model\EmployeeInOutData;
use App\Model\FoodAllowanceDeductionRule;
use App\Model\PayGradeToAllowance;
use App\Model\PayGradeToDeduction;
use App\Model\SalaryDeductionForLateAttendance;
use App\Model\TaxRule;
use App\Model\TelephoneAllowanceDeductionRule;
use App\Repositories\AttendanceRepository;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;

class PayrollRepository
{

    protected $attendanceRepository;

    public function __construct(AttendanceRepository $attendanceRepository)
    {
        $this->attendanceRepository = $attendanceRepository;
    }

    public function pay_grade_to_allowance($pay_grade_id)
    {
        return PayGradeToAllowance::select('allowance.*')
            ->join('allowance', 'allowance.allowance_id', '=', 'pay_grade_to_allowance.allowance_id')
            ->where('pay_grade_id', $pay_grade_id)->get();
    }

    public function pay_grade_to_deduction($pay_grade_id)
    {
        return PayGradeToDeduction::select('deduction.*')
            ->join('deduction', 'deduction.deduction_id', '=', 'pay_grade_to_deduction.deduction_id')
            ->where('pay_grade_id', $pay_grade_id)->get();
    }

    public function calculateEmployeeAllowance($basic_salary, $gross_salary, $pay_grade_id)
    {
        $allowances = $this->pay_grade_to_allowance($pay_grade_id);
        $allowanceArray = [];
        $totalAllowance = 0;

        foreach ($allowances as $key => $allowance) {
            $temp = [];
            $temp['allowance_id'] = $allowance->allowance_id;
            $temp['allowance_name'] = $allowance->allowance_name;
            $temp['allowance_type'] = $allowance->allowance_type;
            $temp['percentage_of_basic'] = $allowance->percentage_of_basic;
            $temp['allowance_criteria'] = $allowance->allowance_criteria;
            $temp['limit_per_month'] = $allowance->limit_per_month;

            if ($allowance->allowance_type == 'Percentage') {
                if ($allowance->allowance_criteria == 1) {
                    $percentageOfAllowance = ($basic_salary * $allowance->percentage_of_basic) / 100;
                } else {
                    $percentageOfAllowance = ($gross_salary * $allowance->percentage_of_basic) / 100;
                }
                if ($allowance->limit_per_month != 0 && $percentageOfAllowance >= $allowance->limit_per_month) {
                    $temp['amount_of_allowance'] = $allowance->limit_per_month;
                } else {
                    $temp['amount_of_allowance'] = $percentageOfAllowance;
                }
            } else {
                $temp['amount_of_allowance'] = $allowance->limit_per_month;
            }
            $totalAllowance += $temp['amount_of_allowance'];
            $allowanceArray[$key] = $temp;
        }

        return ['allowanceArray' => $allowanceArray, 'totalAllowance' => $totalAllowance];
    }

    public function calculateEmployeeDeduction($basic_salary, $gross_salary, $pay_grade_id)
    {
        $deductions = $this->pay_grade_to_deduction($pay_grade_id);
        $deductionArray = [];
        $totalDeduction = 0;
        $lwf = 0;

        if (date('m', strtotime('-1 months')) == '12') {
            $lwf = 20;
        }

        foreach ($deductions as $key => $deduction) {
            $temp = [];
            $temp['deduction_id'] = $deduction->deduction_id;
            $temp['deduction_name'] = $deduction->deduction_name;
            $temp['deduction_type'] = $deduction->deduction_type;
            $temp['percentage_of_basic'] = $deduction->percentage_of_basic;
            $temp['deduction_criteria'] = $deduction->deduction_criteria;
            $temp['limit_per_month'] = $deduction->limit_per_month;

            if ($deduction->deduction_type == 'Percentage') {
                if ($deduction->deduction_criteria == 1) {
                    $percentageOfDeduction = $basic_salary * $deduction->percentage_of_basic / 100;
                } else {
                    $percentageOfDeduction = $gross_salary * $deduction->percentage_of_basic / 100;
                }
                if ($deduction->limit_per_month != 0 && $percentageOfDeduction >= $deduction->limit_per_month) {
                    $temp['amount_of_deduction'] = $deduction->limit_per_month;
                } else {
                    $temp['amount_of_deduction'] = $percentageOfDeduction;
                }
            } else {
                $temp['amount_of_deduction'] = $deduction->limit_per_month;
            }
            $totalDeduction += $temp['amount_of_deduction'];
            $deductionArray[$key] = $temp;
        }
        return ['deductionArray' => $deductionArray, 'totalDeduction' => $totalDeduction, 'lwf' => $lwf];
    }

    /**
     *
     * @employee tax calculation
     *
     *
     */

    public function calculateEmployeeTax($gross_salary, $basic_salary, $date_of_birth, $gender, $pay_grade_id)
    {
        $result = $this->calculateEmployeeAllowance($basic_salary, $gross_salary, $pay_grade_id);
        $birthday = $this->getEmployeeAge($date_of_birth);
        $tax = 0;
        $tax = $gross_salary - $result['totalAllowance'];
        $totalTax = $tax * 12;
        if ($birthday >= 65 || $gender == 'Female') {
            $taxRule = TaxRule::where('gender', 'Female')->get();
        } else {
            $taxRule = TaxRule::where('gender', 'Male')->get();
        }

        $yearlyTax = 0;
        foreach ($taxRule as $value) {
            if ($totalTax <= 0) {
                break;
            }
            if ($totalTax >= $value->amount && $value->amount != 0) {
                $yearlyTax += ($value->amount * $value->percentage_of_tax) / 100;
                $totalTax = $totalTax - $value->amount;
            } else {
                $yearlyTax += ($totalTax * $value->percentage_of_tax) / 100;
                $totalTax = $totalTax - $totalTax;
            }
        }

        $monthlyTax = 0;
        if ($yearlyTax != 0) {
            $monthlyTax = $yearlyTax / 12;
        }
        $data = [
            'monthlyTax' => round($monthlyTax),
            'taxAbleSalary' => $tax,
        ];

        return $data;
    }

    public function getEmployeeAge($date_of_birth)
    {
        $birthday = new DateTime($date_of_birth);
        $currentDate = new DateTime('now');
        $interval = $birthday->diff($currentDate);
        return $interval->y;
    }

    /**
     *
     * @employee total working days
     * @employee total leave
     * @employee total late             @@ getEmployeeOtmAbsLvLtAndWokDays()
     * @employee total late amount
     * @employee total over time
     * @employee total present
     *
     */

    public function getEmployeeOtmAbsLvLtAndWokDays($employee_id, $month, $overtime_rate, $basic_salary)
    {
        $current_month = Carbon::today()->format('Y-m');
        // dd($current_month, $month );
        if ($month == $current_month) {
            $getDate = $this->getMonthToStartDateAndEndDate($month);
            $queryResult = $this->attendanceRepository->getEmployeeMonthlyAttendance($getDate['firstDate'], $getDate['lastDate'], $employee_id);

            $overTime = [];
            $totalPresent = 0;
            $totalAbsence = 0;
            $totalLeave = 0;
            $totalLate = 0;
            $totalLateAmount = 0;
            $totalAbsenceAmount = 0;
            $totalWorkingDays = count($queryResult);

            foreach ($queryResult as $value) {
                if ($value['action'] == 'Absence') {
                    $totalAbsence += 1;
                } elseif ($value['action'] == 'Leave') {
                    $totalLeave += 1;
                } else {
                    $totalPresent += 1;
                }

                if ($value['ifLate'] == 'Yes') {
                    $totalLate += 1;
                }

                $workingHour = new DateTime($value['workingHour']);
                $workingTime = new DateTime($value['working_time']);
                if ($workingHour < $workingTime) {
                    $interval = $workingHour->diff($workingTime);
                    $overTime[] = $interval->format('%H:%I');
                }
            }

            /**
             * @employee Salary Deduction For Late Attendance
             */

            $salaryDeduction = SalaryDeductionForLateAttendance::where('status', 'Active')->first();
            $dayOfSalaryDeduction = 0;
            $oneDaysSalary = 0;
            if ($basic_salary != 0 && $totalWorkingDays != 0 && $totalLate != 0 && !empty($salaryDeduction)) {
                $numberOfDays = 0;
                for ($i = 1; $i <= $totalLate; $i++) {
                    $numberOfDays++;
                    if ($numberOfDays == $salaryDeduction->for_days) {
                        $dayOfSalaryDeduction += 1;
                        $numberOfDays = 0;
                    }
                }

                $oneDaysSalary = $basic_salary / $totalWorkingDays;
                $totalLateAmount = $oneDaysSalary * $dayOfSalaryDeduction;
            }

            /**
             * @employee Salary Deduction For absence
             */

            if ($totalAbsence != 0 && $basic_salary != 0 && $totalWorkingDays != 0) {
                $perDaySalary = $basic_salary / $totalWorkingDays;
                $totalAbsenceAmount = $perDaySalary * $totalAbsence;
            }

            // $oneDaySalary = $basic_salary / $totalWorkingDays;
            $oneDaySalary = isset($basic_salary) ? $basic_salary / $totalWorkingDays : 0;

            $overTime = $this->calculateEmployeeTotalOverTime($overTime, $overtime_rate);
            $data = [
                'overtime_rate' => $overtime_rate,
                'totalOverTimeHour' => $overTime['totalOverTimeHour'],
                'totalOvertimeAmount' => $overTime['overtimeAmount'],
                'totalPresent' => $totalPresent,
                'totalAbsence' => $totalAbsence,
                'totalAbsenceAmount' => round($totalAbsenceAmount),
                'totalLeave' => $totalLeave,
                'totalLate' => $totalLate,
                'dayOfSalaryDeduction' => $dayOfSalaryDeduction,
                'totalLateAmount' => round($totalLateAmount),
                'totalWorkingDays' => $totalWorkingDays,
                'oneDaysSalary' => $oneDaySalary,
            ];
            // dd($data);
            return $data;
        } else {
            return $data = null;
        }
    }

    public function getEmployeeOtmAbsLvLtAndWokDaysManualWay($employee_id, $month, $overtime_rate, $basic_salary)
    {

        $getDate = $this->getMonthToStartDateAndEndDate($month);

        $employee = Employee::where('employee_id', $employee_id)->first();

        $queryResult = $this->attendanceRepository->getEmployeeMonthlyAttendance($getDate['firstDate'], $getDate['lastDate'], $employee_id);

        $employeeInOutData = EmployeeInOutData::where('finger_print_id', $employee->finger_id)->whereBetween('date', [$getDate['firstDate'], $getDate['lastDate']])->get();

        $totalDays = explode('-', $getDate['lastDate']);
        $totalDays = $totalDays[2];

        $overTime = [];
        $totalPresent = 0;
        $totalAbsence = 0;
        $totalLeave = 0;
        $totalLate = 0;
        $totalLateAmount = 0;
        $totalAbsenceAmount = 0;
        $totalHoliday = 0;
        $totalDays = findMonthToAllDate($month);
        $totalWorkingDays = count($queryResult);

        foreach ($employeeInOutData as $key => $inOutData) {

            $totalPresent += 1;

            if ($inOutData->over_time != null && $inOutData->over_time != '00:00:00') {
                $overTime[] = $inOutData->over_time;
            }

            if ($inOutData->late_by != null && $inOutData->late_by != '00:00:00') {
                $totalLate += 1;
            }

            // if ($inOutData->attendance_status != null && $inOutData->attendance_status == 3) {
            //     $totalLeave += 1;
            // }
        }

        foreach ($queryResult as $value) {
            if ($value['action'] == 'Leave') {
                $totalLeave += 1;
            }

            if ($value['action'] == 'Holiday') {
                $totalHoliday += 1;
            }

            // if ($value['action'] == 'Absence') {
            //     $totalAbsence += 1;
            // } elseif ($value['action'] == 'Leave') {
            //     $totalLeave += 1;
            // } else {
            //     $totalPresent += 1;
            // }

            // if ($value['ifLate'] == 'Yes') {
            //     $totalLate += 1;
            // }

            // $workingHour = new DateTime($value['workingHour']);
            // $workingTime = new DateTime($value['working_time']);
            // if ($workingHour < $workingTime) {
            //     $interval   = $workingHour->diff($workingTime);
            //     $overTime[] = $interval->format('%H:%I');
            // }
        }

        $totalAbsence = ($totalWorkingDays - ($totalPresent + $totalLeave + $totalHoliday));

        /**
         * @employee Salary Deduction For Late Attendance
         */

        $salaryDeduction = SalaryDeductionForLateAttendance::where('status', 'Active')->first();
        $dayOfSalaryDeduction = 0;
        $oneDaysSalary = 0;
        if ($basic_salary != 0 && $totalWorkingDays != 0 && $totalLate != 0 && !empty($salaryDeduction)) {
            $numberOfDays = 0;
            for ($i = 1; $i <= $totalLate; $i++) {
                $numberOfDays++;
                if ($numberOfDays == $salaryDeduction->for_days) {
                    $dayOfSalaryDeduction += 1;
                    $numberOfDays = 0;
                }
            }

            $oneDaysSalary = $basic_salary / $totalWorkingDays;
            $totalLateAmount = $oneDaysSalary * $dayOfSalaryDeduction;
        }

        /**
         * @employee Salary Deduction For absence
         */

        if ($totalAbsence != 0 && $basic_salary != 0 && $totalWorkingDays != 0) {
            $perDaySalary = $basic_salary / $totalWorkingDays;
            $totalAbsenceAmount = $perDaySalary * $totalAbsence;
        }

        // $oneDaySalary = $basic_salary / $totalWorkingDays;
        $oneDaySalary = isset($basic_salary) ? $basic_salary / $totalWorkingDays : 0;
        // $oneDaySalary = isset($basic_salary) ? $basic_salary / $totalDays : 0;

        $overtime_rate = $oneDaySalary / 4;

        $overTime = $this->calculateEmployeeTotalOverTime($overTime, $overtime_rate);

        // $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        // $totalDays = findMonthToAllDate($month);

        $data = [
            'overtime_rate' => round($overtime_rate),
            'totalOverTimeHour' => $overTime['totalOverTimeHour'],
            'totalOvertimeAmount' => $overTime['overtimeAmount'],
            'totalPresent' => $totalPresent,
            'totalAbsence' => $totalAbsence,
            'totalAbsenceAmount' => round($totalAbsenceAmount),
            'totalLeave' => $totalLeave,
            'totalLate' => $totalLate,
            'dayOfSalaryDeduction' => $dayOfSalaryDeduction,
            'totalLateAmount' => round($totalLateAmount),
            'totalWorkingDays' => $totalWorkingDays,
            'oneDaysSalary' => $oneDaySalary,
        ];
        // dd($data);
        return $data;
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
        return ['totalOverTimeHour' => $hours, 'overtimeAmount' => round($overtimeAmount)];
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

    public function getEmployeeHourlySalary($employee_id, $month, $hourly_rate)
    {
        $getDate = $this->getMonthToStartDateAndEndDate($month);
        $queryResult = EmployeeAttendanceApprove::where('employee_id', $employee_id)->whereBetween('date', [$getDate['firstDate'], $getDate['lastDate']])->get()->toArray();

        $totalAmountOfSalary = 0;
        $hour = 0;
        $minutes = 0;
        foreach ($queryResult as $value) {
            if ($value['approve_working_hour'] == '00:00' || $value['approve_working_hour'] == '') {
                continue;
            }
            $value = explode(':', date('H:i', strtotime($value['approve_working_hour'])));
            $hour += $value[0];
            $minutes += $value[1];
            if ($minutes >= 60) {
                $minutes -= 60;
                $hour++;
            }
        }

        $totalTime = $hour . ':' . (($minutes < 10) ? '0' . $minutes : $minutes);
        $perMinuteAmount = $hourly_rate / 60;
        $minuteWiseAmount = $perMinuteAmount * (($minutes < 10) ? '0' . $minutes : $minutes);

        $totalAmountOfSalary = ($hour * $hourly_rate) + $minuteWiseAmount;

        $data = [
            'totalWorkingHour' => $totalTime,
            'totalSalary' => round($totalAmountOfSalary),
        ];
        return $data;
    }

    public function makeEmployeeAdvanceDetuctionDataFormat($data)
    {

        $advanceDetuctionData['employee_id'] = $data['employeeSalaryDetails'];

        $advanceDetuctionData['advance_amount'] = $data['advance_amount'];

        $advanceDetuctionData['date_of_advance_given'] = dateConvertFormtoDB($data['date_of_advance_given']);

        $advanceDetuctionData['deduction_amouth_per_month'] = $data['deduction_amouth_per_month'];

        $advanceDetuctionData['no_of_month_to_be_deducted'] = $data['no_of_month_to_be_deducted'];

        return $advanceDetuctionData;
    }

    public function calculateEmployeeAdvanceDeduction($employee_id)
    {
        $advanceDeductions = AdvanceDeduction::join('employee', 'employee.employee_id', '=', 'advance_deduction.employee_id')->where('advance_deduction.employee_id', $employee_id)->where('advance_deduction.status', 1)->select('advance_deduction.*', 'employee.first_name', 'employee.last_name')->get();
        $deductionArray = [];
        $totalDeduction = 0;
        foreach ($advanceDeductions as $key => $advanceDeduction) {
            $temp = [];
            $temp['advance_deduction_id'] = $advanceDeduction->deduction_id;
            $temp['employee_id'] = $advanceDeduction->employee_id;
            $temp['advance_amount'] = $advanceDeduction->advance_amount;
            $temp['date_of_advance_given'] = $advanceDeduction->date_of_advance_given;
            $temp['deduction_amouth_per_month'] = $advanceDeduction->deduction_amouth_per_month;
            $temp['no_of_month_to_be_deducted'] = $advanceDeduction->no_of_month_to_be_deducted;
            $temp['status'] = $advanceDeduction->status;
            $temp['first_name'] = $advanceDeduction->first_name;
            $temp['last_name'] = $advanceDeduction->last_name;

            $temp['date'] = $advanceDeduction->date_of_advance_given;
            $temp['format_date'] = new DateTime($temp['date']);
            $temp['advanced_year'] = $temp['format_date']->format('y');
            $temp['advanced_month'] = $temp['format_date']->format('m');
            $temp['current_year'] = Carbon::today('y')->format('y');
            $temp['current_month'] = Carbon::today('m')->format('m');
            // $temp['total_period']   = $temp['deduction_amouth_per_month'] + $temp['advanced_month'];

            $temp['total_period'] = Carbon::createFromFormat('Y-m-d', $temp['date_of_advance_given'])->addMonth($temp['no_of_month_to_be_deducted']);
            $current_date = Carbon::now()->submonth(1);
            $temp['remaining_period'] = $temp['total_period']->diffInMonths($current_date);

            if ($temp['remaining_period'] > 0 && $temp['status'] == 1) {
                $temp['amount_of_advance_deduction'] = $temp['deduction_amouth_per_month'] / $temp['remaining_period'];
                $temp['status'] = 'true';
            } else {
                $temp['amount_of_advance_deduction'] = 0;
                $temp['status'] = 'false';
            }
            $totalDeduction += $temp['amount_of_advance_deduction'];
            $deductionArray[$key] = $temp;
        }
        return ['deductionArray' => $deductionArray, 'totalDeduction' => $totalDeduction];
    }

    public function calculateEmployeeMonthlyDeduction($employee_id, $month)
    {
        $monthlyDeductions = EmployeeFoodAndTelephoneDeduction::join('employee', 'employee.employee_id', '=', 'employee_food_and_telephone_deductions.employee_id')
            ->where('employee_food_and_telephone_deductions.employee_id', $employee_id)->where('month_of_deduction', $month)
            ->where('employee_food_and_telephone_deductions.status', 1)
            ->select('employee_food_and_telephone_deductions.*', 'employee.first_name', 'employee.last_name')
            ->where('employee.status', UserStatus::$ACTIVE)->get();
        $foodDeductionRules = FoodAllowanceDeductionRule::first();
        $telephoneDeductionRules = TelephoneAllowanceDeductionRule::first();
        $monthlyDeductionArray = [];
        $totalMonthlyDeduction = 0;
        foreach ($monthlyDeductions as $key => $monthlyDeduction) {
            $temp = [];
            $temp['employee_food_and_telephone_deduction_id'] = $monthlyDeduction->employee_food_and_telephone_deduction_id;
            $temp['finger_print_id'] = $monthlyDeduction->finger_print_id;
            $temp['employee_id'] = $monthlyDeduction->employee_id;
            $temp['month_of_deduction'] = $monthlyDeduction->month_of_deduction;
            $temp['call_consumed_per_month'] = $monthlyDeduction->call_consumed_per_month;
            $temp['breakfast_count'] = $monthlyDeduction->breakfast_count;
            $temp['lunch_count'] = $monthlyDeduction->lunch_count;
            $temp['dinner_count'] = $monthlyDeduction->dinner_count;
            $temp['status'] = $monthlyDeduction->status;
            $temp['full_name'] = $monthlyDeduction->first_name . ' ' . $monthlyDeduction->last_name;
            $temp['call_cost'] = (int) $temp['call_consumed_per_month'] > (int) $telephoneDeductionRules->limit_per_month ? \round((int) $temp['call_consumed_per_month']) * (int) $telephoneDeductionRules->cost_per_call : 0;
            $temp['breakfast_cost'] = (int) $temp['breakfast_count'] * $foodDeductionRules->breakfast_cost;
            $temp['lunch_cost'] = (int) $temp['lunch_count'] * $foodDeductionRules->lunch_cost;
            $temp['dinner_cost'] = (int) $temp['dinner_count'] * $foodDeductionRules->dinner_cost;
            $temp['total_food_cost'] = (int) ($temp['breakfast_cost'] + $temp['lunch_cost'] + $temp['dinner_cost']);
            $temp['total_telephone_cost'] = (int) $temp['call_cost'];

            if ($temp['call_consumed_per_month'] != 0) {
                $temp['amount_of_monthly_deduction'] = ($temp['total_food_cost'] + $temp['call_cost']);
                $temp['type'] = 'foodAndTelephone';
            } else {
                $temp['amount_of_monthly_deduction'] = $temp['total_food_cost'];
                $temp['type'] = 'foodOnly';
            }

            $totalMonthlyDeduction += $temp['amount_of_monthly_deduction'];
            $monthlyDeductionArray = $temp;
        }
        return ['monthlyDeductionArray' => $monthlyDeductionArray, 'totalMonthlyDeduction' => $totalMonthlyDeduction];
    }

    public function getEmployeeMonthlySalaryDetails($monthField)
    {
        if ($monthField) {
            $data = \Carbon\Carbon::createFromFormat('Y-m', $monthField);
        } else {
            $data = date("Y-m");
        }
        //    $queryResults =  DB::select("call `SP_DailyAttendance`('".$data."')");
        $queryResults = DB::select("call `SP_SalaryDetails`('" . $data . "')");

        return $queryResults;
    }

    public function ifHoliday($govtHolidays, $date)
    {

        $govt_holidays = [];
        foreach ($govtHolidays as $holidays) {
            $start_date = $holidays->from_date;
            $end_date = $holidays->to_date;
            while (strtotime($start_date) <= strtotime($end_date)) {
                $govt_holidays[] = $start_date;
                $start_date = date("Y-m-d", strtotime("+1 day", strtotime($start_date)));
            }
        }

        foreach ($govt_holidays as $val) {
            if ($val == $date) {
                return true;
            }
        }

        $weeklyHolidays = DB::select(DB::raw('call SP_getWeeklyHoliday()'));
        $timestamp = strtotime($date);
        $dayName = date("l", $timestamp);
        foreach ($weeklyHolidays as $v) {
            if ($v->day_name == $dayName) {
                return true;
            }
        }

        return false;
    }

    public function makeSalaryDataFormat($data, $verify = false)
    {
        $payrollDataFormat = [];

        if ($verify) {
            $payrollDataFormat['fullName'] = trim($data['employeeAllInfo']->first_name . ' ' . $data['employeeAllInfo']->last_name);
            $payrollDataFormat['designation_name'] = $data['employeeAllInfo']->designation->designation_name;
            $payrollDataFormat['contractor_name'] = $data['employeeAllInfo']->branch->branch_name;
            $payrollDataFormat['department_name'] = $data['employeeAllInfo']->department->department_name;
            $payrollDataFormat['unit_name'] = $data['employeeAllInfo']->subdepartment->sub_department_name;
            $payrollDataFormat['cost_center_number'] = $data['employeeAllInfo']->costcenter->cost_center_number;
        }

        $employerDeduction = $data['employeeSalaryDetails']['employerESIC'] + $data['employeeSalaryDetails']['employerEPF'] + ($data['employeeSalaryDetails']['employeeLWF']);
        $totalEarnings = $data['employeeSalaryDetails']['earnLeaveAmount'] + $data['employeeSalaryDetails']['bonusEarning'] + $data['employeeSalaryDetails']['serviceCharge'];
        $salaryOH = $data['employeeSalaryDetails']['grossSalary'] + $totalEarnings + $employerDeduction;

        $payrollDataFormat['employee_pf_percentage'] = $data['payrollSetting']->employee_pf;
        $payrollDataFormat['employee_esic_percentage'] = $data['payrollSetting']->employee_esic;
        $payrollDataFormat['employer_pf_percentage'] = $data['payrollSetting']->employer_pf;
        $payrollDataFormat['employer_esic_percentage'] = $data['payrollSetting']->employer_esic;
        $payrollDataFormat['service_charge_percentage'] = $data['payrollSetting']->service_charge;
        $payrollDataFormat['bonus_percentage'] = $data['payrollSetting']->bonus;
        $payrollDataFormat['employee'] = $data['employeeAllInfo']->employee_id;
        $payrollDataFormat['finger_print_id'] = $data['employeeAllInfo']->finger_id;
        $payrollDataFormat['date'] = date('Y-m-d');
        $payrollDataFormat['fdate'] = $data['employeeSalaryDetails']['fdate'];
        $payrollDataFormat['tdate'] = $data['employeeSalaryDetails']['tdate'];
        $payrollDataFormat['month'] = date('m', strtotime($data['employeeSalaryDetails']['month']));
        $payrollDataFormat['year'] = date('Y', strtotime($data['employeeSalaryDetails']['month']));
        $payrollDataFormat['unit'] = $data['employeeAllInfo']->subdepartment->sub_department_id;
        $payrollDataFormat['tom'] = null;
        $payrollDataFormat['service_provider'] = null;
        $payrollDataFormat['department'] = $data['employeeAllInfo']->department->department_id;
        $payrollDataFormat['branch'] = $data['employeeAllInfo']->branch->branch_id;
        $payrollDataFormat['costcenter'] = $data['employeeAllInfo']->costcenter->cost_center_id;
        $payrollDataFormat['no_day_wages'] = $data['employeeSalaryDetails']['totalPresent'];
        $payrollDataFormat['ph'] = $data['employeeSalaryDetails']['paidHolidays'];
        $payrollDataFormat['company_holiday'] = $data['employeeSalaryDetails']['companyHolidays'];
        $payrollDataFormat['total_days'] = $data['employeeSalaryDetails']['totalWorkingDays'];
        $payrollDataFormat['per_day_basic_da'] = $data['employeeSalaryDetails']['perDayBasicDaEarned'];
        $payrollDataFormat['per_day_basic'] = $data['employeeSalaryDetails']['perDayBasicEarned'];
        $payrollDataFormat['per_day_da'] = $data['employeeSalaryDetails']['perDayDaEarned'];
        $payrollDataFormat['per_day_hra'] = $data['employeeSalaryDetails']['perDayHraEarned'];
        $payrollDataFormat['per_day_wages'] = $data['employeeAllInfo']['daily_wage'];
        $payrollDataFormat['basic_da_amount'] = $data['employeeSalaryDetails']['basicDaEarned'];
        $payrollDataFormat['basic_amount'] = $data['employeeSalaryDetails']['basicEarned'];
        $payrollDataFormat['da_amount'] = $data['employeeSalaryDetails']['daEarned'];
        $payrollDataFormat['hra_amount'] = $data['employeeSalaryDetails']['hraEarned'];
        $payrollDataFormat['wages_amount'] = $data['employeeSalaryDetails']['wagesEarned'];
        $payrollDataFormat['attendance_bonus'] = $data['employeeSalaryDetails']['attendanceBonus'];
        $payrollDataFormat['ot_hours'] = $data['employeeSalaryDetails']['totalOverTimeHour'];
        $payrollDataFormat['ot_per_hours'] = $data['employeeSalaryDetails']['overtime_rate'];
        $payrollDataFormat['ot_amount'] = $data['employeeSalaryDetails']['totalOvertimeAmount'];
        $payrollDataFormat['gross_salary'] = $data['employeeSalaryDetails']['grossSalary'];
        $payrollDataFormat['employee_pf'] = $data['employeeSalaryDetails']['employeeEPF'];
        $payrollDataFormat['employee_esic'] = $data['employeeSalaryDetails']['employeeESIC'];
        $payrollDataFormat['canteen'] = $data['employeeSalaryDetails']['canteenDeduction'];
        $payrollDataFormat['net_salary'] = $data['employeeSalaryDetails']['netSalary'];
        $payrollDataFormat['employer_pf'] = $data['employeeSalaryDetails']['employerEPF'];
        $payrollDataFormat['employer_esic'] = $data['employeeSalaryDetails']['employerESIC'];
        $payrollDataFormat['service_charge'] = $data['employeeSalaryDetails']['serviceCharge'];
        $payrollDataFormat['bonus_amount'] = $data['employeeSalaryDetails']['bonusEarning'];
        $payrollDataFormat['earned_leave_balance'] = $data['employeeSalaryDetails']['earnedLeaveBalance'];
        $payrollDataFormat['earned_leave'] = $data['employeeSalaryDetails']['earnLeaveDays'];
        $payrollDataFormat['leave_amount'] = $data['employeeSalaryDetails']['earnLeaveAmount'];
        $payrollDataFormat['manhours'] = $data['employeeSalaryDetails']['totalWorkingHour'];
        $payrollDataFormat['manhours_amount'] = null;
        $payrollDataFormat['manhour_days'] = $data['employeeSalaryDetails']['totalPresent'];
        $payrollDataFormat['salary'] = $salaryOH;
        $payrollDataFormat['lwf'] = $data['employeeSalaryDetails']['employeeLWF'];
        $payrollDataFormat['retained_bonus'] = $data['employeeSalaryDetails']['retainedBonusAmount'];
        $payrollDataFormat['retained_service_charge'] = $data['employeeSalaryDetails']['retainedServiceChargeAmount'];
        $payrollDataFormat['retained_attendance_bonus'] = $data['employeeSalaryDetails']['retainedAttendanceBonusAmount'];
        $payrollDataFormat['retained_leave_amount'] = $data['employeeSalaryDetails']['retainedLeaveAmount'];
        $payrollDataFormat['employee_total_deduction'] = $data['employeeSalaryDetails']['totalDeduction'];
        $payrollDataFormat['employer_total_deduction'] = $employerDeduction;
        $payrollDataFormat['other_allowance'] = $data['employeeSalaryDetails']['otherAllowance'];
        $payrollDataFormat['other_deduction'] = $data['employeeSalaryDetails']['otherDeduction'];
        $payrollDataFormat['status'] = 0;
        $payrollDataFormat['el_bonus'] = 0;
        $payrollDataFormat['created_at'] = Carbon::now();
        $payrollDataFormat['created_by'] = null;
        $payrollDataFormat['updated_at'] = Carbon::now();
        $payrollDataFormat['updated_by'] = null;

        // dd($payrollDataFormat);
        return $payrollDataFormat;
    }
}
