<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\DailyWagePayGradeRequest;
use App\Model\Allowance;
use App\Model\DailyWagePayGrade;
use App\Model\Deduction;
use App\Model\Employee;
use App\Model\PayGrade;
use App\Model\PayGradeToAllowance;
use App\Model\PayGradeToDeduction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyWagePayGradeController extends Controller
{
    public function index()
    {
        $results = DailyWagePayGrade::get();
        return view('admin.payroll.dailyWagePayGrade.index', ['results' => $results]);
    }

    public function create()
    {
        $allowances = Allowance::all();
        $deductions = Deduction::all();
        return view('admin.payroll.dailyWagePayGrade.form', ['allowances' => $allowances, 'deductions' => $deductions]);
    }

    public function store(DailyWagePayGradeRequest $request)
    {
        $input = $request->all();
        $allowance = [];
        $deduction = [];
        try {
            DB::beginTransaction();

            $result = PayGrade::create($input);

            if (isset($input['allowance_id'])) {
                for ($i = 0; $i < count($input['allowance_id']); $i++) {
                    $allowance[$i] = [
                        'pay_grade_id' => $result->pay_grade_id,
                        'allowance_id' => $input['allowance_id'][$i],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                PayGradeToAllowance::insert($allowance);
            }

            if (isset($input['deduction_id'])) {
                for ($i = 0; $i < count($input['deduction_id']); $i++) {
                    $deduction[$i] = [
                        'pay_grade_id' => $result->pay_grade_id,
                        'deduction_id' => $input['deduction_id'][$i],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                PayGradeToDeduction::insert($deduction);
            }

            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            $bug = $e->errorInfo[1];
        }

        if ($bug == 0) {
            return redirect('dailyWagePayGrade')->with('success', 'Pay grade Successfully saved.');
        } else {
            return redirect('dailyWagePayGrade')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = DailyWagePayGrade::findOrFail($id);
        $sortedPayGradeWiseAllowanceData = PayGradeToAllowance::where('pay_grade_id', $id)->get()->toArray();
        $sortedPayGradeWiseDeductionData = PayGradeToDeduction::where('pay_grade_id', $id)->get()->toArray();
        $allowances = Allowance::all();
        $deductions = Deduction::all();

        return view('admin.payroll.dailyWagePayGrade.form')->with(compact('editModeData', 'allowances', 'deductions', 'sortedPayGradeWiseAllowanceData', 'sortedPayGradeWiseDeductionData'));
    }

    public function update(DailyWagePayGradeRequest $request, $id)
    {
        $data = DailyWagePayGrade::FindOrFail($id);
        $input = $request->all();
        $allowance = [];
        $deduction = [];

        try {
            DB::beginTransaction();

            DB::table('pay_grade_to_allowance')->where('pay_grade_id', $id)->delete();
            DB::table('pay_grade_to_deduction')->where('pay_grade_id', $id)->delete();
            $result = $data->update($input);

            if (isset($input['allowance_id'])) {
                for ($i = 0; $i < count($input['allowance_id']); $i++) {
                    $allowance[$i] = [
                        'pay_grade_id' => $id,
                        'allowance_id' => $input['allowance_id'][$i],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                PayGradeToAllowance::insert($allowance);
            }

            if (isset($input['deduction_id'])) {
                for ($i = 0; $i < count($input['deduction_id']); $i++) {
                    $deduction[$i] = [
                        'pay_grade_id' => $id,
                        'deduction_id' => $input['deduction_id'][$i],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
                PayGradeToDeduction::insert($deduction);
            }

            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            $bug = $e->errorInfo[1];
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Pay Grade Successfully Updated.');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {

        $count = Employee::where('daily_wage_pay_grade_id', '=', $id)->count();

        if ($count > 0) {

            return "hasForeignKey";
        }

        try {
            $data = DailyWagePayGrade::FindOrFail($id);
            $data->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = $e->errorInfo[1];
        }

        if ($bug == 0) {
            echo "success";
        } elseif ($bug == 1451) {
            echo 'hasForeignKey';
        } else {
            echo 'error';
        }
    }
}
