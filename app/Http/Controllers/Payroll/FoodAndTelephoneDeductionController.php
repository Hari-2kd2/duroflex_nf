<?php

namespace App\Http\Controllers\Payroll;

use Carbon\Carbon;
use App\Model\Employee;
use App\Model\Department;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CanteenDeductionImport;
use Illuminate\Support\Facades\Storage;
use App\Model\FoodAllowanceDeductionRule;
use App\Model\TelephoneAllowanceDeductionRule;

class FoodAndTelephoneDeductionController extends Controller
{

    public function monthlyManualDeductions()
    {
        $departmentList = Department::get();
        return view('admin.payroll.monthlyManualDeduction.index', ['departmentList' => $departmentList]);
    }

    public function filterEmployeeData(Request $request)
    {
        $data           = monthConvertFormtoDB($request->get('month'));
        $department     = $request->get('department_id');
        $departmentList = Department::get();

        $deductionData = Employee::select('employee.finger_id', 'employee.employee_id', 'employee.department_id', 'employee.pay_grade_id',
            DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) as fullName'),

            DB::raw('(SELECT employee_food_and_telephone_deductions.call_consumed_per_month  FROM employee_food_and_telephone_deductions
            WHERE employee_food_and_telephone_deductions.month_of_deduction = "' . $data . '" AND employee_food_and_telephone_deductions.finger_print_id = employee.finger_id ) AS call_consumed_per_month'),

            DB::raw('(SELECT employee_food_and_telephone_deductions.breakfast_count  FROM employee_food_and_telephone_deductions
            WHERE employee_food_and_telephone_deductions.month_of_deduction = "' . $data . '" AND employee_food_and_telephone_deductions.finger_print_id = employee.finger_id ) AS breakfast_count'),

            DB::raw('(SELECT employee_food_and_telephone_deductions.lunch_count  FROM employee_food_and_telephone_deductions
            WHERE employee_food_and_telephone_deductions.month_of_deduction = "' . $data . '" AND employee_food_and_telephone_deductions.finger_print_id = employee.finger_id ) AS lunch_count'),

            DB::raw('(SELECT employee_food_and_telephone_deductions.dinner_count  FROM employee_food_and_telephone_deductions
            WHERE employee_food_and_telephone_deductions.month_of_deduction = "' . $data . '" AND employee_food_and_telephone_deductions.finger_print_id = employee.finger_id ) AS dinner_count'),

        )
            ->leftJoin('employee_food_and_telephone_deductions', 'employee_food_and_telephone_deductions.finger_print_id', '=', 'employee.finger_id')
            ->where('employee.department_id', $department)
            ->groupBy('employee.finger_id')
            ->where('employee.status', 1)

            ->get();

        $telephoneDeductionRule = TelephoneAllowanceDeductionRule::select('limit_per_month')->first();
        $foodDeductionRule = FoodAllowanceDeductionRule::first();

        // $deductionData = Employee::select('employee.finger_id', 'employee.department_id', 'employee_food_and_telephone_deductions.call_consumed_per_month', 'employee_food_and_telephone_deductions.month_of_deduction',
        //     'employee_food_and_telephone_deductions.breakfast_count', 'employee_food_and_telephone_deductions.lunch_count', 'employee_food_and_telephone_deductions.dinner_count',
        //     DB::raw('CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) as fullName'), )->leftJoin('employee_food_and_telephone_deductions', 'employee_food_and_telephone_deductions.finger_print_id', '=', 'employee.finger_id')
        //     ->where('employee.department_id', $department)
        //     ->where('employee.status', 1)
        //     ->get();

        return view('admin.payroll.monthlyManualDeduction.index', ['departmentList' => $departmentList, 'deductionData' => $deductionData, 'telephoneDeductionRule' => $telephoneDeductionRule]);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        try {
            DB::beginTransaction();
            $data       = monthConvertFormtoDB($request->get('month'));
            $department = $request->get('department_id');

            $result = json_decode(DB::table(DB::raw("(SELECT employee_food_and_telephone_deductions.*,employee.`department_id`,  DATE_FORMAT(`employee_food_and_telephone_deductions`.`month_of_deduction`,'y-m') AS `month` FROM `employee_food_and_telephone_deductions`
                    INNER JOIN `employee` ON `employee`.`finger_id` = employee_food_and_telephone_deductions.`finger_print_id`
                    WHERE department_id = $department) as monthlyDeduction"))
                    ->select('monthlyDeduction.employee_food_and_telephone_deduction_id')
                    ->where('monthlyDeduction.month_of_deduction', $data)
                    ->get()->toJson(), true);
            // dd($result);
            DB::table('employee_food_and_telephone_deductions')->whereIn('employee_food_and_telephone_deduction_id', array_values($result))->delete();

            foreach ($request->finger_print_id as $index => $finger_print_id) {
                if (isset($request->month) && isset($request->employee_id[$index]) && isset($request->breakfast_count[$index]) &&
                    isset($request->lunch_count[$index]) && isset($request->dinner_count[$index])
                    && isset($request->call_consumed_per_month[$index])) {
                    $inputData = [
                        'finger_print_id'         => $finger_print_id,
                        'employee_id'             => $request->employee_id[$index],
                        'month_of_deduction'      => monthConvertFormtoDB($request->month),
                        'breakfast_count'         => $request->breakfast_count[$index],
                        'lunch_count'             => $request->lunch_count[$index],
                        'dinner_count'            => $request->dinner_count[$index],
                        'call_consumed_per_month' => $request->call_consumed_per_month[$index],
                        'created_at'              => Carbon::now(),
                        'updated_at'              => Carbon::now(),
                    ];

                    DB::table('employee_food_and_telephone_deductions')->insert([$inputData]);

                }
                // \dump($inputData);
            }

            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            // $bug = $e->errorInfo[1];
            return redirect('monthlyDeduction')->with('error', $e->getMessage());

        }

        if ($bug == 0) {
            return redirect('monthlyDeduction')->with('success', 'Monthly Deductions successfully saved.');
        } else {
            return redirect('monthlyDeduction')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function monthlyDeductionTemplate()
    {
        $file_name = 'templates/canteendeduction_template.xlsx';
        $file      = Storage::disk('public')->get($file_name);
        return (new Response($file, 200))
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function monthlyDeductionImport(Request $request)
    {
        try {
            // $file = $request->file('termination');
            $file = $request->file('canteendeduction')->getRealPath();
            // $ext = $request->file('termination')->getClientOriginalExtension();
            Excel::import(new CanteenDeductionImport($request->all()), $file);

            // $path = $request->file('select_file')->getRealPath();
            // $excel =  Excel::import(new EmployeeImport($request->all()), $path);
            // return back()->with('success', 'User Imported Successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $import = new CanteenDeductionImport();
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }
        return back()->with('success', 'Canteen deduction information imported successfully.');
    }
}
