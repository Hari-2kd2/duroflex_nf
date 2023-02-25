<?php

namespace App\Http\Controllers\Employee;

use App\Model\SubDepartment;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\SubDepartmentRequest;
use App\Model\Department;
use App\Repositories\CommonRepository;

class SubDepartmentController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }

    public function index()
    {
        // $results = Department::with('subdepartment')->get();
        $results = SubDepartment::join('department', 'sub_departments.department_id', 'department.department_id')->get();
        // dd($results);
        return view('admin.employee.subdepartment.index', ['results' => $results]);
    }


    public function create()
    {
        $departmentList = $this->commonRepository->departmentList();
        return view('admin.employee.subdepartment.form', ['departmentList' => $departmentList]);
    }


    public function store(SubDepartmentRequest $request)
    {
        // dd($request->all());

        $input = $request->all();

        try {
            $subdepartment = SubDepartment::create($input);


            $pushStatus =  DB::table('sync_to_live')->first();

            if ($pushStatus->status == 1) {
                //Push to LIVE

                $form_data = $request->all();
                $form_data['sub_department_id'] = $subdepartment->sub_department_id;
                unset($form_data['_method']);
                unset($form_data['_token']);

                $data_set = [];
                foreach ($form_data as $key => $value) {
                    if ($value)
                        $data_set[$key] = $value;
                    else
                        $data_set[$key] = '';
                }

                $client   = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('POST', Common::liveurl() . "addSubDepartment", [
                    'form_params' => $data_set
                ]);



                // PUSH TO LIVE END
            }

            $bug = 0;
        } catch (\Exception $e) {
            // dd($e->getMessage());
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('sub_department')->with('success', 'Department successfully saved.');
        } else {
            return redirect('sub_department')->with('error', 'Something Error Found !, Please try again.');
        }
    }


    public function edit($id)
    {
        $editModeData = SubDepartment::findOrFail($id);
        $departmentList = $this->commonRepository->departmentList();

        return view('admin.employee.subdepartment.form', ['editModeData' => $editModeData, 'departmentList' => $departmentList]);
    }


    public function update(SubDepartmentRequest $request, $id)
    {
        $department = SubDepartment::findOrFail($id);
        $input = $request->all();
        try {
            $department->update($input);

            $pushStatus =  DB::table('sync_to_live')->first();

            if ($pushStatus->status == 1) {
                //Push to LIVE

                $form_data = $request->all();
                $form_data['department_id'] = $department->department_id;
                unset($form_data['_method']);
                unset($form_data['_token']);

                $data_set = [];
                foreach ($form_data as $key => $value) {
                    if ($value)
                        $data_set[$key] = $value;
                    else
                        $data_set[$key] = '';
                }

                $client   = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('POST', Common::liveurl() . "editDepartment", [
                    'form_params' => $data_set

                ]);

                // PUSH TO LIVE END
            }

            $bug = 0;
        } catch (\Exception $e) {
            // dd($e);
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Department successfully updated ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }


    public function destroy($id)
    {

        $count = Employee::where('sub_department_id', '=', $id)->count();

        if ($count > 0) {
            return  'hasForeignKey';
        }


        try {
            $department = SubDepartment::FindOrFail($id);
            $department->delete();
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
}
