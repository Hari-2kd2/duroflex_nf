<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollSettingRequest;
use App\Model\PayRollSetting;
use Carbon\Carbon;

class PayrollSettingController extends Controller
{
    public function index()
    {
        $editModeData = PayRollSetting::first();
        // return view('admin.payroll.payrollSettings.form', ['editModeData' => $editModeData]);
        if ($editModeData) {
            return view('admin.payroll.payrollSettings.edit', ['editModeData' => $editModeData]);
        } else {
            return view('admin.payroll.payrollSettings.add', ['editModeData' => $editModeData]);
        }
    }

    public function create()
    {
        $editModeData = PayRollSetting::first();
        return view('admin.payroll.payrollSettings.add', ['editModeData' => $editModeData]);
    }

    public function store(PayrollSettingRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;
        // dd($input);
        try {
            PayRollSetting::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('payrollSettings')->with('success', 'PayRoll Configuration  Successfully saved.');
        } else {
            return redirect('payrollSettings')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function update(PayrollSettingRequest $request, $id)
    {
        // dd($request->all());
        $data = PayRollSetting::FindOrFail($id);
        $input = $request->all();
        $input['updated_by'] = auth()->user()->user_id;
        $input['updated_at'] = Carbon::now();
        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'PayRoll Configuration  Successfully Updated.');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }
}
