<?php

namespace App\Imports;

use App\Lib\Enumerations\ServiceCharge;
use App\Lib\Enumerations\UserStatus;
use App\Model\Branch;
use App\Model\CostCenter;
use App\Model\Department;
use App\Model\Designation;
use App\Model\Employee;
use App\Model\Role;
use App\Model\SubDepartment;
use App\Model\WorkShift;
use App\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithLimit;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class EmployeeImport implements ToModel, WithValidation, WithStartRow, WithLimit
{
    use Importable;

    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    public function sanitize()
    {
        $this->data['*.21'] = trim($this->data['*.21']);
        dd($this->data);
    }

    public function rules(): array
    {
        return [
            '*.0' => 'required',
            '*.1' => 'required|regex:/^\S*$/u',
            // '*.1' => 'required|unique:user,user_name|regex:/^\S*$/u',
            '*.2' => 'required|exists:role,role_name',
            '*.3' => 'required',
            // '*.3' => 'required|unique:employee,finger_id',
            '*.4' => 'required|exists:department,department_name',
            '*.5' => 'required|exists:sub_departments,sub_department_name',
            '*.6' => 'required|exists:cost_centers,cost_center_number',
            '*.7' => 'required|exists:designation,designation_name',
            '*.8' => 'required|exists:branch,branch_name',
            '*.9' => 'nullable|exists:user,user_name',
            '*.10' => 'nullable|exists:work_shift,shift_name',
            '*.11' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            '*.12' => 'nullable|email',
            '*.13' => 'nullable|email',
            '*.14' => 'required',
            '*.15' => 'nullable',
            '*.16' => 'required',
            '*.17' => 'required',
            '*.18' => 'required',
            // '*.17' => 'required|date_format:Y-m-d',
            // '*.18' => 'required|date_format:Y-m-d',
            '*.19' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = ['Male', 'Female', 'NoDisclosure'];
                if (!in_array($value, $arr)) {
                    $onFailure('Gender is invalid, it should be Male/Female/NoDisclosure');
                }
            },
            //'required|in:Male,Female,NoDisclosure',
            '*.20' => 'nullable',
            '*.21' => function ($attribute, $value, $onFailure) {
                $value = trim($value);
                $arr = [null, 'Married', 'Unmarried', 'NoDisclosure'];
                if (!in_array($value, $arr)) {
                    $onFailure('Martial Status is invalid, it should be Married/Unmarried/NoDisclosure');
                }
            }, //'nullable|in:Married,Unmarried,NoDisclosure',
            '*.22' => 'nullable',
            '*.23' => 'nullable',
            '*.24' => 'required',
            // '*.24' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
            '*.25' => 'required',
            // '*.25' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
            '*.26' => 'required',
            '*.27' => 'required',
            '*.28' => 'required',
            '*.29' => 'required',
            '*.30' => 'required',
            // '*.30' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
            '*.31' => 'required',
            // '*.31' => 'required|regex:/^([a-zA-Z]){5}([0-9]){4}([a-zA-Z]){1}?$/',
            '*.32' => 'required',
            '*.33' => 'required',
            '*.34' => 'required|numeric',
            '*.35' => 'required|numeric',
            '*.36' => 'required|numeric',
            '*.37' => 'required|numeric',
            '*.38' => 'nullable|in:Yes,No',
            '*.39' => 'required|in:Enabled,Disabled',
            // '*.32' => 'required|regex:/(^([a-zA-z]+)(\d+)?$)/u',

        ];
    }

    public function customValidationMessages()
    {
        return [
            '0.required' => 'Sr.No is required',
            '1.required' => 'User name is required',
            '2.required' => 'Role name should be same as the name provided in Master',
            '3.required' => 'Employee Id is required (ie: Device Unique id) ',
            '4.required' => 'Department Name should be same as the name provided in Master',
            '5.required' => 'Sub Unit Name should be same as the name provided in Master',
            '6.required' => 'Cost Center Number should be same as the name provided in Master',
            '7.required' => 'Designation Name should be same as the name provided in Master',
            '8.required' => 'Contractor Name should be same as the name provided in Master',
            // '9.required' => 'Supervisor Name should be same as the  user name provided in Master',
            // '10.required' => 'Workshift Name should be same as the name provided in Master',
            '11.required' => 'Phone No is required',
            // '11.numeric' => 'Phone No should be numeric',
            '11.min' => 'Phone No should be min 10 digits',
            '11.regex' => 'Phone No is invalid',
            // '12.required' => 'Personal email id is required',
            // '13.required' => 'Official email id is required',
            '14.required' => 'Employee first name is required',
            // '15.required' => 'Employee last name is required',
            '16.required' => 'Father Name is required',
            '17.required' => 'Date of birth is required',
            '18.required' => 'Date of joining is required',
            '19.in' => 'Invalid Gender ,can user only Male/Female/NoDisclosure ',
            // '20.required' => 'Religion is required',
            '21.in' => 'Invalid Marital status ,can user only use Married/Unmarried/NoDisclosure',
            // '21.required' => 'Marital status is required',
            // '22.required' => 'Address is required',
            // '23.required' => 'Emergence Contact is required',

            '24.required' => 'ESIC NO is required',
            '25.required' => 'UAN NO is required',
            '26.required' => 'Bank Name is required',
            '27.required' => 'Branch Name  is required',
            '28.required' => 'Bank Account No is required',
            '29.required' => 'City name (ie: Bank landmark) is required',
            '30.required' => 'IFSC NO is required',
            '31.required' => 'PAN NO is required',
            '32.required' => 'Aadhar No is required',
            '33.required' => 'Leave Balance is required',
            '34.required' => 'Daily Wage is required',
            '35.required' => 'Basic Value is required',
            '36.required' => 'DA Value is required',
            '37.required' => 'HRA Value is required',
            '34.numeric' => 'Daily Wage should be numeric',
            '35.numeric' => 'Basic Value should be numeric',
            '36.numeric' => 'DA Value should be numeric',
            '37.numeric' => 'HRA Value should be numeric',
            // '38.required' => 'Status is required',
            '38.in' => 'Invalid status ,can user only use Yes/No',
            '39.in' => 'Invalid Service charge ,can user only use Enabled/Disabled',

            '1.unique' => 'Username should be unique',
            '1.regex' => 'Space not allowed in Username',
            '2.exists' => 'Role name doest not exists',
            '3.unique' => 'Employee Id should be unique',
            '4.exists' => 'Department name doest not exists',
            '5.exists' => 'Sub. Unit name doest not exists',
            '6.exists' => 'Cost Center doest not exists',
            '7.exists' => 'Designation name doest not exists',
            '8.exists' => 'Contractor name doest not exists',
            '9.exists' => 'Supervisor user name doest not exists',
            '10.exists' => 'Workshift name doest not exists',
            // '17.date_format' => 'Date format should be Y-m-d',
            // '18.date_format' => 'Date format should be Y-m-d',
        ];
    }

    public function model(array $row)
    {

        // info($row);

        $dataUpdate = false;
        $dataInsert = false;
        $usr_status = UserStatus::$ACTIVE;

        $checkEmployee = Employee::where('finger_id', $row[3])->first();

        if ($checkEmployee) {
            $checkUser = User::where('user_id', $checkEmployee->user_id)->first();
            $dataUpdate = true;
        } else {
            $dataInsert = true;
        }

        $dob = "0000-00-00";
        $doj = "0000-00-00";
        $password = '';

        if ($row[17]) {
            try {
                $dob = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[17])->format('Y-m-d');
                $password = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[17])->format('Ymd');
            } catch (\Throwable $th) {
                $dob = date('Y-m-d', strtotime($row[17]));
                $password = date('Ymd', strtotime($row[17]));
            }
        }

        // $password =  \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[14])->format('Ymd');
        if ($row[18]) {
            try {
                $doj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[18])->format('Y-m-d');
            } catch (\Throwable $th) {
                $doj = date('Y-m-d', strtotime($row[18]));
            }
        }

        if ($row[39]) {
            $serviceCharge = $row[39] == 'Enabled' ? ServiceCharge::$ENABLED : ServiceCharge::$DISABLED;
        }

        //copy('foo/test.php', 'bar/test.php');

        $role = Role::where('role_name', $row[2])->first();
        $dept = Department::where('department_name', $row[4])->first();
        $subdept = SubDepartment::where('sub_department_name', $row[5])->first();
        $costcenter = CostCenter::where('cost_center_number', $row[6])->first();
        $designation = Designation::where('designation_name', $row[7])->first();

        if (isset($row[9]) && isset($row[10])) {
            $user = User::where('user_name', $row[9])->first();
            $emp = Employee::where('user_id', $user->user_id)->first();
            $shift = WorkShift::where('shift_name', $row[10])->first();
        }

        $branch = Branch::where('branch_name', $row[8])->first();

        // info($user);
        // info($dept);
        // info($subdept);
        // info($costcenter);
        // info($designation);
        // info($emp);
        // info($branch);
        // info($shift);

        if ($dataInsert) {

            $userData = User::create([
                'user_name' => $row[1],
                'role_id' => $role->role_id,
                'password' => Hash::make($password),
                'status' => UserStatus::$ACTIVE,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);

            $employeeData = Employee::create([
                'user_id' => $userData->user_id,
                'finger_id' => $row[3],
                'department_id' => $dept->department_id,
                'sub_department_id' => $subdept->sub_department_id,
                'cost_center_id' => $costcenter->cost_center_id,
                'designation_id' => $designation->designation_id,
                'branch_id' => $branch->branch_id,
                'supervisor_id' => isset($emp->employee_id) ? $emp->employee_id : null,
                'work_shift_id' => isset($shift->work_shift_id) ? $shift->work_shift_id : null,
                'phone' => $row[11],
                'email' => $row[12],
                'official_email' => $row[13],
                'first_name' => $row[14],
                'last_name' => $row[15],
                'father_name' => $row[16],
                'date_of_birth' => $dob,
                'date_of_joining' => $doj,
                'gender' => $row[19],
                'religion' => $row[20],
                'marital_status' => $row[21],
                'address' => $row[22],
                'emergency_contacts' => $row[23],
                'esi_card_number' => $row[24],
                'pf_account_number' => $row[25],
                'bank_name' => $row[26],
                'bank_branch' => $row[27],
                'bank_account_no' => $row[28],
                'bank_of_the_city' => $row[29],
                'ifsc_no' => $row[30],
                'pan_no' => $row[31],
                'aadhar_no' => $row[32],
                'leave_balance' => $row[33],
                'daily_wage' => $row[34],
                'basic_amt' => $row[35],
                'da_amt' => $row[36],
                'hra_amt' => $row[37],
                'service_charge' => $serviceCharge,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
        }

        if ($row[38] == 'No') {
            $usr_status = UserStatus::$INACTIVE;
        }

        if ($dataUpdate) {

            $userData = User::where('user_id', $checkUser->user_id)->update([
                'user_name' => $row[1],
                'role_id' => $role->role_id,
                // 'password' => Hash::make($password),
                'status' => UserStatus::$ACTIVE,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);

            $employeeData = Employee::where('employee_id', $checkEmployee->employee_id)->update([
                'user_id' => $checkUser->user_id,
                'finger_id' => $row[3],
                'department_id' => $dept->department_id,
                'sub_department_id' => $subdept->sub_department_id,
                'cost_center_id' => $costcenter->cost_center_id,
                'designation_id' => $designation->designation_id,
                'branch_id' => $branch->branch_id,
                'supervisor_id' => isset($emp->employee_id) ? $emp->employee_id : null,
                'work_shift_id' => isset($shift->work_shift_id) ? $shift->work_shift_id : null,
                'phone' => $row[11],
                'email' => $row[12],
                'official_email' => $row[13],
                'first_name' => $row[14],
                'last_name' => $row[15],
                'father_name' => $row[16],
                'date_of_birth' => $dob,
                'date_of_joining' => $doj,
                'gender' => $row[19],
                'religion' => $row[20],
                'marital_status' => $row[21],
                'address' => $row[22],
                'emergency_contacts' => $row[23],
                'esi_card_number' => $row[24],
                'pf_account_number' => $row[25],
                'bank_name' => $row[26],
                'bank_branch' => $row[27],
                'bank_account_no' => $row[28],
                'bank_of_the_city' => $row[29],
                'ifsc_no' => $row[30],
                'pan_no' => $row[31],
                'aadhar_no' => $row[32],
                // 'leave_balance' => $row[33],
                'daily_wage' => $row[34],
                'basic_amt' => $row[35],
                'da_amt' => $row[36],
                'hra_amt' => $row[37],
                'service_charge' => $serviceCharge,
                'status' => $usr_status,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
        }
    }

    public function startRow(): int
    {
        return 2;
    }

    public function limit(): int
    {
        return 200;
    }
}
