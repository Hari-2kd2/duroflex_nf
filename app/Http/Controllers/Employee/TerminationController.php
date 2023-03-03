<?php

namespace App\Http\Controllers\Employee;

use App\Exports\TerminationReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\TerminationRequest;
use App\Imports\TerminationImport;
use App\Model\Employee;
use App\Model\Termination;
use App\Repositories\CommonRepository;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TerminationController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function index(Request $request)
    {

        $results = Termination::with(['terminateTo', 'terminateBy'])->without('branch', 'department', 'designation', 'costcenter', 'subdepartment')->orderBy('termination_id', 'DESC')->get();

        return view('admin.employee.termination.index', ['results' => $results, 'terminate_by' => $request->terminate_by, 'termination_date' => $request->termination_date, 'notice_date' => $request->notice_date]);
    }

    public function create()
    {
        $employeeList = $this->commonRepository->employeeList();
        return view('admin.employee.termination.form', ['employeeList' => $employeeList]);
    }

    public function store(TerminationRequest $request)
    {
        $input = $request->all();
        $input['notice_date'] = dateConvertFormtoDB($request->notice_date);
        $input['termination_date'] = dateConvertFormtoDB($request->termination_date);
        $emp = Employee::find($input['terminate_to']);
        $input['finger_print_id'] = $emp->finger_id;

        try {
            $result = Termination::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('termination/' . $result->termination_id . '/edit')->with('success', 'Employee Performance Successfully saved.');
        } else {
            return redirect('termination')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = Termination::findOrFail($id);
        $employeeList = $this->commonRepository->employeeList();
        return view('admin.employee.termination.form', ['employeeList' => $employeeList, 'editModeData' => $editModeData]);
    }

    public function show($id)
    {
        $results = Termination::with(['terminateTo.department', 'terminateBy'])->where('termination_id', $id)->first();
        return view('admin.employee.termination.details', ['result' => $results]);
    }

    public function update(TerminationRequest $request, $id)
    {
        $data = Termination::findOrFail($id);
        $input = $request->all();
        $input['notice_date'] = dateConvertFormtoDB($request->notice_date);
        $input['termination_date'] = dateConvertFormtoDB($request->termination_date);

        if (isset($request->submit)) {
            $input['status'] = 2;
        }

        try {
            DB::beginTransaction();

            $data->update($input);
            if (isset($request->submit)) {
                $employee = Employee::where('employee_id', $request->terminate_to)->first();
                $employee->where('employee_id', $request->terminate_to)->update(['status' => 3]);
                User::where('user_id', $employee->user_id)->update(['status' => 3]);
            }

            DB::commit();
            $bug = 0;
        } catch (\Exception $e) {
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            if (isset($request->submit)) {
                return redirect('termination')->with('success', 'Employee termination successfully updated.');
            } else {
                return redirect()->back()->with('success', 'Employee termination successfully updated.');
            }
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {
        try {
            $data = Termination::FindOrFail($id);
            $data->delete();
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } elseif ($bug == 1451) {
            echo 'hasForeignKey';
        } else {
            echo 'error';
        }
    }

    public function terminationTemplate()
    {
        $file_name = 'templates/termination_template.xlsx';
        $file = Storage::disk('public')->get($file_name);
        return (new Response($file, 200))
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function terminationimport(Request $request)
    {
        try {

            $file = $request->file('termination');
            Excel::import(new TerminationImport($request->all()), $file);

            return back()->with('success', 'Termination information imported successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            $import = new TerminationImport();
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }
    }

    public function report(Request $request)
    {
        $dataset = [];

        // $qry = "1 ";

        // if ($request->terminate_by) {
        //     $qry .= " AND terminate_by=" . $request->terminate_by;
        // }

        // if ($request->termination_date) {
        //     $qry .= " AND termination_date=" . $request->termination_date;
        // }

        // $termination = Termination::whereRaw("(" . $qry . ")")->with('terminateTo', 'terminateBy')->orderBy('created_at', 'DESC')->get();
        $termination = Termination::with('terminateTo', 'terminateBy')->orderBy('created_at', 'DESC')->get();

        $inc = 1;

        foreach ($termination as $key => $Data) {

            $dataset[] = [
                $inc,
                DATE('d-m-Y', strtotime($Data->notice_date)),
                DATE('d-m-Y', strtotime($Data->termination_date)),
                $Data->terminateTo->finger_id,
                $Data->terminateBy->first_name . " " . $Data->terminateBy->last_name,
                $Data->subject,
                $Data->description,
                $Data->status == 1 ? 'Pending' : 'Terminated',
            ];

            $inc++;
        }

        $filename = 'TerminationReport-' . DATE('dmYHis') . '.xlsx';

        $heading = [
            [
                'Sr.No.',
                'Notice Date',
                'Termination Date',
                'Employee ID',
                'Terminated By',
                'Subject',
                'Description',
                'Status',
            ],
        ];
        $extraData['heading'] = $heading;
        //dd($dataset);
        return Excel::download(new TerminationReport($dataset, $extraData), $filename);
    }
}
