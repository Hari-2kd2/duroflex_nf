<?php

namespace App\Http\Controllers\Employee;

use App\Model\SubDepartment;
use App\Model\Employee;
use App\Components\Common;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\CostCenterRequest;
use App\Http\Requests\SubDepartmentRequest;
use App\Model\CostCenter;
use App\Model\Department;
use App\Repositories\CommonRepository;
use Illuminate\Http\Request;

class CostCenterController extends Controller
{
    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }
    public function index()
    {
        $results = CostCenter::join('sub_departments', 'sub_departments.sub_department_id', 'cost_centers.sub_department_id')->get();
        // $results = SubDepartment::leftjoin('department', 'sub_departments.department_id', 'department.department_id')
        // ->leftjoin('cost_centers', 'cost_centers.sub_department_id', 'sub_departments.sub_department_id')
        // ->get();
        // dd($results);

        return view('admin.employee.costcenter.index', ['results' => $results]);
    }

    public function create()
    {
        $subDepartmentList = $this->commonRepository->subDepartmentList();
        return view('admin.employee.costcenter.form', ['subDepartmentList' => $subDepartmentList]);
    }

    public function store(CostCenterRequest $request)
    {
        // dd($request->all()); 

        $input = $request->all();

        try {

            $costcenter = CostCenter::create($input);
            $bug = 0;
        } catch (\Exception $e) {

            // dd($e->getMessage());
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('costcenter')->with('success', 'Costcenter successfully saved.');
        } else {
            return redirect('costcenter')->with('error', 'Something Error Found !, Please try again.');
        }
    }


    public function edit($id)
    {
        $editModeData =  CostCenter::findOrFail($id);
        $subDepartmentList = $this->commonRepository->subDepartmentList();

        return view('admin.employee.costcenter.form', ['subDepartmentList' => $subDepartmentList, 'editModeData' => $editModeData]);
    }


    public function update(CostCenterRequest $request)
    {
        $input = $request->all();
        $cost_centers =  CostCenter::findOrFail($request->cost_center_id);

        try {
            $cost_centers->update($input);

            $bug = 0;
        } catch (\Exception $e) {
            // dd($e);
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect()->back()->with('success', 'Costcenter successfully updated ');
        } else {
            return redirect()->back()->with('error', 'Something Error Found !, Please try again.');
        }
    }


    public function destroy($id)
    {

        $count = Employee::where('employee.cost_center_id', '=', $id)->count();

        if ($count > 0) {
            return  'hasForeignKey';
        }

        try {
            $cost_centers = CostCenter::FindOrFail($id);
            $cost_centers->delete();

            $bug = 0;
        } catch (\Exception $e) {
            // dd($e);
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
