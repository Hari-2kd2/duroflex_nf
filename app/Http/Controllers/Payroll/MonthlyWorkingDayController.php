<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\MonthlyWorkingDayRequest;
use App\Model\MonthlyWorkingDay;

class MonthlyWorkingDayController extends Controller
{

    public function index()
    {
        $results = MonthlyWorkingDay::all();
        return view('admin.payroll.monthlyWorkingDay.index', ['results' => $results]);

    }

    public function create()
    {
        return view('admin.payroll.monthlyWorkingDay.add');
    }

    public function store(MonthlyWorkingDayRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;
        // dd($input);
        try {
            MonthlyWorkingDay::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            dd($e);
            $bug = $e->errorInfo[1];
        }

        if ($bug == 0) {
            return redirect('monthlyWorkingDay')->with('success', 'monthlyWorkingDay Successfully saved.');
        } else {
            return redirect('monthlyWorkingDay')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = MonthlyWorkingDay::FindOrFail($id);
        return view('admin.payroll.monthlyWorkingDay.edit', ['editModeData' => $editModeData]);
    }

    public function update(MonthlyWorkingDayRequest $request, $id)
    {
        // dd($request->all());
        $data = MonthlyWorkingDay::FindOrFail($id);
        $input = $request->all();
        $input['updated_by'] = auth()->user()->user_id;
        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = $e->errorInfo[1];
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'monthlyWorkingDay Successfully Updated.');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {

        try {
            $data = MonthlyWorkingDay::FindOrFail($id);
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
