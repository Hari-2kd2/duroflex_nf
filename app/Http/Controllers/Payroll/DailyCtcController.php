<?php

namespace App\Http\Controllers\Payroll;

use App\Model\DailyCostToCompany;
use App\Model\MonthlyWorkingDay;
use App\Model\Employee;
use App\Model\PayRollSetting;
use App\Model\EmployeeInOutData;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Lib\Enumerations\ServiceCharge;
use App\Lib\Enumerations\OvertimeStatus;
use App\Lib\Enumerations\PayrollConstant;

class DailyCtcController extends Controller
{

    private $salaryController;
    public function __construct(SalaryController $salaryController)
    {
        $this->salaryController = $salaryController;
    }


    public function calculate_ctc($date)
    {
        set_time_limit(0);
        ini_set('memmory_limit', '2048M');

        $contract = false;
        $month = date('Y-m', strtotime($date));
        $employeeAllInfo = Employee::without('branch', 'department', 'designation', 'costcenter', 'subdepartment')->where('status', UserStatus::$ACTIVE)->get();
        $payrollSetting = PayRollSetting::first();
        $allEmployees = Employee::without('branch', 'department', 'designation', 'costcenter', 'subdepartment')->orderBy('finger_id', 'ASC')->pluck('finger_id')->toArray();
        $workingDays = MonthlyWorkingDay::where('year', date('Y', strtotime($date)))->first();
        $monthName = date('F', strtotime($month));
        $monthName = strtolower(substr($monthName, 0, 3));
        $workingDays = $workingDays->$monthName;
        $permenantEmployeesArray = DB::table('permanent_employees')->get();
        $permenantEmployees = [];
        $permenantEmployeesWithCTC = [];
        $totalContractEmployeePresent = 0;
        $totalPermenantEmployeePresent = 0;
        $totalAbsent = 0;
        $employees = [];

        foreach ($employeeAllInfo as $key => $value) {
            $employees[$value->finger_id] = $value;
        }

        foreach ($permenantEmployeesArray as $key => $value) {
            array_push($permenantEmployees, $value->finger_id);
            $permenantEmployeesWithCTC[$value->finger_id] = $value->ctc;
        }

        $contractEmployeeCTC = 0.00;
        $permenantEmployeeCTC = 0.00;

        foreach ($allEmployees as $key => $finger_id) {

            $singleEmployeeInfo = $employees[$finger_id];

            if (in_array($singleEmployeeInfo->finger_id, array_values($permenantEmployees))) {

                $CTC = $permenantEmployeesWithCTC[$singleEmployeeInfo->finger_id];
                $permenantEmployeeCTC += $CTC;

                if ($CTC != 0) {
                    $totalPermenantEmployeePresent += 1;
                } else {
                    $totalAbsent += 1;
                }

            }else{

                $CTC = $this->getEmployeeCtcDetail($date, $month, $singleEmployeeInfo, $payrollSetting, $singleEmployeeInfo->finger_id, $contract, $workingDays);
                $contractEmployeeCTC += $CTC;

                if ($CTC != 0) {
                    $totalContractEmployeePresent += 1;
                } else {
                    $totalAbsent += 1;
                }
            }

        }


        try {

            $dailyCTC = DailyCostToCompany::where('date', $date)->first();

            $rawData = [
                'date' => $date,
                'contractor' => $totalContractEmployeePresent,
                'staff' => $totalPermenantEmployeePresent,
                'employee' => count($allEmployees),
                'present' => ($totalContractEmployeePresent + $totalPermenantEmployeePresent),
                'absent' => $totalAbsent,
                'contractor_ctc' => number_format((float) $contractEmployeeCTC, 2, '.', ''),
                'staff_ctc' => number_format((float) $permenantEmployeeCTC, 2, '.', ''),
                'total_ctc' => number_format((float) $contractEmployeeCTC + $permenantEmployeeCTC, 2, '.', '')
            ];

            if (!$dailyCTC) {
                DailyCostToCompany::create($rawData);
            } else {
                $dailyCTC->update($rawData);
            }

            info('daily ctc calculated');
            return true;

        } catch (\Throwable $th) {
            info('daily ctc error ' . $th->getMessage());
        }

    }

