<?php

use Illuminate\Database\Migrations\Migration;

class CreateSPMonthlyOverTimeStoreProcedure extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('CREATE PROCEDURE SP_monthlyOverTime(
                    IN employeeId INT(10),
                    IN from_date DATE,
                    IN to_date DATE
        )
 BEGIN

select employee.employee_id,CONCAT(COALESCE(employee.first_name,\'\'),\' \',COALESCE(employee.last_name,\'\')) AS fullName,department_name,
                        view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        DATE_FORMAT(view_employee_in_out_data.in_time,\'%h:%i %p\') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,\'%h:%i %p\') AS out_time,
		
             TIMEDIFF((DATE_FORMAT(work_shift.`end_time`,\'%H:%i:%s\')),work_shift.`start_time`) AS workingHour
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
JOIN work_shift on work_shift.work_shift_id = employee.work_shift_id
                        where `status`=1
                       AND `date` between from_date and to_date and employee_id=employeeId
                        GROUP BY view_employee_in_out_data.date,view_employee_in_out_data.`finger_print_id`;



 END');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS SP_monthlyOverTime');
    }
}
