<?php

namespace App\Http\Controllers\Leave;

use App\Exports\EarnedLeaveReport;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\EarnedLeave;
use App\Model\Employee;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class EarnedLeaveReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->month;
        $employee_id = $request->employee_id;
        $employeeList = Employee::where('status', UserStatus::$ACTIVE)->get();

        return \view('admin.leave.earnedLeave.index', compact('month', 'employee_id', 'employeeList'));
    }

    public function report(Request $request)
    {

        $qry = "1 ";

        if ($request->employee_id) {
            $qry .= " AND employee_id=" . $request->employee_id;
        }

        if ($request->month) {
            $qry .= " AND month=" . date('m', strtotime($request->month));
            $qry .= " AND year=" . date('Y', strtotime($request->month));
        }

        $i = 0;

        $data = EarnedLeave::whereRaw("(" . $qry . ")")->with('employeeinfo')->orderBy('updated_at', 'DESC');

        return DataTables::of($data)

            ->addColumn('employee_name', function ($data) {
                return $data->employeeinfo->first_name . " " . $data->employeeinfo->last_name;
            })
            ->addColumn('finger_print_id', function ($data) use ($request) {
                return $data->employeeinfo->finger_id;
            })
            ->editColumn('month', function ($data) {
                $month = "01-" . $data->month . "-" . $data->year;
                return DATE('M-Y', strtotime($month));
            })
            ->addColumn('sl_no', function ($data) use ($i) {
                $i++;
                return $i;
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function download(Request $request)
    {
        $dataset = [];

        $qry = "1 ";

        if ($request->employee_id) {
            $qry .= " AND employee_id=" . $request->employee_id;
        }

        if ($request->month) {
            $qry .= " AND month=" . date('m', strtotime($request->month));
            $qry .= " AND year=" . date('Y', strtotime($request->month));
        }

        $earnedLeave = EarnedLeave::whereRaw("(" . $qry . ")")->orderBy('created_at', 'DESC')->with('employeeinfo')->get();

        $inc = 1;

        foreach ($earnedLeave as $Data) {

            $dataset[] = [
                $inc,
                DATE('M-Y', strtotime($Data->year . '-' . $Data->month . '-01')),
                $Data->employeeinfo->finger_id,
                $Data->employeeinfo->first_name . " " . $Data->employeeinfo->last_name,
                $Data->el_balance,
                $Data->el,
            ];

            $inc++;
        }

        $filename = 'EarnedLEaveReport-' . DATE('dmYHis') . '.xlsx';
        $heading = [
            ['DUROFLEX Pvt. Ltd.'],
            [
                'Sl.NO.',
                'MONTH',
                'EMPLOYEE ID',
                'NAME OF THE EMPLOYEE',
                'ACCUMULATED EL',
                'TOTAL EL',
            ],
        ];

        $extraData['heading'] = $heading;
        // dd($extraData);
        return Excel::download(new EarnedLeaveReport($dataset, $extraData), $filename);
    }
}