    public function getEmployeeCtcDetail($date, $month, $employeeAllInfo, $payrollSetting, $finger_id, $contract, $workingDays)
    {

        try {

            $dayValue = 0.0;
            $overTimeArray = [];
            $workingTimeArray = [];
            $monthName = date('F', strtotime($month));
            $monthName = strtolower(substr($monthName, 0, 3));

            $employeeInOutData = EmployeeInOutData::without('employee', 'updatedBy', 'createdBy', 'workShift')->where('finger_print_id', $finger_id)->whereDate('date', $date)->get();

            foreach ($employeeInOutData as $key => $inOutData) {

                if (isset($inOutData->mandays) && $inOutData->mandays != null) {
                    if ($contract) {
                        (float) $dayValue += (float) $inOutData->mandays;
                    } else {
                        (float) $dayValue += 1.00;
                    }

                }

                if ($inOutData->over_time != null && $inOutData->over_time != PayrollConstant::$EMPTY_TIME && $inOutData->over_time_status == OvertimeStatus::$OT_FOUND_AND_APPROVED) {
                    $overTimeArray[] = $inOutData->over_time;
                }

                if ($inOutData->working_time != null) {
                    $workingTimeArray[] = $inOutData->working_time;
                }
            }

            $overtime_rate = (float) ($employeeAllInfo->daily_wage / 8.00) * $payrollSetting->ot_per_hour;
            $overTime = $this->salaryController->calculateEmployeeTotalOverTime($overTimeArray, $overtime_rate);
            $workingHour = $this->salaryController->calculateEmployeeTotalWorkingHour($workingTimeArray);
            $allowances = $this->calculateEmployeeAllowance($employeeAllInfo, $payrollSetting, $overTime, $month, $workingDays);
            $deductions = $this->calculateEmployeeDeduction($allowances['perDaybasicDaEarned'], $month, $payrollSetting, $employeeAllInfo->finger_id, $allowances['grossSalary'], $employeeAllInfo);

            $data = [
                'overtime_rate' => number_format((float) $overtime_rate, 2, '.', ''),
                'totalOverTimeHour' => $overTime['totalOverTimeHour'],
                'totalOvertimeAmount' => number_format((float) $overTime['overtimeAmount'], 2, '.', ''),
                'totalWorkingHour' => $workingHour['totalWorkingHour'],
            ];

            $dataSet = array_merge($data, $allowances, $deductions, ['status' => true]);

            $CTC = $dataSet['dailyWage'] + $dataSet['employerEPF'] + $dataSet['employerESIC'] + $dataSet['serviceCharge'] + $dataSet['bonusEarning'] + $dataSet['earnLeaveAmount'];
            return $CTC * $dayValue;

        } catch (\Throwable $th) {
            return ['status' => false, 'message' => $th];
        }
    }

    public function calculateEmployeeAllowance($employeeAllInfo, $payrollSetting, $overTime, $month, $workingDays)
    {
        $perDayBasicEarned = 0.00;
        $perDayDaEarned = 0.00;
        $perDayHraEarned = 0.00;
        $bonusEarning = 0.00;
        $overTimeAmount = 0.00;
        $otherAllowance = 0.00;
        $earnLeaveAmount = 0.00;
        $earnLeaveDays = 0.00;

        $dailyWage = number_format((float) $employeeAllInfo->daily_wage, 2, '.', '');
        $perDayBasicEarned = number_format((float) $employeeAllInfo->basic_amt, 2, '.', '');
        $perDayDaEarned = number_format((float) $employeeAllInfo->da_amt, 2, '.', '');
        $perDayHraEarned = $employeeAllInfo->hra_amt;
        $otherAllowance = number_format((float) $payrollSetting->other_allowance, 2, '.', '');
        $wagesEarned = number_format((float) $perDayBasicEarned + $perDayDaEarned + $perDayHraEarned, 2, '.', '');
        $overTimeAmount = number_format((float) $overTime['overtimeAmount'], 2, '.', '');
        $earnLeaveDays = number_format((float) $employeeAllInfo->leave_balance, 2, '.', '');
        $perDaybasicDaEarned = number_format((float) $perDayBasicEarned + $perDayDaEarned, 2, '.', '');
        $earnLeaveAmount = ($earnLeaveDays * $perDaybasicDaEarned) / $workingDays;
        $earnLeaveAmount = number_format((float) $earnLeaveAmount, 2, '.', '');
        $bonusActual = number_format((float) 7000 * ($payrollSetting->bonus / 100), 2, '.', '');
        $bonusDefault = number_format((float) $perDaybasicDaEarned * ($payrollSetting->bonus / 100), 2, '.', '');
        $bonusEarning = $perDaybasicDaEarned > 7000 ? $bonusActual : $bonusDefault;
        $grossSalary = number_format((float) $perDaybasicDaEarned + $perDayHraEarned + $overTimeAmount + $otherAllowance, 2, '.', '');

        $allowances = [
            'dailyWage' => $dailyWage,
            'perDayBasicEarned' => $perDayBasicEarned,
            'perDayDaEarned' => $perDayDaEarned,
            'perDayHraEarned' => $perDayHraEarned,
            'perDaybasicDaEarned' => $perDaybasicDaEarned,
            'wagesEarned' => $wagesEarned,
            'earnLeaveDays' => $earnLeaveDays,
            'earnLeaveAmount' => $earnLeaveAmount,
            'bonusEarning' => $bonusEarning,
            'otherAllowance' => $otherAllowance,
            'grossSalary' => $grossSalary,
        ];

        if ($employeeAllInfo->service_charge == ServiceCharge::$ENABLED) {
            $allowances['serviceCharge'] = number_format((float) $allowances['grossSalary'] * ($payrollSetting->service_charge / 100), 2, '.', '');
        } else {
            $allowances['serviceCharge'] = 0.00;
        }

        $allowances['totalBonusElServiceCharge'] = number_format((float) ($earnLeaveAmount + $bonusEarning + $allowances['serviceCharge']), 2, '.', '');

        return $allowances;
    }

    public function calculateEmployeeDeduction($basicDaAllowance, $month, $payrollSetting, $finger_id, $grossSalary, $employeeAllInfo)
    {
        $employerEPF = 0.00;
        $employerESIC = 0.00;
        $otherDeduction = 0.00;

        $employerEPF = number_format((float) $basicDaAllowance * ($payrollSetting->employer_pf / 100), 2, '.', '');

        if ($grossSalary < 21000) {
            $employerESIC = number_format((float) $employeeAllInfo->daily_wage * ($payrollSetting->employer_esic / 100), 2, '.', '');
        }

        $otherDeduction = number_format((float) $payrollSetting->other_deduction, 2, '.', '');

        $deductions = [
            'employerEPF' => $employerEPF,
            'employerESIC' => $employerESIC,
            'otherDeduction' => $otherDeduction,
        ];

        return $deductions;
    }


}