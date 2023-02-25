<?php

namespace App\Http\Controllers\Payroll;

use App\Exports\SettlementReport;
use App\Http\Controllers\Controller;
use App\Http\Requests\SettlementRequest;
use App\Lib\Enumerations\SettlementStatus;
use App\Model\Branch;
use App\Model\CostCenter;
use App\Model\Department;
use App\Model\Employee;
use App\Model\PayRoll;
use App\Model\PayRollSetting;
use App\Model\Settlement;
use App\Model\SubDepartment;
use App\Repositories\AttendanceRepository;
use App\Repositories\CommonRepository;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class SettlementController extends Controller
{
    public $commonRepository;
    protected $attendanceRepository;

    public function __construct(CommonRepository $commonRepository, AttendanceRepository $attendanceRepository)
    {
        $this->commonRepository = $commonRepository;
        $this->attendanceRepository = $attendanceRepository;
    }

    public function index(Request $request)
    {
        \set_time_limit(0);
        // dd($request->all());
        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $employeeList = Employee::get();
        $payrollSetting = PayRollSetting::first();
        $results = [];

        return view('admin.payroll.settlement.index', compact('branchList', 'departmentList', 'branch_id', 'department_id', 'employeeList', 'payrollSetting'));
    }

    public function details(Request $request)
    {

        $qry = "1 ";
        if ($request->employee_id) {
            $qry .= " AND employee_id=" . $request->employee_id;
        }

        if ($request->branch_id) {
            $qry .= " AND branch_id=" . $request->branch_id;
        }

        if ($request->department_id) {
            $qry .= " AND department_id=" . $request->department_id;
        }

        $i = 0;

        $data = Settlement::whereIn('status', [SettlementStatus::$CREATE, SettlementStatus::$UPDATE]);

        return DataTables::of($data)
        /*->addColumn('action', function ($data) {
        return
        '<a href="' . route('settlement.form', ['id' => $data->payroll_id,'employee_id' => $data->employee]) . '" class="btn btn-xs btn-success" title="Settlement" target="_blank" data-id="' . $data->payroll_id . '"><i style="color: #fff" class="fa fa-hand-paper-o"></i></a>';
        })*/
            ->addColumn('serviceCharge', function ($data) {
                $serviceCharge = $data->retained_service_charge;
                return $serviceCharge;
            })
            ->editColumn('employee', function ($data) {
                return $data->employeeinfo->first_name . " " . $data->employeeinfo->last_name;
            })
        /*->editColumn('month', function ($data) {
        $payroll=Payroll::where('el_bonus',$data->elb_id)->get();
        $set=[];
        foreach($payroll as $key => $Data){
        $set[]=DATE("M-Y",strtotime("01-".$Data->month."-".$Data->year));
        }
        return implode(",",$set);
        })*/
            ->editColumn('paid_at', function ($data) {
                return DATE('d-m-Y', strtotime($data->paid_at));
            })
            ->addColumn('total_amount', function ($data) {
                $earnLeave = $data->retained_leave_amount;
                $bonus = $data->retained_bonus;
                $serviceCharge = $data->retained_service_charge;
                $totalAmount = number_format((float) $earnLeave + $bonus, '2', '.', ''); //$serviceCharge
                return $totalAmount;
            })
        //->rawColumns(['action'])
            ->addColumn('sl.no', function ($data) use ($i) {
                $i++;
                return $i;
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function pending(Request $request)
    {
        \set_time_limit(0);
        // dd($request->all());
        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $employeeList = Employee::get();
        $payrollSetting = PayRollSetting::first();
        $results = [];

        return view('admin.payroll.settlement.pending', compact('branchList', 'departmentList', 'branch_id', 'department_id', 'employeeList', 'payrollSetting'));
    }

    public function pendingdetails(Request $request)
    {

        $qry = "1 ";
        if ($request->employee_id) {
            $qry .= " AND employee_id=" . $request->employee_id;
        }

        if ($request->branch_id) {
            $qry .= " AND branch_id=" . $request->branch_id;
        }

        if ($request->department_id) {
            $qry .= " AND department_id=" . $request->department_id;
        }

        $i = 0;

        //$data = Employee::where('status', '=', UserStatus::$ACTIVE)->with('department', 'branch', 'designation', 'subdepartment', 'costcenter', 'payroll')->whereHas('payroll'); // ->whereRaw("(" . $qry . ")")

        $data = PayRoll::whereIn('status', [SettlementStatus::$CREATE, SettlementStatus::$UPDATE])->where('el_bonus', 0)->groupBy('employee');

        return DataTables::of($data)
            ->addColumn('action', function ($data) {
                return
                '<a href="' . route('settlementInfo.form', ['id' => $data->payroll_id, 'employee_id' => $data->employee]) . '" class="btn btn-xs btn-success" title="Settlement" target="_blank" data-id="' . $data->payroll_id . '"><i style="color: #fff" class="fa fa-hand-paper-o"></i></a>';
            })
            ->editColumn('employee', function ($data) {
                return $data->employeeinfo->first_name . " " . $data->employeeinfo->last_name;
            })
            ->addColumn('total_amount', function ($data) {
                $totalAmount = round(($data->bonus_amount + $data->leave_amount + $data->service_charge), 2);
                return $totalAmount;
            })
            ->rawColumns(['action'])
            ->addColumn('sl.no', function ($data) use ($i) {
                $i++;
                return $i;
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function form(Request $request)
    {

        $payroll = Payroll::where('employee', $request->employee_id)->where('el_bonus', 0)->get();
        $data = Employee::find($request->employee_id);
        return view('admin.payroll.settlement.form', compact('data', 'payroll'));
    }

    public function store(SettlementRequest $request)
    {

        $employee = Employee::where('employee_id', $request->employee)->first();

        $settlement = new Settlement;
        $settlement->employee = $request->employee;
        $settlement->finger_print_id = $employee->finger_id;
        $settlement->amount = $request->amount;
        $settlement->pay_status = 1;
        $settlement->deduction_amount = $request->deduction_amount;
        $settlement->net_amount = $request->net_amount;
        $settlement->paid_at = DATE('Y-m-d', strtotime($request->paid_at));
        $settlement->paid_on = DATE('Y-m-d', strtotime($request->paid_at)) . DATE('H:i:s');
        $settlement->remarks = $request->remarks;
        $settlement->department = $employee->department_id;
        $settlement->branch = $employee->branch_id;
        $settlement->costcenter = $employee->cost_center_id;
        $settlement->unit = 0;
        $settlement->month = DATE('m', strtotime($request->paid_at));
        $settlement->year = DATE('Y', strtotime($request->paid_at));
        $settlement->save();

        Payroll::where('employee', $request->employee)->where('el_bonus', 0)->update(['el_bonus' => $settlement->elb_id]);

        return redirect(route('settlementInfo.index'))->with('Settlement saved successfully!');
    }

    public function report(Request $request)
    {
        \set_time_limit(0);
        // $dataProvider = new EloquentDataProvider(Payroll::query());

        $departmentList = Department::get();
        $branchList = Branch::get();
        $date = $request->date;
        $branch_id = $request->branch_id;
        $department_id = $request->department_id;
        $attendance_status = $request->attendance_status;
        $employeeList = Employee::get();

        return \view('admin.payroll.settlement.report', compact('branchList', 'departmentList', 'date', 'branch_id', 'department_id', 'attendance_status', 'employeeList'));
    }

    public function reportdetails(Request $request)
    {

        $qry = "1 ";
        if ($request->employee) {
            $qry .= " AND employee=" . $request->employee;
        }

        if ($request->branch) {
            $qry .= " AND branch=" . $request->branch;
        }

        if ($request->department) {
            $qry .= " AND department=" . $request->department;
        }

        if ($request->date) {
            $qry .= " AND month=" . date('m', strtotime($request->date));
            $qry .= " AND year=" . date('Y', strtotime($request->date));
        }

        $i = 0;

        $data = Settlement::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->orderBy('created_at', 'DESC');

        return DataTables::of($data)
        /*->addColumn('action', function ($data) {
        return
        '<a href="' . route('settlementInfo.form', ['id' => $data->payroll_id,'employee_id' => $data->employee]) . '" class="btn btn-xs btn-success" title="Settlement" target="_blank" data-id="' . $data->payroll_id . '"><i style="color: #fff" class="fa fa-hand-paper-o"></i></a>';
        })*/
            ->addColumn('serviceCharge', function ($data) {
                $serviceCharge = $data->retained_service_charge;
                return $serviceCharge;
            })
            ->editColumn('employee', function ($data) {
                return $data->employeeinfo->first_name . " " . $data->employeeinfo->last_name;
            })
        /*->editColumn('month', function ($data) {
        $payroll=Payroll::where('el_bonus',$data->elb_id)->get();
        $set=[];
        foreach($payroll as $key => $Data){
        $set[]=DATE("M-Y",strtotime("01-".$Data->month."-".$Data->year));
        }
        return implode(",",$set);
        })*/
            ->editColumn('paid_at', function ($data) {
                return DATE('d-m-Y', strtotime($data->paid_at));
            })
            ->addColumn('branch', function ($data) {
                $branch = Branch::where('branch_id', $data->branch)->first();
                if ($branch) {
                    return $branch->branch_name;
                } else {
                    return 'NA';
                }
            })
            ->addColumn('subunit', function ($data) {
                $subunit = SubDepartment::where('sub_department_id', $data->unit)->first();
                if ($subunit) {
                    return $subunit->sub_department_name;
                } else {
                    return 'NA';
                }
            })
            ->addColumn('costcenter', function ($data) {
                $costcenter = CostCenter::where('cost_center_id', $data->costcenter)->first();
                if ($costcenter) {
                    return $costcenter->cost_center_number;
                } else {
                    return 'NA';
                }
            })
            ->addColumn('department', function ($data) {
                $department = Department::where('department_id', $data->department)->first();
                if ($department) {
                    return $department->department_name;
                }
            })
            ->addColumn('total_amount', function ($data) {
                $earnLeave = $data->retained_leave_amount;
                $bonus = $data->retained_bonus;
                $serviceCharge = $data->retained_service_charge;
                $totalAmount = number_format((float) $earnLeave + $bonus, '2', '.', ''); //$serviceCharge
                return $totalAmount;
            })
        //->rawColumns(['action'])
            ->addColumn('sl.no', function ($data) use ($i) {
                $i++;
                return $i;
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function download(Request $request)
    {
        $dataset = [];

        $branchName = '';

        $qry = "1 ";
        if ($request->employee) {
            $qry .= " AND employee=" . $request->employee;
        }

        if ($request->branch) {
            $qry .= " AND branch=" . $request->branch;
        }

        if ($request->department) {
            $qry .= " AND department=" . $request->department;
        }

        if ($request->date) {
            $qry .= " AND month=" . date('m', strtotime($request->date));
            $qry .= " AND year=" . date('Y', strtotime($request->date));
        }

        $settlement = Settlement::where('status', '!=', 2)->whereRaw("(" . $qry . ")")->orderBy('created_at', 'DESC')->get();
        // dd($payroll);

        $inc = 1;
        foreach ($settlement as $key => $Data) {

            $branchName = $request->branch ? $Data->branchInfo->branch_name : 'ALL';

            $dataset[] = [
                $inc,
                DATE('d-m-Y', strtotime($Data->paid_at)),
                $Data->employeeinfo->finger_id,
                $Data->employeeinfo->first_name . " " . $Data->employeeinfo->last_name,
                $Data->branchInfo->branch_name,
                $Data->departmentinfo->department_name,
                $Data->amount,
                $Data->deduction_amount,
                $Data->net_amount,
                $Data->remarks,
            ];

            $inc++;
        }

        $filename = 'settlement-report-' . DATE('d-m-Y-h-i-A') . '.xlsx';
        $date = $request->date . "-01";
        $extraData = ['subtitle2' => 'Name of the Contractor - ' . $branchName, 'subtitle3' => 'Settlement Report Month&Year - ' . DATE('F/Y', strtotime($date)) . ' '];

        $heading = [
            ['Duroflex Pvt. Ltd.'],
            [$extraData['subtitle2']],
            [$extraData['subtitle3']],
            [
                'Sr.No.',
                'Date',
                'Employee ID',
                'Employee Name',
                'Contractor Name',
                'Department',
                'Amount',
                'Deduction Amount',
                'Net Amount',
                'Remarks',
            ],
        ];

        $extraData['heading'] = $heading;
        //dd($dataset);
        return Excel::download(new SettlementReport($dataset, $extraData), $filename);
    }
}
