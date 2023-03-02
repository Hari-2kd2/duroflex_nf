<?php

namespace App\Http\Controllers\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyHolidayRequest;
use App\Http\Requests\FileUploadRequest;
use App\Imports\CompanyHolidayImport;
use App\Model\CompanyHoliday;
use App\Repositories\CommonRepository;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CompanyHolidayController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {
        $results = CompanyHoliday::with('employee', 'created_user', 'updated_user')->orderByDesc('company_holiday_id')->get();

        return view('admin.leave.companyHoliday.index', ['results' => $results]);
    }

    public function create()
    {
        $employeeList = $this->commonRepository->employeeListWithId();

        return view('admin.leave.companyHoliday.form', ['employeeList' => $employeeList]);
    }

    public function store(CompanyHolidayRequest $request)
    {

        $input = $request->all();
        $input['fdate'] = Carbon::createFromFormat('d/m/Y', $input['fdate'])->format('Y-m-d');
        $input['tdate'] = Carbon::createFromFormat('d/m/Y', $input['tdate'])->format('Y-m-d');
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;
        // dd($input);

        try {
            CompanyHoliday::create($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('companyHoliday')->with('success', 'Holiday successfully saved.');
        } else {
            return redirect('companyHoliday')->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function edit($id)
    {
        $editModeData = CompanyHoliday::findOrFail($id);
        $editModeData->fdate = date('d/m/Y', strtotime($editModeData->fdate));
        $editModeData->tdate = date('d/m/Y', strtotime($editModeData->tdate));
        $employeeList = $this->commonRepository->employeeListWithId();

        return view('admin.leave.companyHoliday.form', ['editModeData' => $editModeData, 'employeeList' => $employeeList]);
    }

    public function update(CompanyHolidayRequest $request, $id)
    {
        $holiday = CompanyHoliday::findOrFail($id);

        $input = $request->all();
        $input['fdate'] = Carbon::createFromFormat('d/m/Y', $input['fdate'])->format('Y-m-d');
        $input['tdate'] = Carbon::createFromFormat('d/m/Y', $input['tdate'])->format('Y-m-d');
        $input['updated_by'] = auth()->user()->user_id;

        try {
            $holiday->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Company Holiday successfully updated. ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }

    public function destroy($id)
    {

        try {
            $holiday = CompanyHoliday::findOrFail($id);
            $holiday->delete();
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

    public function template()
    {
        $file_name = 'templates/company_holiday.xlsx';
        $file = Storage::disk('public')->get($file_name);
        return (new Response($file, 200))
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function import(FileUploadRequest $request)
    {
        try {
            $file = $request->file('select_file');
            Excel::import(new CompanyHolidayImport(), $file);

            // $path = $request->file('select_file')->getRealPath();
            // $excel =  Excel::import(new EmployeeImport($request->all()), $path);
            // return back()->with('success', 'User Imported Successfully.');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            $import = new CompanyHolidayImport();
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }

        return back()->with('success', 'Holiday information saved successfully.');
    }
}
