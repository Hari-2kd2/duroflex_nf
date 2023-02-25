<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\FileUploadRequest;
use App\Imports\EmployeeImport;
use App\Model\Employee;
use App\Repositories\EmployeeRepository;
use Maatwebsite\Excel\Facades\Excel as Excel;

class EmployeeImportController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepository $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function import(FileUploadRequest $request)
    {

        set_time_limit(0);

        try {
            $file = $request->file('select_file');
            Excel::import(new EmployeeImport($request->all()), $file);

            return back()->with('success', 'Employee information saved successfully.');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $import = new EmployeeImport();
            $import->import($file);

            foreach ($import->failures() as $failure) {
                $failure->row(); // row that went wrong
                $failure->attribute(); // either heading key (if using heading row concern) or column index
                $failure->errors(); // Actual error messages from Laravel validator
                $failure->values(); // The values of the row that has failed.
            }
        }

    }
}
