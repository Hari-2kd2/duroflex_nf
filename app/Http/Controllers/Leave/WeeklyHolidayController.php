<?php

namespace App\Http\Controllers\Leave;

use App\Model\Employee;

use App\Model\WeeklyHoliday;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;

use App\Repositories\CommonRepository;
use App\Http\Requests\WeeklyHolidayRequest;
use Carbon\Carbon;

class WeeklyHolidayController extends Controller
{

    protected $commonRepository;

    public function __construct(CommonRepository $commonRepository)
    {
        $this->commonRepository = $commonRepository;
    }


    public function index()
    {
        $results = WeeklyHoliday::groupBy('employee_id', 'day_name', 'month')->orderBy('updated_at', 'DESC')->get();
        return view('admin.leave.weeklyHoliday.index', ['results' => $results]);
    }


    public function create(Request $request)
    {
        $count = Employee::where('status', UserStatus::$ACTIVE)->count();
        $departmentList = $this->commonRepository->departmentList();
        $weekList = $this->commonRepository->weekList();
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)
            // ->orderBy('weekoff_updated_at', 'ASC')
            ->where('weekoff_updated_at', '=', \null)
            ->groupBy('department_id', 'finger_id')
            ->paginate($count);


        if (request()->ajax()) {
            if ($request->department_id != '') {
                $employeeList = Employee::where('status', UserStatus::$ACTIVE)
                    ->where('department_id', $request->department_id)
                    ->orderBy('weekoff_updated_at', 'ASC')
                    // ->where('weekoff_updated_at', '=', \null)
                    ->groupBy('department_id', 'finger_id')
                    ->paginate($count);
            }

            if ($request->month != '') {
                $employeeList = Employee::where('status', UserStatus::$ACTIVE)
                    // ->orderBy('weekoff_updated_at', 'ASC')
                   // ->where('weekoff_updated_at', '!=', date('Y-m'))
                    ->groupBy('department_id', 'finger_id')
                    ->paginate($count);
            }


            return View('admin.leave.weeklyHoliday.pagination', ['employeeList' => $employeeList])->render();
        }

        // return $employeeList;

        return view('admin.leave.weeklyHoliday.form', [
            'day_name' => $request->day_name, 'status' => $request->status, 'employeeList' => $employeeList, 'weekList' => $weekList,
            'department_id' => $request->department_id, 'departmentList' => $departmentList, 'month' => $request->month
        ]);
    }

    public function store(WeeklyHolidayRequest $request)
    {

        // dd($request->all());
        $month = $request->month;
        $day_name = $request->day_name;
        $dateList = '';
        $dayKey = '';
        $week_days = [];
        if (isset($month) && isset($day_name)) {
            $week = \weekedName();
            foreach ($week as $dayKey => $weekLi) {
                if ($weekLi === $day_name) {
                    $dayKey =  $dayKey;
                }
            }

            $dateList = findMonthToAllDate($month);
            foreach ($dateList as $key => $dateLi) {
                if ($dateLi['day_name'] === $dayKey) {
                    $week_days[] .=  $dateLi['date'];
                }
            }
            // return($week_days);
            // $dateList =  weekOffDateList($day_name, $month);
        }

        $input = $request->all();
        $employees = $input['employee_id'];
        $input['weekoff_days'] = \json_encode($week_days);
        $input['status'] = 1;
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;
        unset($input['_token']);
        unset($input['employee_id']);
        unset($input['department_id']);
        $bool = 'false';
        // return $input;
        try {

            DB::beginTransaction();
            foreach ($employees as $key => $value) {
                $input['employee_id'] = $value;
                $if_exists = WeeklyHoliday::where('month', $input['month'])->where('employee_id', $input['employee_id'])->first();
                if (!$if_exists) {
                    // dd($input);
                    WeeklyHoliday::create($input);
                } else {
                    $if_exists->update($input);
                }
                Employee::where('employee_id', $value)->update(['weekoff_updated_at' => date('Y-m-d H:i:s', \strtotime($request->month))]);
            }
            $bug = 0;
            DB::commit();
        } catch (\Exception $e) {
            $bug = 1;
            $bug = $e->getMessage();
            return redirect('weeklyHoliday')->with('error', $e->getMessage());
        }

        if ($bug == 0) {
            return redirect('weeklyHoliday')->with('success', 'Weekly holiday successfully saved.');
        } else {
            return redirect('weeklyHoliday')->with('error', 'Something Error Found !, Please try again. ' . $bug);
        }
    }


    public function edit($id)
    {
        $count = Employee::where('status', UserStatus::$ACTIVE)->count();
        $employeeList = Employee::where('employee.status', UserStatus::$ACTIVE)
            ->join('weekly_holiday', 'weekly_holiday.employee_id', 'employee.employee_id')
            ->where('weekly_holiday.week_holiday_id', $id)
            ->orderBy('employee.weekoff_updated_at', 'ASC')
            ->paginate($count);

        $weekList       = $this->commonRepository->weekList();
        $editModeData   = WeeklyHoliday::findOrFail($id);
        $departmentList = $this->commonRepository->departmentList();

        return view('admin.leave.weeklyHoliday.form', ['editModeData' => $editModeData, 'employeeList' => $employeeList, 'departmentList' => $departmentList, 'weekList' => $weekList]);
    }


    public function update(WeeklyHolidayRequest $request, $id)
    {
        $month = $request->month;
        $day_name = $request->day_name;
        $dateList = '';

        if (isset($month) && isset($day_name)) {
            $dateList =  weekOffDateList($day_name, $month);
        }

        $input = $request->all();
        $employees = $input['employee_id'];
        $input['weekoff_days'] = \json_encode($dateList);
        $input['status'] = 1;
        $input['created_by'] = auth()->user()->user_id;
        $input['updated_by'] = auth()->user()->user_id;

        unset($input['_token']);
        unset($input['employee_id']);
        unset($input['department_id']);
        try {

            DB::beginTransaction();
            $data   = WeeklyHoliday::findOrFail($id);
            foreach ($employees as $key => $value) {
                $input['employee_id'] = $value;
                $data->update($input);
                Employee::where('employee_id', $value)->update(['weekoff_updated_at' => Carbon::now()]);
            }
            $bug = 0;
            DB::commit();
        } catch (\Exception $e) {
            $bug = 1;
            $bug = $e->getMessage();
            return redirect('weeklyHoliday')->with('error', $e->getMessage());
        }
        try {
            $data->update($input);
            $bug = 0;
        } catch (\Exception $e) {
            $bug = 1;
        }

        if ($bug == 0) {
            return redirect('weeklyHoliday')->with('success', 'Weekly holiday successfully updated.');
        } else {
            return redirect('weeklyHoliday')->with('error', 'Something Error Found !, Please try again.');
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $holiday = WeeklyHoliday::findOrFail($id);
            $data = WeeklyHoliday::findOrFail($id)->delete();
            $bug = 0;

            if ($data) {
                Employee::where('employee_id', $holiday->employee_id)->update(['weekoff_updated_at' => \null]);
                $bug = 0;
            } else {
                $bug = 2;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $bug = 1;
        }

        if ($bug == 0) {
            echo "success";
        } elseif ($bug == 1) {
            echo 'hasForeignKey';
        } else {
            DB::rollback();
            echo 'error';
        }
    }
}
