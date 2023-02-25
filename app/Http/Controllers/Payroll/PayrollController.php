<?php

namespace App\Http\Controllers\Payroll;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollRequest;
use App\Model\PayRoll;

class PayrollController extends Controller
{
    public function index()
    {
        $editModeData = PayRoll::first();
        return view('admin.payroll.payrollSettings.form', ['editModeData' => $editModeData]);
    }

    public function create()
    {
        $editModeData = PayRoll::first();
        return view('admin.payroll.payrollSettings.form', ['editModeData' => $editModeData]);
    }

    public function store(PayrollRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;
        // dd($input);
        try {
            PayRoll::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            dd($e);
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('payrollSettings')->with('success', 'PayRoll Configuration  Successfully saved.');
        } else {
            return redirect('payrollSettings')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function update(PayrollRequest $request, $id)
    {
        // dd($request->all());
        $data = PayRoll::FindOrFail($id);
        $input = $request->all();
        $input['updated_by'] = auth()->user()->user_id;
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
