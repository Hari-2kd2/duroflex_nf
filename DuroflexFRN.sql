-- Adminer 4.8.1 MySQL 8.0.32-0ubuntu0.22.04.2 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DELIMITER ;;

DROP PROCEDURE IF EXISTS `SP_calculateEmployeeLeaveBalance`;;
CREATE PROCEDURE `SP_calculateEmployeeLeaveBalance`(IN `employeeId` int(10), IN `leaveTypeId` int(10))
BEGIN  
          SELECT SUM(number_of_day) AS totalNumberOfDays FROM leave_application WHERE employee_id=employeeId AND leave_type_id=leaveTypeId and status = 2
          AND (approve_date  BETWEEN DATE_FORMAT(NOW(),'%Y-01-01') AND DATE_FORMAT(NOW(),'%Y-12-31')) COLLATE utf8mb4_unicode_ci;
         END;;

DROP PROCEDURE IF EXISTS `SP_DailyAttendance`;;
CREATE PROCEDURE `SP_DailyAttendance`(IN `input_date` DATE)
BEGIN 
 
select employee.employee_id,employee_attendance.uri,employee.photo,CONCAT(COALESCE(employee.first_name,''),' ',COALESCE(employee.last_name,'')) AS fullName,department_name,
                        view_employee_in_out_data.employee_attendance_id,view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        DATE_FORMAT(view_employee_in_out_data.in_time,'%h:%i %p') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,'%h:%i %p') AS out_time, 
		TIME_FORMAT( work_shift.late_count_time, '%H:%i:%s' ) as lateCountTime,
	(SELECT CASE WHEN DATE_FORMAT(MIN(view_employee_in_out_data.in_time),'%H:%i:00')  > lateCountTime
            THEN 'Yes' 
            ELSE 'No' END) AS  ifLate,
 
            (SELECT CASE WHEN TIMEDIFF((DATE_FORMAT(MIN(view_employee_in_out_data.in_time),'%H:%i:%s')),work_shift.late_count_time)  > '0'
            THEN TIMEDIFF((DATE_FORMAT(MIN(view_employee_in_out_data.in_time),'%H:%i:%s')),work_shift.late_count_time) 
            ELSE '00:00:00' END) AS  totalLateTime,
             TIMEDIFF((DATE_FORMAT(work_shift.`end_time`,'%H:%i:%s')),work_shift.`start_time`) AS workingHour
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
                        JOIN work_shift on work_shift.work_shift_id = employee.work_shift_id
                        JOIN employee_attendance on employee_attendance.employee_id= employee.employee_id
                        where employee.status=1 AND `date`=input_date GROUP BY view_employee_in_out_data.finger_print_id ORDER BY employee_attendance_id DESC;
   

 
 END;;

DROP PROCEDURE IF EXISTS `SP_DailyAttendanceThis`;;
CREATE PROCEDURE `SP_DailyAttendanceThis`(IN `input_date` DATE)
select employee.employee_id,employee.photo,CONCAT(COALESCE(employee.first_name,''),' ',COALESCE(employee.last_name,'')) AS fullName,department_name,
                        view_employee_in_out_data.employee_attendance_id,view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        DATE_FORMAT(view_employee_in_out_data.in_time,'%h:%i %p') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,'%h:%i %p') AS out_time, 
		TIME_FORMAT( work_shift.late_count_time, '%H:%i:%s' ) as lateCountTime,
	(SELECT CASE WHEN DATE_FORMAT(MIN(view_employee_in_out_data.in_time),'%H:%i:00')  > lateCountTime
            THEN 'Yes' 
            ELSE 'No' END) AS  ifLate,
 
            (SELECT CASE WHEN TIMEDIFF((DATE_FORMAT(MIN(view_employee_in_out_data.in_time),'%H:%i:%s')),work_shift.late_count_time)  > '0'
            THEN TIMEDIFF((DATE_FORMAT(MIN(view_employee_in_out_data.in_time),'%H:%i:%s')),work_shift.late_count_time) 
            ELSE '00:00:00' END) AS  totalLateTime,
             TIMEDIFF((DATE_FORMAT(work_shift.`end_time`,'%H:%i:%s')),work_shift.`start_time`) AS workingHour
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
JOIN work_shift on work_shift.work_shift_id = employee.work_shift_id
                        where  `date`=input_date GROUP BY view_employee_in_out_data.finger_print_id ORDER BY employee_attendance_id DESC;;

DROP PROCEDURE IF EXISTS `SP_DailyOverTime`;;
CREATE PROCEDURE `SP_DailyOverTime`(IN `input_date` DATE)
BEGIN

select employee.employee_id,employee.photo,CONCAT(COALESCE(employee.first_name,''),' ',COALESCE(employee.last_name,'')) AS fullName,department_name,
                        view_employee_in_out_data.employee_attendance_id,view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        DATE_FORMAT(view_employee_in_out_data.in_time,'%h:%i %p') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,'%h:%i %p') AS out_time,

             TIMEDIFF((DATE_FORMAT(work_shift.`end_time`,'%H:%i:%s')),work_shift.`start_time`) AS workingHour
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
JOIN work_shift on work_shift.work_shift_id = employee.work_shift_id
                        where `date`=input_date GROUP BY view_employee_in_out_data.finger_print_id ORDER BY employee_attendance_id DESC;



 END;;

DROP PROCEDURE IF EXISTS `SP_DepartmentDailyAttendance`;;
CREATE PROCEDURE `SP_DepartmentDailyAttendance`(IN `input_date` date, IN `department_id` int(10), IN `branch_id` int(10), IN `attendance_status` tinyint(4))
select employee.employee_id,designation.designation_name,department.department_name,branch.branch_name,employee.photo,CONCAT(COALESCE(employee.first_name,''),' ',COALESCE(employee.last_name,'')) AS fullName,department_name,
                        view_employee_in_out_data.employee_attendance_id,view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        view_employee_in_out_data.device_name, view_employee_in_out_data.shift_name, view_employee_in_out_data.late_by, view_employee_in_out_data.early_by,
                         view_employee_in_out_data.over_time, view_employee_in_out_data.in_out_time, view_employee_in_out_data.attendance_status,
                        DATE_FORMAT(view_employee_in_out_data.in_time,'%H:%i') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,'%H:%i') AS out_time
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
						inner join designation on designation.designation_id = employee.designation_id
						inner join branch on branch.branch_id = employee.branch_id
    where ( `date`=input_date)AND(employee.department_id=department_id OR department_id="")AND(employee.branch_id=branch_id OR branch_id="")AND(view_employee_in_out_data.attendance_status=attendance_status OR attendance_status="") GROUP BY view_employee_in_out_data.finger_print_id ORDER BY view_employee_in_out_data.finger_print_id COLLATE utf8mb4_unicode_ci;;

DROP PROCEDURE IF EXISTS `SP_getCompanyHoliday`;;
CREATE PROCEDURE `SP_getCompanyHoliday`(IN `fromDate` date, IN `toDate` date, IN `id` int(11))
BEGIN 
 
SELECT fdate,tdate,employee_id FROM company_holiday WHERE fdate>= fromDate AND tdate <=toDate AND employee_id = id;
   

 
 END;;

DROP PROCEDURE IF EXISTS `SP_getEmployeeInfo`;;
CREATE PROCEDURE `SP_getEmployeeInfo`(IN `employeeId` int(10))
BEGIN
	       SELECT employee.*,user.`user_name` FROM employee 
            INNER JOIN `user` ON `user`.`user_id` = employee.`user_id`
            WHERE employee_id = employeeId COLLATE utf8mb4_unicode_ci;
        END;;

DROP PROCEDURE IF EXISTS `SP_getHoliday`;;
CREATE PROCEDURE `SP_getHoliday`(IN `fromDate` date, IN `toDate` date)
BEGIN 
 
SELECT from_date,to_date FROM holiday_details WHERE from_date >= fromDate AND to_date <=toDate
;
   

 
 END;;

DROP PROCEDURE IF EXISTS `SP_getWeeklyHoliday`;;
CREATE PROCEDURE `SP_getWeeklyHoliday`()
BEGIN
	        select day_name , employee_id from  weekly_holiday where status=1
        COLLATE utf8mb4_unicode_ci;



END;;

DROP PROCEDURE IF EXISTS `SP_monthlyAttendance`;;
CREATE PROCEDURE `SP_monthlyAttendance`(IN `employeeId` int(10), IN `from_date` date, IN `to_date` date)
BEGIN 
 
select employee.employee_id,designation.designation_name,department.department_name,branch.branch_name,employee.photo,CONCAT(COALESCE(employee.first_name,''),' ',COALESCE(employee.last_name,'')) AS fullName,department_name,
                        view_employee_in_out_data.employee_attendance_id,view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        view_employee_in_out_data.device_name, view_employee_in_out_data.shift_name, view_employee_in_out_data.late_by, view_employee_in_out_data.early_by,
                         view_employee_in_out_data.over_time, view_employee_in_out_data.in_out_time, view_employee_in_out_data.attendance_status,
                        DATE_FORMAT(view_employee_in_out_data.in_time,'%H:%i') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,'%H:%i') AS out_time
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
                        inner join designation on designation.designation_id = employee.designation_id
                        inner join branch on branch.branch_id = employee.branch_id
                        where `date` between from_date and to_date and employee_id=employeeId
                        GROUP BY view_employee_in_out_data.date,view_employee_in_out_data.`finger_print_id` COLLATE utf8mb4_unicode_ci;
   

 
 END;;

DROP PROCEDURE IF EXISTS `SP_monthlyOverTime`;;
CREATE PROCEDURE `SP_monthlyOverTime`(IN `employeeId` int(10), IN `from_date` date, IN `to_date` date)
BEGIN

select employee.employee_id,CONCAT(COALESCE(employee.first_name,''),' ',COALESCE(employee.last_name,'')) AS fullName,department_name,
                        view_employee_in_out_data.finger_print_id,view_employee_in_out_data.date,view_employee_in_out_data.working_time,
                        DATE_FORMAT(view_employee_in_out_data.in_time,'%h:%i %p') AS in_time,DATE_FORMAT(view_employee_in_out_data.out_time,'%h:%i %p') AS out_time,
		
             TIMEDIFF((DATE_FORMAT(work_shift.`end_time`,'%H:%i:%s')),work_shift.`start_time`) AS workingHour
                        from employee
                        inner join view_employee_in_out_data on view_employee_in_out_data.finger_print_id = employee.finger_id
                        inner join department on department.department_id = employee.department_id
JOIN work_shift on work_shift.work_shift_id = employee.work_shift_id
                        where  `date` between from_date and to_date and employee_id=employeeId
                        GROUP BY view_employee_in_out_data.date,view_employee_in_out_data.`finger_print_id` COLLATE utf8mb4_unicode_ci;



 END;;

DELIMITER ;

DROP TABLE IF EXISTS `advance_deduction`;
CREATE TABLE `advance_deduction` (
  `advance_deduction_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `advance_amount` int NOT NULL,
  `date_of_advance_given` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deduction_amouth_per_month` int NOT NULL,
  `no_of_month_to_be_deducted` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`advance_deduction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `allowance`;
CREATE TABLE `allowance` (
  `allowance_id` int unsigned NOT NULL AUTO_INCREMENT,
  `allowance_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `allowance_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_of_basic` double NOT NULL,
  `allowance_criteria` tinyint DEFAULT NULL,
  `limit_per_month` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`allowance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `allowance` (`allowance_id`, `allowance_name`, `allowance_type`, `percentage_of_basic`, `allowance_criteria`, `limit_per_month`, `created_at`, `updated_at`) VALUES
(1,	'HRA-40%',	'Percentage',	50,	1,	0,	'2022-12-21 06:55:38',	'2022-12-21 19:25:08'),
(2,	'HRA-50%',	'Percentage',	50,	1,	0,	'2022-12-21 06:57:07',	'2022-12-21 19:24:58'),
(3,	'DA',	'Fixed',	0,	0,	1000,	'2022-12-21 11:57:03',	'2022-12-21 11:57:03');

DROP TABLE IF EXISTS `bonus_setting`;
CREATE TABLE `bonus_setting` (
  `bonus_setting_id` int unsigned NOT NULL AUTO_INCREMENT,
  `festival_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_of_bonus` int NOT NULL,
  `bonus_type` enum('Gross','Basic') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`bonus_setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `branch`;
CREATE TABLE `branch` (
  `branch_id` int unsigned NOT NULL AUTO_INCREMENT,
  `branch_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`branch_id`),
  KEY `branch_id` (`branch_id`),
  KEY `branch_name` (`branch_name`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `branch` (`branch_id`, `branch_name`, `created_at`, `updated_at`) VALUES
(1,	'Apprentice',	'2022-11-28 15:41:11',	'2022-11-28 15:41:11'),
(2,	'DPL',	'2022-11-28 15:41:27',	'2022-11-28 15:41:27'),
(3,	'FTE',	'2022-11-28 15:41:33',	'2022-11-28 15:41:33'),
(4,	'Leader Security',	'2022-11-28 15:41:47',	'2022-11-28 15:41:47'),
(5,	'Maruthi Contract',	'2022-11-28 15:42:02',	'2022-11-28 15:42:02'),
(6,	'Staff',	'2022-11-28 15:42:09',	'2022-11-28 15:42:09'),
(7,	'The Bharath Security',	'2022-11-28 15:42:24',	'2022-11-28 15:42:24'),
(8,	'Vinayaka Contract',	'2022-11-28 15:42:35',	'2022-11-28 15:42:35'),
(9,	'Workman',	'2022-11-28 15:42:44',	'2022-11-28 15:42:44'),
(10,	'PR ENTERPRISES',	'2023-02-13 11:09:54',	'2023-02-13 11:09:54');

DROP TABLE IF EXISTS `company_address_settings`;
CREATE TABLE `company_address_settings` (
  `company_address_setting_id` int unsigned NOT NULL AUTO_INCREMENT,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`company_address_setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `company_holiday`;
CREATE TABLE `company_holiday` (
  `company_holiday_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int unsigned NOT NULL,
  `fdate` date NOT NULL,
  `tdate` date NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`company_holiday_id`),
  KEY `employee_id` (`employee_id`),
  KEY `fdate` (`fdate`),
  KEY `tdate` (`tdate`),
  KEY `company_holiday_id` (`company_holiday_id`),
  KEY `updated_by` (`updated_by`),
  KEY `updated_at` (`updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `company_holiday` (`company_holiday_id`, `employee_id`, `fdate`, `tdate`, `comment`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(4,	1,	'2023-02-04',	'2023-02-04',	'Test',	1,	1,	'2023-02-04 15:58:15',	'2023-02-04 15:58:15');

DROP TABLE IF EXISTS `cost_centers`;
CREATE TABLE `cost_centers` (
  `cost_center_id` int unsigned NOT NULL AUTO_INCREMENT,
  `sub_department_id` int unsigned DEFAULT NULL,
  `cost_center_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`cost_center_id`),
  KEY `sub_department_id` (`sub_department_id`),
  KEY `cost_center_id` (`cost_center_id`),
  KEY `cost_center_number` (`cost_center_number`),
  CONSTRAINT `cost_centers_ibfk_1` FOREIGN KEY (`sub_department_id`) REFERENCES `sub_departments` (`sub_department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cost_centers` (`cost_center_id`, `sub_department_id`, `cost_center_number`, `created_at`, `updated_at`) VALUES
(11,	12,	'ASDF21345',	'2022-12-24 14:59:48',	'2022-12-24 14:59:48'),
(12,	14,	'CC123456',	'2022-12-30 11:37:09',	'2022-12-30 11:37:09'),
(13,	15,	'CORE PREPARATIONS',	'2023-02-11 15:03:43',	'2023-02-11 15:03:43'),
(14,	15,	'BAND SAW 1 & 2',	'2023-02-11 15:04:06',	'2023-02-11 15:04:06'),
(15,	15,	'FP TAILOR & RUFFLER M/C',	'2023-02-11 15:04:44',	'2023-02-11 15:04:44'),
(16,	15,	'FLANGING',	'2023-02-11 15:05:09',	'2023-02-11 15:05:09'),
(17,	15,	'SUB CUTTING',	'2023-02-11 15:05:30',	'2023-02-11 15:05:30'),
(18,	15,	'QUILT MOUNTING',	'2023-02-11 15:05:50',	'2023-02-11 15:05:50'),
(19,	15,	'FP TAPE EDGE',	'2023-02-11 15:06:13',	'2023-02-11 15:06:13'),
(20,	16,	'HOTMELT LINE',	'2023-02-11 15:15:29',	'2023-02-11 15:15:29'),
(21,	16,	'WATER BASE',	'2023-02-11 15:15:48',	'2023-02-11 15:15:48'),
(22,	16,	'TAPE EDGE AND ZIPPER COVER',	'2023-02-11 15:16:18',	'2023-02-11 15:16:18'),
(23,	16,	'RP TAILOR',	'2023-02-11 15:16:48',	'2023-02-11 15:16:48'),
(24,	16,	'RP SUBCUTTING',	'2023-02-11 15:17:07',	'2023-02-11 15:17:07'),
(25,	16,	'BAND SAW',	'2023-02-11 15:17:30',	'2023-02-11 15:17:30'),
(26,	17,	'DISPATCH',	'2023-02-13 10:56:35',	'2023-02-13 10:56:35'),
(27,	17,	'LOADING',	'2023-02-13 10:56:48',	'2023-02-13 10:56:48'),
(28,	17,	'UNLOADING',	'2023-02-13 10:56:58',	'2023-02-13 10:56:58'),
(29,	18,	'CUTTING PLANT',	'2023-02-13 11:02:59',	'2023-02-13 11:02:59'),
(30,	18,	'SHIFTING',	'2023-02-13 11:03:10',	'2023-02-13 11:03:10'),
(31,	16,	'ROLL PACK- M/C OPERATORS',	'2023-02-13 11:03:53',	'2023-02-13 11:03:53'),
(32,	18,	'STORE',	'2023-02-13 11:05:20',	'2023-02-13 11:05:20'),
(33,	20,	'PACKING',	'2023-02-13 11:07:33',	'2023-02-13 11:07:33'),
(34,	15,	'SPRING',	'2023-02-13 16:33:52',	'2023-02-13 16:33:52'),
(35,	19,	'HOUSEKEEPING',	'2023-02-15 10:55:51',	'2023-02-15 10:55:51'),
(36,	22,	'QUALITY ASSURANCE',	'2023-02-15 11:07:40',	'2023-02-15 11:07:40'),
(37,	23,	'NPD',	'2023-02-15 11:16:05',	'2023-02-15 11:16:05'),
(38,	24,	'MAINTENANCE',	'2023-02-15 12:03:04',	'2023-02-15 12:03:04');

DROP TABLE IF EXISTS `daily_cost_to_company`;
CREATE TABLE `daily_cost_to_company` (
  `daily_cost_to_company_id` int NOT NULL AUTO_INCREMENT,
  `date` date DEFAULT NULL,
  `contractor` int DEFAULT NULL,
  `staff` int DEFAULT NULL,
  `employee` int DEFAULT NULL,
  `present` int DEFAULT NULL,
  `absent` int DEFAULT NULL,
  `contractor_ctc` decimal(10,2) DEFAULT NULL,
  `staff_ctc` decimal(10,2) DEFAULT NULL,
  `total_ctc` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`daily_cost_to_company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `daily_cost_to_company` (`daily_cost_to_company_id`, `date`, `contractor`, `staff`, `employee`, `present`, `absent`, `contractor_ctc`, `staff_ctc`, `total_ctc`, `created_at`, `updated_at`) VALUES
(1,	'2023-03-02',	1,	71,	267,	72,	195,	562.81,	87071.00,	87633.81,	'2023-03-03 10:12:32',	'2023-03-03 10:12:32');

DROP TABLE IF EXISTS `deduction`;
CREATE TABLE `deduction` (
  `deduction_id` int unsigned NOT NULL AUTO_INCREMENT,
  `deduction_name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deduction_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage_of_basic` double NOT NULL,
  `limit_per_month` int DEFAULT NULL,
  `deduction_criteria` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`deduction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `deduction` (`deduction_id`, `deduction_name`, `deduction_type`, `percentage_of_basic`, `limit_per_month`, `deduction_criteria`, `created_at`, `updated_at`) VALUES
(2,	'ESIC',	'Percentage',	0.75,	0,	0,	'2022-12-22 13:58:50',	'2022-12-22 13:58:50'),
(3,	'EPF',	'Percentage',	12,	0,	0,	'2022-12-22 14:00:08',	'2022-12-22 18:28:47');

DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `department_id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`department_id`),
  KEY `department_id` (`department_id`),
  KEY `department_name` (`department_name`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `department` (`department_id`, `department_name`, `created_at`, `updated_at`) VALUES
(1,	'ACCOUNTS',	'2022-11-28 15:34:52',	'2022-11-28 15:36:28'),
(2,	'ADMIN',	'2022-11-28 15:35:02',	'2022-11-28 15:36:38'),
(3,	'CUTTING PLANT',	'2022-11-28 15:35:15',	'2022-11-28 15:36:48'),
(4,	'DISPATCH',	'2022-11-28 15:35:26',	'2022-11-28 15:36:59'),
(5,	'EHS',	'2022-11-28 15:35:41',	'2022-11-28 15:35:41'),
(6,	'HOUSE KEEPING',	'2022-11-28 15:35:52',	'2022-11-28 15:37:10'),
(7,	'HR',	'2022-11-28 15:35:57',	'2022-11-28 15:35:57'),
(8,	'LOADING',	'2022-11-28 15:36:16',	'2022-11-28 15:36:16'),
(9,	'MAINTENANCE',	'2022-11-28 15:37:32',	'2022-11-28 15:37:32'),
(10,	'NPD',	'2022-11-28 15:37:38',	'2022-11-28 15:37:38'),
(11,	'OPERATION REX',	'2022-11-28 15:37:49',	'2022-11-28 15:37:49'),
(12,	'PACKING',	'2022-11-28 15:38:02',	'2022-11-28 15:38:02'),
(13,	'PRODUCTION',	'2022-11-28 15:38:13',	'2022-11-28 15:38:13'),
(15,	'PRODUCTION ROLL PACKING',	'2022-11-28 15:38:57',	'2022-11-28 15:38:57'),
(16,	'PRODUCTION SPRING',	'2022-11-28 15:39:12',	'2022-11-28 15:39:12'),
(17,	'PURCHASE',	'2022-11-28 15:39:19',	'2022-11-28 15:39:19'),
(18,	'QUALITY ASSURANCE',	'2022-11-28 15:39:35',	'2022-11-28 15:39:35'),
(20,	'SECURITY',	'2022-11-28 15:39:53',	'2022-11-28 15:39:53'),
(21,	'SPRING',	'2022-11-28 15:40:05',	'2023-02-13 16:32:03'),
(22,	'STORE',	'2022-11-28 15:40:12',	'2022-11-28 15:40:12'),
(23,	'TAILOR',	'2022-11-28 15:40:21',	'2022-11-28 15:40:21'),
(24,	'PRODUCTION ROLL PACKING M/C',	'2023-02-11 14:58:05',	'2023-02-11 14:58:05');

DROP TABLE IF EXISTS `designation`;
CREATE TABLE `designation` (
  `designation_id` int unsigned NOT NULL AUTO_INCREMENT,
  `designation_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`designation_id`),
  KEY `designation_name` (`designation_name`),
  KEY `designation_id` (`designation_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `designation` (`designation_id`, `designation_name`, `created_at`, `updated_at`) VALUES
(1,	'HR/ADMIN',	'2022-11-28 15:58:21',	'2022-11-28 15:58:21'),
(2,	'Common',	'2022-11-28 15:58:28',	'2022-11-28 15:58:28'),
(3,	'HELPER',	'2023-02-11 14:54:24',	'2023-02-11 14:54:24'),
(4,	'OPERATIONS',	'2023-02-13 16:05:17',	'2023-02-13 16:05:17'),
(5,	'Executive',	'2023-02-24 11:58:09',	'2023-02-24 11:58:09');

DROP TABLE IF EXISTS `device`;
CREATE TABLE `device` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `protocol` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  `model` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `device_status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `port` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `devIndex` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `devResponse` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `verification_status` tinyint DEFAULT NULL,
  `type` tinyint DEFAULT NULL COMMENT '1 In , 2 Out',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `earn_leave_rule`;
CREATE TABLE `earn_leave_rule` (
  `earn_leave_rule_id` int unsigned NOT NULL,
  `for_month` int NOT NULL,
  `day_of_earn_leave` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `earn_leave_rule` (`earn_leave_rule_id`, `for_month`, `day_of_earn_leave`, `created_at`, `updated_at`) VALUES
(1,	1,	0.00,	'2022-06-11 14:10:52',	'2022-12-25 10:16:21'),
(1,	1,	0.00,	'2022-06-27 14:06:32',	'2022-12-25 10:16:21');

DROP TABLE IF EXISTS `earned_leave`;
CREATE TABLE `earned_leave` (
  `earned_leave_id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `month` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `el_balance` decimal(10,2) NOT NULL,
  `el` decimal(10,2) NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`earned_leave_id`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `earned_leave` (`earned_leave_id`, `employee_id`, `month`, `year`, `el_balance`, `el`, `status`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1,	1,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(2,	4,	'01',	'2023',	0.00,	0.00,	2,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-07 09:37:43'),
(3,	5,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(4,	6,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(5,	7,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(6,	8,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(7,	9,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(8,	10,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(9,	11,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(10,	12,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(11,	13,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(12,	14,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(13,	15,	'01',	'2023',	0.00,	1.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(14,	16,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(15,	17,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(16,	18,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(17,	19,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(18,	20,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(19,	21,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(20,	22,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(21,	23,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(22,	24,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(23,	25,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(24,	26,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(25,	27,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(26,	28,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(27,	29,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(28,	30,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(29,	31,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(30,	32,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(31,	33,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(32,	34,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(33,	35,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45'),
(34,	36,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(35,	37,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(36,	38,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(37,	39,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(38,	40,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(39,	41,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(40,	42,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(41,	43,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(42,	44,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(43,	45,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(44,	46,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(45,	47,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(46,	48,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(47,	49,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(48,	50,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(49,	51,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(50,	52,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(51,	53,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(52,	54,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(53,	55,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(54,	56,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(55,	57,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(56,	58,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(57,	59,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(58,	60,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(59,	61,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(60,	62,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(61,	63,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(62,	64,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(63,	65,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(64,	66,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(65,	67,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(66,	68,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46'),
(67,	69,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(68,	70,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(69,	71,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(70,	72,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(71,	73,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(72,	74,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(73,	75,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(74,	76,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(75,	77,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(76,	78,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(77,	79,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(78,	80,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(79,	81,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(80,	82,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(81,	83,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(82,	84,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(83,	85,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(84,	86,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(85,	87,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(86,	88,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(87,	89,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(88,	90,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(89,	91,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(90,	92,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(91,	93,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(92,	94,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(93,	95,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(94,	96,	'01',	'2023',	0.00,	1.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(95,	97,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(96,	98,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(97,	99,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(98,	100,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(99,	101,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(100,	102,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47'),
(101,	103,	'01',	'2023',	0.00,	1.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(102,	104,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(103,	105,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(104,	106,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(105,	107,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(106,	108,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(107,	109,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(108,	110,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(109,	111,	'01',	'2023',	0.00,	1.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(110,	112,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(111,	113,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(112,	114,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(113,	115,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(114,	116,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(115,	117,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(116,	118,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(117,	119,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(118,	120,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(119,	121,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(120,	122,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(121,	123,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(122,	124,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(123,	125,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(124,	126,	'01',	'2023',	0.00,	1.10,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(125,	127,	'01',	'2023',	0.00,	1.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(126,	128,	'01',	'2023',	0.00,	1.05,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(127,	129,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(128,	130,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(129,	131,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(130,	132,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(131,	133,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(132,	134,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(133,	135,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(134,	136,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48'),
(135,	137,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(136,	138,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(137,	139,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(138,	140,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(139,	141,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(140,	142,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(141,	143,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(142,	144,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(143,	145,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(144,	146,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(145,	147,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(146,	148,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(147,	149,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(148,	150,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(149,	151,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(150,	152,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(151,	153,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(152,	154,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(153,	155,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(154,	156,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(155,	157,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(156,	158,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(157,	159,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(158,	160,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(159,	161,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(160,	162,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(161,	163,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(162,	164,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(163,	165,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(164,	166,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(165,	167,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(166,	168,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49'),
(167,	169,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(168,	170,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(169,	171,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(170,	172,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(171,	173,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(172,	174,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(173,	175,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(174,	176,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(175,	177,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(176,	178,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(177,	179,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(178,	180,	'01',	'2023',	0.00,	1.30,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(179,	181,	'01',	'2023',	0.00,	1.15,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(180,	182,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(181,	183,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(182,	184,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(183,	185,	'01',	'2023',	0.00,	1.25,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(184,	186,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(185,	187,	'01',	'2023',	0.00,	1.20,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(186,	188,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(187,	189,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(188,	190,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(189,	191,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(190,	192,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(191,	193,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(192,	194,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(193,	195,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(194,	196,	'01',	'2023',	0.00,	1.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(195,	197,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50'),
(196,	198,	'01',	'2023',	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50');

DROP TABLE IF EXISTS `el_bonus`;
CREATE TABLE `el_bonus` (
  `elb_id` int NOT NULL AUTO_INCREMENT,
  `employee` int DEFAULT NULL,
  `finger_print_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `pay_status` tinyint DEFAULT NULL,
  `lwf_amount` decimal(10,2) DEFAULT NULL,
  `other_deduction` decimal(10,2) DEFAULT NULL,
  `deduction_amount` decimal(10,2) DEFAULT NULL,
  `net_amount` decimal(10,2) DEFAULT NULL,
  `paid_at` date DEFAULT NULL,
  `paid_on` datetime DEFAULT NULL,
  `remarks` text,
  `department` int DEFAULT NULL,
  `branch` int DEFAULT NULL,
  `costcenter` int DEFAULT NULL,
  `unit` int DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `month` int DEFAULT NULL,
  `year` int DEFAULT NULL,
  PRIMARY KEY (`elb_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

INSERT INTO `el_bonus` (`elb_id`, `employee`, `finger_print_id`, `amount`, `created_at`, `created_by`, `updated_at`, `updated_by`, `pay_status`, `lwf_amount`, `other_deduction`, `deduction_amount`, `net_amount`, `paid_at`, `paid_on`, `remarks`, `department`, `branch`, `costcenter`, `unit`, `status`, `month`, `year`) VALUES
(3,	1,	'ADM1001',	181.47,	'2023-01-06 15:50:47',	NULL,	'2023-01-06 15:50:47',	NULL,	1,	NULL,	NULL,	100.00,	81.00,	'2023-01-06',	'0000-00-00 00:00:00',	'settlement',	2,	1,	11,	0,	1,	1,	2023);

DROP TABLE IF EXISTS `email_notification`;
CREATE TABLE `email_notification` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `email_notification` (`id`, `email`) VALUES
(1,	'hari9578@gmail.com,bharani@iproat.com,selvakumar.a@duroflexworld.com,isaias.f@duroflexworld.com');

DROP TABLE IF EXISTS `employee`;
CREATE TABLE `employee` (
  `employee_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `finger_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` int unsigned DEFAULT '1',
  `sub_department_id` int unsigned DEFAULT NULL,
  `cost_center_id` int unsigned DEFAULT NULL,
  `designation_id` int unsigned DEFAULT '1',
  `branch_id` int unsigned DEFAULT '1',
  `supervisor_id` int DEFAULT '1',
  `work_shift_id` int unsigned DEFAULT '1',
  `weekoff_updated_at` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_no` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_of_the_city` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ifsc_no` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_no` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `esi_card_number` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pf_account_number` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aadhar_no` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `pay_grade_id` int unsigned DEFAULT '1',
  `hourly_salaries_id` int unsigned DEFAULT '0',
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `official_email` varchar(50) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `first_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `father_name` varchar(150) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `date_of_birth` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_joining` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_leaving` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `religion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `marital_status` varchar(12) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daily_wage` decimal(10,2) DEFAULT '0.00',
  `basic_amt` decimal(10,2) DEFAULT '0.00',
  `da_amt` decimal(10,2) DEFAULT '0.00',
  `hra_amt` decimal(10,2) DEFAULT '0.00',
  `leave_balance` decimal(10,2) DEFAULT '0.00',
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `emergency_contacts` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `document_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_expiry` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_title2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_expiry2` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_title3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_expiry3` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_title4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_expiry4` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_title5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_name5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_expiry5` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `service_charge` tinyint NOT NULL DEFAULT '1',
  `permanent_status` tinyint DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `leave_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `device_employee_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  KEY `employee_id` (`employee_id`),
  KEY `user_id` (`user_id`),
  KEY `finger_id` (`finger_id`),
  KEY `department_id` (`department_id`),
  KEY `sub_department_id` (`sub_department_id`),
  KEY `cost_center_id` (`cost_center_id`),
  KEY `designation_id` (`designation_id`),
  KEY `branch_id` (`branch_id`),
  KEY `weekoff_updated_at` (`weekoff_updated_at`),
  KEY `daily_wage` (`daily_wage`),
  KEY `basic_amt` (`basic_amt`),
  KEY `da_amt` (`da_amt`),
  KEY `hra_amt` (`hra_amt`),
  KEY `status` (`status`),
  KEY `service_charge` (`service_charge`),
  KEY `leave_balance` (`leave_balance`),
  KEY `leave_updated_at` (`leave_updated_at`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

INSERT INTO `employee` (`employee_id`, `user_id`, `finger_id`, `department_id`, `sub_department_id`, `cost_center_id`, `designation_id`, `branch_id`, `supervisor_id`, `work_shift_id`, `weekoff_updated_at`, `bank_name`, `bank_branch`, `bank_account_no`, `bank_of_the_city`, `ifsc_no`, `pan_no`, `esi_card_number`, `pf_account_number`, `aadhar_no`, `pay_grade_id`, `hourly_salaries_id`, `email`, `official_email`, `first_name`, `last_name`, `father_name`, `date_of_birth`, `date_of_joining`, `date_of_leaving`, `gender`, `religion`, `marital_status`, `photo`, `daily_wage`, `basic_amt`, `da_amt`, `hra_amt`, `leave_balance`, `address`, `emergency_contacts`, `document_title`, `document_name`, `document_expiry`, `document_title2`, `document_name2`, `document_expiry2`, `document_title3`, `document_name3`, `document_expiry3`, `document_title4`, `document_name4`, `document_expiry4`, `document_title5`, `document_name5`, `document_expiry5`, `phone`, `status`, `service_charge`, `permanent_status`, `created_by`, `updated_by`, `deleted_at`, `leave_updated_at`, `created_at`, `updated_at`, `device_employee_id`) VALUES
(1,	1,	'ADM1001',	2,	12,	11,	1,	6,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'0',	'City',	'0',	'0',	'0',	'0',	'0',	NULL,	NULL,	'admin@gmail.com',	'admin@gmail.com',	'Administrator',	NULL,	'Administrator',	'1987-12-01',	'2022-12-05',	NULL,	'Male',	'Hindu',	'Unmarried',	NULL,	650.00,	175.00,	202.73,	272.27,	0.00,	'address line , district.',	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	'ADM1001'),
(4,	4,	'MC006',	13,	15,	13,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6082064839',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MADESH BABU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	611.00,	238.77,	173.15,	199.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-07 09:37:43',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(5,	5,	'MC005',	12,	20,	33,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6264511818',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N.MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	530.00,	238.77,	173.15,	118.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(6,	6,	'MC004',	13,	15,	13,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'830813397',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V.MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	560.00,	238.77,	173.15,	148.08,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(7,	7,	'VC005',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'902235224',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K.VIJAYENDRAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	572.00,	238.77,	173.15,	160.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(8,	8,	'MC003',	16,	15,	34,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566424448',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S.AROKIASAMY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	707.00,	238.77,	173.15,	295.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(9,	9,	'VC006',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6501734100',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.MUNIRATHNAMMA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	560.00,	238.77,	173.15,	148.08,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(10,	10,	'VC007',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'824709894',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C.SAKTHIVEL',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	602.00,	238.77,	173.15,	190.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(11,	11,	'VC008',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6064383111',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'P.NANDEESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	505.00,	238.77,	173.15,	93.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(12,	12,	'MC007',	22,	18,	32,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6202755381',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'J.RAJESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	495.00,	238.77,	173.15,	83.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(13,	13,	'VC009',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6533001925',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A.PALANI BHARATHI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	485.00,	238.77,	173.15,	73.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(14,	14,	'VC010',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6444374274',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N.SAMPATH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	505.00,	238.77,	173.15,	93.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(15,	15,	'VC011',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6237561099',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'G.NAVEEN KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	475.00,	238.77,	173.15,	63.08,	1.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(16,	16,	'VC012',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6165601580',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.R.MANJULA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	490.00,	238.77,	173.15,	78.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	NULL),
(17,	17,	'VC013',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'50100389328917',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C.SIVA KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	605.00,	238.77,	173.15,	193.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(18,	18,	'VC014',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6826334409',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MUNENDRA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	505.00,	238.77,	173.15,	93.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(19,	19,	'VC015',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6760367595',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K.SABHESA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	470.00,	238.77,	173.15,	58.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(20,	20,	'MC010',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6268557554',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'H.MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	470.00,	238.77,	173.15,	58.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(21,	21,	'VC016',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6453495947',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B.MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	238.77,	173.15,	38.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(22,	22,	'VC017',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6764845979',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'G.MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	655.00,	238.77,	173.15,	243.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(23,	23,	'VC018',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6142852484',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'H.NAVEEN KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	694.00,	238.77,	173.15,	282.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(24,	24,	'VC020',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'7061009229',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SHIVRAT ROBIDAS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	460.00,	238.77,	173.15,	48.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(25,	25,	'VC021',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6719767913',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'JOHN PETER',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	460.00,	238.77,	173.15,	48.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(26,	26,	'MC009',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6858376699',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M.NARAYANAPPA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	451.92,	238.77,	173.15,	40.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(27,	27,	'MC008',	22,	18,	32,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6054762815',	'Hosur',	'IDIB000D010',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M.RAMESH BABU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	590.00,	238.77,	173.15,	178.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(28,	28,	'MC012',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6850472483',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R YELLAPPA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	460.00,	238.77,	173.15,	48.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(29,	29,	'MC013',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6416715992',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V.JAYA SHANKAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	480.00,	238.77,	173.15,	68.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(30,	30,	'VC022',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'27',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'R.UDAY KUMAR',	NULL,	'FN',	'1987-12-28',	'2023-01-01',	NULL,	'Male',	NULL,	NULL,	NULL,	460.00,	165.54,	202.73,	91.73,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-06 13:28:45',	NULL),
(31,	31,	'VC023',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'579708934',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V.MANJUNATH REDDY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	238.77,	173.15,	38.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	NULL),
(32,	32,	'VC024',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6647806568',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'JAYAPRASAD',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	460.00,	238.77,	173.15,	48.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(33,	33,	'MC014',	22,	18,	32,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6753306675',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V.THAJESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	475.00,	238.77,	173.15,	63.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(34,	34,	'VC025',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566430869',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K.MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	485.00,	238.77,	173.15,	73.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(35,	35,	'VC001',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566404249',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.S.SOWBAGHYA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	565.00,	238.77,	173.15,	153.08,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:45',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(36,	36,	'VC002',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6109633777',	'Hosur',	'IDIB000H011',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.B.VARALAKSHMI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	565.00,	238.77,	173.15,	153.08,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(37,	37,	'VC003',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566434604',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N.RAMAPPA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	610.00,	238.77,	173.15,	198.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(38,	38,	'VC026',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6764586321',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'D.SATHISH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	445.00,	238.77,	173.15,	33.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(39,	39,	'VC027',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6295896550',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C.NAGARAJU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(40,	40,	'VC004',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566435937',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N.MAHADEVAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	485.00,	238.77,	173.15,	73.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(41,	41,	'MC015',	23,	15,	13,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'999684302',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S.ANAND',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	490.00,	238.77,	173.15,	78.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(42,	42,	'VC042',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'IDBI BANK',	'Kanpur',	'213102000033956',	'Hosur',	'IBKL0000213',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'JANARDHAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	840.00,	238.77,	173.15,	428.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(43,	43,	'VC028',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6609616925',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.JYOTHI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	490.00,	238.77,	173.15,	78.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(44,	44,	'VC029',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B DEEPAK',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	840.00,	238.77,	173.15,	428.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(45,	45,	'VC030',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6146495562',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'NANDEESH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	490.00,	238.77,	173.15,	78.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(46,	46,	'VC031',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6149298719',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S DEEPU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	431.92,	238.77,	173.15,	20.00,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(47,	47,	'LS005',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R.THIMMARAYAPPA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	480.00,	238.77,	173.15,	68.08,	1.25,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(48,	48,	'LS004',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.SAROJAMMA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	425.00,	238.77,	173.15,	13.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	NULL),
(49,	49,	'LS003',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.C.MUNIRATHNAMMA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	455.00,	238.77,	173.15,	43.08,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(50,	50,	'LS010',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.SAMANTHA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	425.00,	238.77,	173.15,	13.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(51,	51,	'LS009',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.T.MUNIRATHNA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	425.00,	238.77,	173.15,	13.08,	1.25,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(52,	52,	'LS008',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.C NARAYANAMMA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	425.00,	238.77,	173.15,	13.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(53,	53,	'LS007',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.SHIVAMMA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	425.00,	238.77,	173.15,	13.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(54,	54,	'LS001',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.M.RATHNAMMA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	469.00,	238.77,	173.15,	57.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(55,	55,	'LS006',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C M MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	421.92,	238.77,	173.15,	10.00,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(56,	56,	'LS002',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.N.SUNANDHA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	469.00,	238.77,	173.15,	57.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(57,	57,	'VC032',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6202230578',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C.GOPAL',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	480.00,	238.77,	173.15,	68.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(58,	58,	'VC033',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6155720008',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R.ARUN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(59,	59,	'VC035',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6544935839',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'BHYRESHA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(60,	60,	'MC016',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'PALLAVAN GRAMA BANK',	'Kanpur',	'10007978896',	'Hosur',	'IDIB0PLB001',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ANJAPPA P',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	431.92,	238.77,	173.15,	20.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(61,	61,	'VC037',	18,	22,	36,	3,	8,	NULL,	NULL,	NULL,	'STATE BANK OF INDIA',	'Kanpur',	'31341094978',	'Hosur',	'SBIN0011058',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S.SASI KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	559.92,	238.77,	173.15,	148.00,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(62,	62,	'VC038',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6538060250',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ANTO AMALRAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(63,	63,	'VC039',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6352550370',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N.BABU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	NULL),
(64,	64,	'VC040',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'CANARABANK',	'Kanpur',	'64692610007879',	'Hosur',	'CNRB0016469',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MURALIDHARA M',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	490.00,	238.77,	173.15,	78.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(65,	65,	'VC041',	4,	17,	27,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'831865471',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'NAGARAJ G',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(66,	66,	'VC043',	16,	15,	34,	3,	8,	NULL,	NULL,	NULL,	'CANARABANK',	'Kanpur',	'8445101060770',	'Hosur',	'CNRB0008445',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S.KIRAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	590.00,	238.77,	173.15,	178.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(67,	67,	'VC044',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6456473974',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N SASI KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(68,	68,	'VC045',	16,	15,	34,	3,	8,	NULL,	NULL,	NULL,	' UJJIVAN SMALL FINANCE BANK',	'Kanpur',	'1676110010050790',	'Hosur',	'UJVN0001676',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V MANI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	600.00,	238.77,	173.15,	188.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:46',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(69,	69,	'VC046',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'7043301565',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MADHU N',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(70,	70,	'MC017',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6509861798',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S MADHU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	451.92,	238.77,	173.15,	40.00,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(71,	71,	'VC047',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6236837800',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SOMA SEKARA REDDY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	238.77,	173.15,	38.08,	1.25,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(72,	72,	'VC048',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6810847872',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'KISHOREKUMAR GIRI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	NULL),
(73,	73,	'VC049',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566393712',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'VEDHAMUTHI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	490.00,	238.77,	173.15,	78.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	NULL),
(74,	74,	'MC001',	13,	15,	13,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6162518366',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'L.MUTHAPPA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	597.00,	238.77,	173.15,	185.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	NULL),
(75,	75,	'VC050',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'UCO BANK',	'Kanpur',	'16260110035634',	'Hosur',	'UCBA0001626',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SATYAJIT SAMANATARAY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	NULL),
(76,	76,	'VC051',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6233899269',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R RAMA MOORTHY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	NULL),
(77,	77,	'VC052',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6126053195',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DAVID KUMAR M',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	NULL),
(78,	78,	'VC053',	16,	15,	34,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'UBIN0931314',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'HARI HARAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	451.92,	238.77,	173.15,	40.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	NULL),
(79,	79,	'VC054',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'76',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'P SANDEEP',	NULL,	'FN',	'1988-02-15',	'2023-02-19',	NULL,	'Male',	NULL,	NULL,	NULL,	451.92,	162.73,	202.73,	86.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:40',	'2023-02-06 13:28:47',	NULL),
(80,	80,	'VC055',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'UBIN0931314',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B RANJITH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	451.92,	238.77,	173.15,	40.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(81,	81,	'LS011',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'UBIN0931314',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MRS.ASHWINI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	421.92,	238.77,	173.15,	10.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(82,	82,	'VC056',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'UBIN0931314',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N HARISH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(83,	83,	'MC022',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'UNION BANK',	'Kanpur',	'313122010000622',	'Hosur',	'UBIN0931314',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PINTU SATHY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	431.92,	238.77,	173.15,	20.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(84,	84,	'VC057',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'UNION BANK',	'Kanpur',	'0',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'BULAN NAMOSUDRA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(85,	85,	'VC058',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'UNION BANK',	'Kanpur',	'0',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'AKASH T',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(86,	86,	'VC059',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'83',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'MAHESH KUMAR',	NULL,	'FN',	'1988-02-22',	'2023-02-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	162.73,	202.73,	76.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-06 13:28:47',	NULL),
(87,	87,	'VC060',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'UNION BANK',	'Kanpur',	'0',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SANTHOSH REDDY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	620.00,	238.77,	173.15,	208.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(88,	88,	'VC093',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'UNION BANK',	'Kanpur',	'0',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RAM KUMAR B',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	800.00,	238.77,	173.15,	388.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(89,	89,	'VC061',	18,	22,	36,	3,	8,	NULL,	NULL,	NULL,	'UNION BANK',	'Kanpur',	'0',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ABHISHEK S',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	660.00,	238.77,	173.15,	248.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(90,	90,	'MC031',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'50100557336742',	'Hosur',	'HDFC0004123',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'BALAMURUGAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	431.92,	238.77,	173.15,	20.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(91,	91,	'VC087',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C VIJAY KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(92,	92,	'VC063',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K SATHISH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	580.00,	238.77,	173.15,	168.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(93,	93,	'VC095',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N MAHESH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	431.92,	238.77,	173.15,	20.00,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(94,	94,	'VC064',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(95,	95,	'LS012',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'HEMAVATHI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	421.92,	238.77,	173.15,	10.00,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	NULL),
(96,	96,	'VC065',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N MALLESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(97,	97,	'VC066',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SHIBANANDA NAIK',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(98,	98,	'VC067',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SAMRAT KALINDI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(99,	99,	'VC068',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MANOJ N',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(100,	100,	'MC029',	18,	22,	36,	3,	5,	NULL,	NULL,	NULL,	'HDFC BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K SAKTHIVEL',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	575.00,	238.77,	173.15,	163.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(101,	101,	'MC024',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6238840822',	'Hosur',	'IDIB000B162',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MADESH ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(102,	102,	'VC071',	18,	22,	36,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SIBA PRASAD MALIK',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	238.77,	173.15,	88.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:47',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(103,	103,	'VC072',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'THIRUMALESH R',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	441.92,	238.77,	173.15,	30.00,	1.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(104,	104,	'VC074',	18,	22,	36,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MOORTHY A',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	238.77,	173.15,	88.08,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(105,	105,	'MC002',	12,	20,	33,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566413822',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'T MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	645.00,	238.77,	173.15,	233.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(106,	106,	'VC104',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RENSING RONGHANG',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	625.00,	238.77,	173.15,	213.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(107,	107,	'LS013',	6,	19,	35,	3,	4,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'KANTHA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Female',	NULL,	NULL,	NULL,	421.92,	238.77,	173.15,	10.00,	1.25,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(108,	108,	'VC094',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DAYA SANKAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	800.00,	238.77,	173.15,	388.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(109,	109,	'MC025',	2,	12,	11,	1,	5,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'106',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'KANTHARAJU',	NULL,	'FN',	'1988-03-16',	'2023-03-21',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-06 13:28:48',	NULL),
(110,	110,	'VC102',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SAMIR SINGH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(111,	111,	'VC076',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S PRABHAKAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	NULL),
(112,	112,	'MC026',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'STATE BANK OF INDIA',	'Kanpur',	'39037007960',	'Hosur',	'SBIN0013255',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'LALSUN MARDI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(113,	113,	'VC083',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'AMIT SINGH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	495.00,	238.77,	173.15,	83.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(114,	114,	'VC089',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'111',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'C M NANDEESH',	NULL,	'FN',	'1988-03-21',	'2023-03-26',	NULL,	'Male',	NULL,	NULL,	NULL,	545.00,	178.73,	202.73,	163.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-06 13:28:48',	NULL),
(115,	115,	'VC097',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'NILKUMAR REE',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(116,	116,	'VC118',	16,	15,	34,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K MADESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(117,	117,	'VC019',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SESHADRI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(118,	118,	'VC084',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'AJOY GOALA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(119,	119,	'VC101',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V SASI KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(120,	120,	'VC100',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M NARAYANA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(121,	121,	'VC092',	10,	23,	37,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MANTU KARMAKER',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	238.77,	173.15,	38.08,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(122,	122,	'VC121',	9,	24,	38,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K VIJAY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	600.00,	238.77,	173.15,	188.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(123,	123,	'VC148',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SHAMEER',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(124,	124,	'VC099',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'INDRAJEET MALAHU PRAJAPATI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(125,	125,	'VC150',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MAHANGI DEVI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(126,	126,	'VC120',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ALAMELU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	238.77,	173.15,	38.08,	1.10,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(127,	127,	'VC078',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RAVI R',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	NULL),
(128,	128,	'VC090',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DOMNIC PAUL',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	470.00,	238.77,	173.15,	58.08,	1.05,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(129,	129,	'VC107',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'126',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'AMBARISH N',	NULL,	'FN',	'1988-04-05',	'2023-04-10',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-06 13:28:48',	NULL),
(130,	130,	'MC027',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'800000019881467',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'GANDHI RAJBHAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(131,	131,	'MC028',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'YES BANK',	'Kanpur',	'800000019881483',	'Hosur',	'YESB0CMSNOC',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DILIP GHOSUS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(132,	132,	'VC079',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SURYAKANTA MOHANTY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(133,	133,	'VC081',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'130',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'SURAJ DUSAD',	NULL,	'FN',	'1988-04-09',	'2023-04-14',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	178.73,	202.73,	68.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-06 13:28:48',	NULL),
(134,	134,	'VC123',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'AJOY DAS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(135,	135,	'VC088',	18,	22,	36,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S MAHESH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	545.00,	238.77,	173.15,	133.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:48',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(136,	136,	'VC069',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'133',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'VASANTHA KUMARI',	NULL,	'FN',	'1988-04-12',	'2023-04-17',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-06 13:28:49',	NULL),
(137,	137,	'VC091',	16,	15,	34,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PRAKASH SAHOO',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	1078.00,	238.77,	173.15,	666.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(138,	138,	'VC119',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'GUPTA GIRI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(139,	139,	'VC108',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ATUL SABAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(140,	140,	'VC082',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PINKUNA JENA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	470.00,	238.77,	173.15,	58.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(141,	141,	'VC034',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SUKANTA URANG',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(142,	142,	'VC126',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SANTHOSH MOHANTY',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	NULL),
(143,	143,	'LS014',	2,	12,	11,	1,	4,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'140',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'MANTU DEORI',	NULL,	'FN',	'1988-04-19',	'2023-04-24',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	162.73,	202.73,	84.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-06 13:28:49',	NULL),
(144,	144,	'VC073',	4,	17,	27,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	238.77,	173.15,	88.08,	1.25,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(145,	145,	'VC106',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DULAN MUNDA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(146,	146,	'VC122',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RABINDRA GOUDA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(147,	147,	'VC096',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'AMAL LOURDU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(148,	148,	'VC116',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PRADIP TANTI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(149,	149,	'VC077',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DHAYANANTHA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(150,	150,	'VC070',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SHARATH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(151,	151,	'VC103',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'BINAY KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(152,	152,	'VC098',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'H M VENKATESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(153,	153,	'VC086',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'T CHETHAN',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(154,	154,	'VC085',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'153',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'JNANA RANJAN DAS',	NULL,	'FN',	'1988-05-02',	'2023-05-07',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-06 13:28:49',	NULL),
(155,	155,	'VC105',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PRADIP RABIDAS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(156,	156,	'VC062',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'155',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'SAROJ KUMAR MUDULI',	NULL,	'FN',	'1988-05-04',	'2023-05-09',	NULL,	'Male',	NULL,	NULL,	NULL,	660.00,	162.73,	202.73,	294.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-06 13:28:49',	NULL),
(157,	157,	'MC032',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'BAPPY REE',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.25,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(158,	158,	'MC023',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RANJIT RIKISON',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(159,	159,	'VC132',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'UTTAM BARIK',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	NULL),
(160,	160,	'VC139',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PITAMBAR MANJHI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(161,	161,	'VC145',	12,	20,	33,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'PAPPU YADAV',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(162,	162,	'VC143',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SIKENDAR MARIYA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(163,	163,	'VC144',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'WAKIL MARIYA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(164,	164,	'VC154',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SANJOY KALINDI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(165,	165,	'MC033',	13,	15,	13,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MALLESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(166,	166,	'VC161',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'HIRALAL DAS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(167,	167,	'VC140',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RAKESH DAS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(168,	168,	'VC141',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'167',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'LOVO BIRUA',	NULL,	'FN',	'1988-05-16',	'2023-05-21',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:49',	'2023-01-12 07:01:46',	'2023-02-06 13:28:49',	NULL),
(169,	169,	'VC149',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MONORAMA TOWARI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(170,	170,	'VC131',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RAJENDRA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(171,	171,	'VC152',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RABINDRA KALINDI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(172,	172,	'VC151',	22,	18,	32,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DILIP KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(173,	173,	'VC146',	3,	18,	29,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ANIMESH KALINDI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	1.20,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(174,	174,	'VC147',	16,	15,	34,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'OSHIHAR RAM',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(175,	175,	'VC142',	23,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M RAVI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	238.77,	173.15,	38.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(176,	176,	'VC127',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'DILIP RAVIDAS',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	NULL),
(177,	177,	'VC133',	13,	15,	13,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'BHAJAMANA KANDI',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	NULL),
(178,	178,	'VC155',	13,	16,	31,	3,	8,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'0',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'LAKHI SAH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	238.77,	173.15,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	0,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	NULL),
(179,	179,	'MC018',	4,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'566409076',	'Hosur',	'IDIB000M097',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C.THIMMARAYAPPA',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	525.00,	238.77,	173.15,	113.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	NULL),
(180,	180,	'MC019',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'6029243824',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'INNESH RAJ',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	238.77,	173.15,	88.08,	1.30,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	NULL),
(181,	181,	'MC020',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'579712725',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M.SURESH BABU',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	515.00,	238.77,	173.15,	103.08,	1.15,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	NULL),
(182,	182,	'MC021',	8,	17,	27,	3,	5,	NULL,	NULL,	NULL,	'INDIAN BANK',	'Kanpur',	'772389870',	'Hosur',	'IDIB000T060',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K.GANESH',	NULL,	'FN',	'1988-06-19',	'2022-12-26',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	238.77,	173.15,	88.08,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	NULL),
(183,	183,	'VC115',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'182',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'PURAN URANG',	NULL,	'FN',	'1988-05-31',	'2023-06-05',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	178.73,	202.73,	118.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(184,	184,	'VC114',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'183',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'DIPAK MOHANTA',	NULL,	'FN',	'1988-06-01',	'2023-06-06',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	178.73,	202.73,	68.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(185,	185,	'VC112',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'184',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'DIPAK KUMAR GUDA',	NULL,	'FN',	'1988-06-02',	'2023-06-07',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	178.73,	202.73,	118.54,	1.25,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(186,	186,	'VC111',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'185',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'ARTO SINKU',	NULL,	'FN',	'1988-06-03',	'2023-06-08',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	178.73,	202.73,	68.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(187,	187,	'VC075',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'186',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'ANNATH',	NULL,	'FN',	'1988-06-04',	'2023-06-09',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	162.73,	202.73,	134.54,	1.20,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(188,	188,	'VC0125',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'187',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'BIJIT SOTAL',	NULL,	'FN',	'1988-06-05',	'2023-06-10',	NULL,	'Male',	NULL,	NULL,	NULL,	490.00,	165.54,	202.73,	121.73,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(189,	189,	'VC113',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'198',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'RANJAN CHANRA TANTI',	NULL,	'FN',	'1988-06-16',	'2023-06-21',	NULL,	'Male',	NULL,	NULL,	NULL,	500.00,	178.73,	202.73,	118.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(190,	190,	'VC080',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'189',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'NARESH DALAI',	NULL,	'FN',	'1988-06-07',	'2023-06-12',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(191,	191,	'VC135',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'190',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'RANJIT KUMAR SHARMA',	NULL,	'FN',	'1988-06-08',	'2023-06-13',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(192,	192,	'VC110',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'191',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'SATYA NARAYAN BEHERA',	NULL,	'FN',	'1988-06-09',	'2023-06-14',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(193,	193,	'VC130',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'192',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'SONU KUMAR',	NULL,	'FN',	'1988-06-10',	'2023-06-15',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	178.73,	202.73,	68.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:47',	'2023-02-06 13:28:50',	NULL),
(194,	194,	'VC128',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'193',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'BILU URANG',	NULL,	'FN',	'1988-06-11',	'2023-06-16',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:48',	'2023-02-06 13:28:50',	NULL),
(195,	195,	'VC137',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'194',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'ANIL BIRUA',	NULL,	'FN',	'1988-06-12',	'2023-06-17',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	178.73,	202.73,	68.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:48',	'2023-02-06 13:28:50',	NULL),
(196,	196,	'VC117',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'195',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'AKASH PATRA',	NULL,	'FN',	'1988-06-13',	'2023-06-18',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	1.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:48',	'2023-02-06 13:28:50',	NULL),
(197,	197,	'VC136',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'196',	'City',	'0',	'0',	'0',	'0',	'0',	1,	0,	NULL,	NULL,	'NASIB KONDELKEL',	NULL,	'FN',	'1988-06-14',	'2023-06-19',	NULL,	'Male',	NULL,	NULL,	NULL,	450.00,	178.73,	202.73,	68.54,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:48',	'2023-02-06 13:28:50',	NULL),
(198,	198,	'VC156',	2,	12,	11,	1,	8,	NULL,	NULL,	NULL,	'Bank',	'Branch',	'197',	'City',	'0',	'0',	'0',	'0',	'0',	1,	NULL,	NULL,	NULL,	'DOMNIC SANGMA',	NULL,	'FN',	'1988-06-15',	'2023-06-20',	NULL,	'Male',	NULL,	NULL,	NULL,	411.92,	162.73,	202.73,	46.46,	0.00,	NULL,	'0',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'9876543210',	1,	1,	0,	1,	1,	NULL,	'2023-02-06 13:28:50',	'2023-01-12 07:01:48',	'2023-02-06 13:28:50',	'VC156'),
(199,	199,	'FRNFTE01',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'E NAGARAJ',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	NULL),
(200,	200,	'FRNFTE02',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A THOMAS',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	NULL),
(201,	201,	'FRNFTE03',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M SRINIVASAN',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	NULL),
(202,	202,	'FRNFTE04',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B S NAGARAJ',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	NULL),
(203,	203,	'FRNFTE05',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K VEERAMANI',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	NULL),
(204,	204,	'FRNFTE06',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K SRINIVASAN',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	NULL),
(205,	205,	'FRNFTE07',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'SONJOY KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(206,	206,	'FRNFTE08',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K GURURAJ',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(207,	207,	'FRNFTE09',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(208,	208,	'FRNFTE10',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N VINAY KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(209,	209,	'FRNFTE11',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MAHENDRAN T',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(210,	210,	'FRNFTE12',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S SRINIVASAN',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(211,	211,	'FRNFTE13',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M RAJESH',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(212,	212,	'FRNFTE14',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S MANIKANDAN',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(213,	213,	'FRNFTE15',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R HARISH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(214,	214,	'FRNFTE16',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A THEJASH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(215,	215,	'FRNFTE17',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V RAMESH',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(216,	216,	'FRNFTE18',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'ALLABAHAKASH',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(217,	217,	'FRNFTE19',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V RAGHU',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(218,	218,	'FRNFTE20',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B V MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(219,	219,	'FRNFTE21',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'L ARUL RAJ',	NULL,	'FN',	'1988-06-19',	'2022-04-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(220,	220,	'FRNS10',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B ANJANAPPA',	NULL,	'FN',	'1988-06-19',	'1989-10-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(221,	221,	'FRNS113',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M RAMACHANDRA',	NULL,	'FN',	'1988-06-19',	'2008-09-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	NULL),
(222,	222,	'FRNS126',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'E LAGUMAIAH',	NULL,	'FN',	'1988-06-19',	'2012-09-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(223,	223,	'FRNS13',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C NAGARAJU',	NULL,	'FN',	'1988-06-19',	'1996-01-04',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(224,	224,	'FRNS130',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A BYRAPPA',	NULL,	'FN',	'1988-06-19',	'2015-07-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(225,	225,	'FRNS132',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B M SHASHI KUMAR',	NULL,	'FN',	'1988-06-19',	'2010-01-10',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(226,	226,	'FRNS133',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'L RAGHU SHANKAR',	NULL,	'FN',	'1988-06-19',	'2011-01-06',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(227,	227,	'FRNS134',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N RADHA KRISHNA',	NULL,	'FN',	'1988-06-19',	'2012-12-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(228,	228,	'FRNS135',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'C LINGAIAH',	NULL,	'FN',	'1988-06-19',	'2012-01-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(229,	229,	'FRNS142',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'G RAMESH',	NULL,	'FN',	'1988-06-19',	'2016-01-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(230,	230,	'FRNS143',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M GOPAL',	NULL,	'FN',	'1988-06-19',	'2016-01-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(231,	231,	'FRNS146',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R MANJUNATH',	NULL,	'FN',	'1988-06-19',	'2016-06-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(232,	232,	'FRNS148',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'P DHARUMAN',	NULL,	'FN',	'1988-06-19',	'2015-12-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(233,	233,	'FRNS149',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'CHIKKONAPPA L',	NULL,	'FN',	'1988-06-19',	'2016-06-15',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(234,	234,	'FRNS153',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'P VELAN',	NULL,	'FN',	'1988-06-19',	'2017-09-05',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(235,	235,	'FRNS161',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K UTHTHIRAN',	NULL,	'FN',	'1988-06-19',	'2019-04-03',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(236,	236,	'FRNS162',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A YAGAPPAN',	NULL,	'FN',	'1988-06-19',	'2019-06-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(237,	237,	'FRNS163',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A VIVEK WILSON',	NULL,	'FN',	'1988-06-19',	'2019-06-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(238,	238,	'FRNS167',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A MATHAIYAN',	NULL,	'FN',	'1988-06-19',	'2020-11-26',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	NULL),
(239,	239,	'FRNS172',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'J DHANASEKARAN',	NULL,	'FN',	'1988-06-19',	'2021-08-04',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(240,	240,	'FRNS175',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'RANA M',	NULL,	'FN',	'1988-06-19',	'2021-09-06',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(241,	241,	'FRNS176',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'MANJUNATH T',	NULL,	'FN',	'1988-06-19',	'2021-09-06',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(242,	242,	'FRNS182',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S GANESH',	NULL,	'FN',	'1988-06-19',	'2021-10-11',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(243,	243,	'FRNS183',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V PRASANTH',	NULL,	'FN',	'1988-06-19',	'2021-10-11',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(244,	244,	'FRNS184',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A MOHAN',	NULL,	'FN',	'1988-06-19',	'2021-10-14',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(245,	245,	'FRNS186',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M MADESH',	NULL,	'FN',	'1988-06-19',	'2013-03-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(246,	246,	'FRNS187',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S MAGESH KUMAR',	NULL,	'FN',	'1988-06-19',	'2022-06-06',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(247,	247,	'FRNW05',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M LAGUMAIAH',	NULL,	'FN',	'1988-06-19',	'1990-06-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(248,	248,	'FRNW06',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'P MUNIRAJ',	NULL,	'FN',	'1988-06-19',	'1991-03-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(249,	249,	'FRNW08',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M NATARAJ',	NULL,	'FN',	'1988-06-19',	'1990-06-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(250,	250,	'FRNW17',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N THIRUMALAPPA',	NULL,	'FN',	'1988-06-19',	'1992-02-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(251,	251,	'FRNW18',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M DAMODHARA REDDY',	NULL,	'FN',	'1988-06-19',	'1992-02-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(252,	252,	'FRNW26',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'L VISWANATHAN',	NULL,	'FN',	'1988-06-19',	'1995-04-03',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(253,	253,	'FRNW32',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'Y PRAKASH BABU',	NULL,	'FN',	'1988-06-19',	'1998-07-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(254,	254,	'FRNW33',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'P SIVA KUMAR',	NULL,	'FN',	'1988-06-19',	'1999-11-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	NULL),
(255,	255,	'FRNW34',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'A ZACHARIAS',	NULL,	'FN',	'1988-06-19',	'1999-11-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(256,	256,	'FRNW38',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V ANANDHAN',	NULL,	'FN',	'1988-06-19',	'1999-11-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(257,	257,	'FRNW39',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K RAVI',	NULL,	'FN',	'1988-06-19',	'1999-11-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(258,	258,	'FRNW48',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R RAMA',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(259,	259,	'FRNW49',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R NATARAJU',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(260,	260,	'FRNW50',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M RAMACHANDRAN',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(261,	261,	'FRNW51',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'R VENKATESHAPPA',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(262,	262,	'FRNW52',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'K CHANDRA SEKAR',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(263,	263,	'FRNW53',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'B ANBU',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(264,	264,	'FRNW54',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'V RAJESH',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(265,	265,	'FRNW57',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'N M VENKATESH',	NULL,	'FN',	'1988-06-19',	'2006-05-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(266,	266,	'FRNW59',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'S MURUGESH BABU',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(267,	267,	'FRNW62',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M RAMESH',	NULL,	'FN',	'1988-06-19',	'2006-05-02',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(268,	268,	'FRNW65',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'M KRISHNAPPA',	NULL,	'FN',	'1988-06-19',	'1999-11-01',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL),
(269,	269,	'FRNW66',	13,	15,	19,	5,	6,	NULL,	NULL,	NULL,	'Bank of Baroda',	'Kanpur',	'2428100000790',	'Kanpur',	'BARB0DHILAD',	'AORTYP9341M',	'0',	'100082806749',	'779977895360',	1,	0,	NULL,	NULL,	'P K MURUGAN',	NULL,	'FN',	'1988-06-19',	'2000-07-17',	NULL,	'Male',	NULL,	NULL,	NULL,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'8780402839',	1,	0,	0,	1,	1,	NULL,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	NULL);

DROP TABLE IF EXISTS `employee_access_control`;
CREATE TABLE `employee_access_control` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee` int DEFAULT NULL,
  `department` int DEFAULT NULL,
  `device` int DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `device_employee_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_attendance`;
CREATE TABLE `employee_attendance` (
  `employee_attendance_id` int NOT NULL AUTO_INCREMENT,
  `finger_print_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` int NOT NULL,
  `face_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `work_shift_id` int DEFAULT '1',
  `in_out_time` datetime NOT NULL,
  `latitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uri` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inout_status` tinyint DEFAULT NULL COMMENT '0-in,1-out,2-in_only',
  `check_type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `verify_code` bigint DEFAULT NULL,
  `sensor_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `Memoinfo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `WorkCode` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sn` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `UserExtFmt` int DEFAULT NULL,
  `mechine_sl` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_attendance_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_attendance_approve`;
CREATE TABLE `employee_attendance_approve` (
  `employee_attendance_approve_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `finger_print_id` int NOT NULL,
  `date` date NOT NULL,
  `in_time` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `out_time` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_hour` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `approve_working_hour` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_attendance_approve_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_award`;
CREATE TABLE `employee_award` (
  `employee_award_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `award_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gift_item` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `month` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_award_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_bonus`;
CREATE TABLE `employee_bonus` (
  `employee_bonus_id` int unsigned NOT NULL,
  `bonus_setting_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `month` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_salary` int NOT NULL,
  `basic_salary` int NOT NULL,
  `bonus_amount` int NOT NULL,
  `tax` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_education_qualification`;
CREATE TABLE `employee_education_qualification` (
  `employee_education_qualification_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int unsigned NOT NULL,
  `institute` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `board_university` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `degree` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `result` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cgpa` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `passing_year` year NOT NULL,
  PRIMARY KEY (`employee_education_qualification_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employee_education_qualification` (`employee_education_qualification_id`, `employee_id`, `institute`, `board_university`, `degree`, `result`, `cgpa`, `created_at`, `updated_at`, `passing_year`) VALUES
(1,	230,	'Board',	'test univercity',	'Bsc',	'First class',	'6.8',	'2022-11-18 12:29:41',	'2022-11-18 12:29:41',	'2020');

DROP TABLE IF EXISTS `employee_experience`;
CREATE TABLE `employee_experience` (
  `employee_experience_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int unsigned NOT NULL,
  `organization_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `designation` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `skill` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `responsibility` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_experience_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employee_experience` (`employee_experience_id`, `employee_id`, `organization_name`, `designation`, `from_date`, `to_date`, `skill`, `responsibility`, `created_at`, `updated_at`) VALUES
(1,	230,	'test',	'training',	'2022-10-01',	'2022-11-30',	'test',	'test',	'2022-11-18 12:29:41',	'2022-11-18 12:29:41');

DROP TABLE IF EXISTS `employee_food_and_telephone_deductions`;
CREATE TABLE `employee_food_and_telephone_deductions` (
  `employee_food_and_telephone_deduction_id` int unsigned NOT NULL AUTO_INCREMENT,
  `month_of_deduction` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `finger_print_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` int NOT NULL,
  `food_allowance_deduction_rule_id` int NOT NULL DEFAULT '1',
  `telephone_allowance_deduction_rule_id` int NOT NULL DEFAULT '1',
  `call_consumed_per_month` int NOT NULL DEFAULT '0',
  `breakfast_count` int NOT NULL DEFAULT '0',
  `lunch_count` int NOT NULL DEFAULT '0',
  `dinner_count` int NOT NULL DEFAULT '0',
  `status` tinyint NOT NULL DEFAULT '1',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` tinyint DEFAULT NULL,
  `updated_by` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_food_and_telephone_deduction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employee_food_and_telephone_deductions` (`employee_food_and_telephone_deduction_id`, `month_of_deduction`, `finger_print_id`, `employee_id`, `food_allowance_deduction_rule_id`, `telephone_allowance_deduction_rule_id`, `call_consumed_per_month`, `breakfast_count`, `lunch_count`, `dinner_count`, `status`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(14,	'2022-11',	'ADM1001',	1,	1,	1,	0,	2,	2,	2,	1,	'NA',	1,	1,	'2022-12-21 16:37:06',	'2022-12-21 16:37:06'),
(15,	'2022-12',	'ROLL001',	254,	1,	1,	0,	10,	10,	10,	1,	'',	NULL,	NULL,	'2022-12-29 11:01:14',	'2022-12-29 11:01:14'),
(22,	'2022-12',	'ADM1001',	1,	1,	1,	0,	0,	0,	0,	1,	'NA',	1,	1,	'2022-12-30 10:14:03',	'2023-02-04 17:43:24');

DROP TABLE IF EXISTS `employee_overtime`;
CREATE TABLE `employee_overtime` (
  `employee_over_time_id` int unsigned NOT NULL,
  `date` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `work_shift_id` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `Overtime_duration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_performance`;
CREATE TABLE `employee_performance` (
  `employee_performance_id` int unsigned NOT NULL,
  `employee_id` int NOT NULL,
  `month` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_performance_details`;
CREATE TABLE `employee_performance_details` (
  `employee_performance_details_id` int unsigned NOT NULL,
  `employee_performance_id` int unsigned NOT NULL,
  `performance_criteria_id` int unsigned NOT NULL,
  `rating` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `employee_shift`;
CREATE TABLE `employee_shift` (
  `employee_shift_id` int unsigned NOT NULL AUTO_INCREMENT,
  `finger_print_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `month` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `d_1` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_2` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_3` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_4` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_5` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_6` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_7` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_8` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_9` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_10` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_11` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_12` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_13` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_14` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_15` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_16` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_17` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_18` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_19` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_20` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_21` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_22` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_23` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_24` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_25` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_26` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_27` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_28` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_29` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_30` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `d_31` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=268 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `employee_shift` (`employee_shift_id`, `finger_print_id`, `month`, `d_1`, `d_2`, `d_3`, `d_4`, `d_5`, `d_6`, `d_7`, `d_8`, `d_9`, `d_10`, `d_11`, `d_12`, `d_13`, `d_14`, `d_15`, `d_16`, `d_17`, `d_18`, `d_19`, `d_20`, `d_21`, `d_22`, `d_23`, `d_24`, `d_25`, `d_26`, `d_27`, `d_28`, `d_29`, `d_30`, `d_31`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1,	'ADM1001',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(2,	'MC006',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(3,	'MC005',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(4,	'MC004',	'2023-01',	NULL,	'3',	'3',	'3',	'3',	'3',	'3',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 15:33:15'),
(5,	'VC005',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(6,	'MC003',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(7,	'VC006',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(8,	'VC007',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(9,	'VC008',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(10,	'MC007',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(11,	'VC009',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(12,	'VC010',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(13,	'VC011',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(14,	'VC012',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(15,	'VC013',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(16,	'VC014',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(17,	'VC015',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(18,	'MC010',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(19,	'VC016',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(20,	'VC017',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(21,	'VC018',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(22,	'VC020',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(23,	'VC021',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(24,	'MC009',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(25,	'MC008',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(26,	'MC012',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(27,	'MC013',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(28,	'VC022',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(29,	'VC023',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(30,	'VC024',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(31,	'MC014',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(32,	'VC025',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(33,	'VC001',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(34,	'VC002',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(35,	'VC003',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(36,	'VC026',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(37,	'VC027',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(38,	'VC004',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(39,	'MC015',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(40,	'VC042',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(41,	'VC028',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(42,	'VC029',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(43,	'VC030',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(44,	'VC031',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(45,	'LS005',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(46,	'LS004',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(47,	'LS003',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(48,	'LS010',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(49,	'LS009',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(50,	'LS008',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(51,	'LS007',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(52,	'LS001',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(53,	'LS006',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(54,	'LS002',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(55,	'VC032',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(56,	'VC033',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(57,	'VC035',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(58,	'MC016',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(59,	'VC037',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(60,	'VC038',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(61,	'VC039',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(62,	'VC040',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(63,	'VC041',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(64,	'VC043',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(65,	'VC044',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(66,	'VC045',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(67,	'VC046',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(68,	'MC017',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(69,	'VC047',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(70,	'VC048',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(71,	'VC049',	'2023-01',	NULL,	'3',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 14:43:17'),
(72,	'MC001',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(73,	'VC050',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(74,	'VC051',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(75,	'VC052',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(76,	'VC053',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(77,	'VC054',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(78,	'VC055',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(79,	'LS011',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(80,	'VC056',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(81,	'MC022',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(82,	'VC057',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(83,	'VC058',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(84,	'VC059',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(85,	'VC060',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(86,	'VC093',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(87,	'VC061',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(88,	'MC031',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(89,	'VC087',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(90,	'VC063',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(91,	'VC095',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(92,	'VC064',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(93,	'LS012',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(94,	'VC065',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(95,	'VC066',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(96,	'VC067',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(97,	'VC068',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(98,	'MC029',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(99,	'MC024',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(100,	'VC071',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(101,	'VC072',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(102,	'VC074',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(103,	'MC002',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(104,	'VC104',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(105,	'LS013',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(106,	'VC094',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(107,	'MC025',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(108,	'VC102',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(109,	'VC076',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(110,	'MC026',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(111,	'VC083',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(112,	'VC089',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(113,	'VC097',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(114,	'VC118',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(115,	'VC019',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(116,	'VC084',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(117,	'VC101',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:43',	'2023-01-12 07:47:43'),
(118,	'VC100',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(119,	'VC092',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(120,	'VC121',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(121,	'VC148',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(122,	'VC099',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(123,	'VC150',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(124,	'VC120',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(125,	'VC078',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(126,	'VC090',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(127,	'VC107',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(128,	'MC027',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(129,	'MC028',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(130,	'VC079',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(131,	'VC081',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(132,	'VC123',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(133,	'VC088',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(134,	'VC069',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(135,	'VC091',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(136,	'VC119',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(137,	'VC108',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(138,	'VC082',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(139,	'VC034',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(140,	'VC126',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'3',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(141,	'LS014',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(142,	'VC073',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(143,	'VC106',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(144,	'VC122',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(145,	'VC096',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(146,	'VC116',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(147,	'VC077',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(148,	'VC070',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(149,	'VC103',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(150,	'VC098',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(151,	'VC086',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(152,	'VC085',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(153,	'VC105',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(154,	'VC062',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(155,	'MC032',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(156,	'MC023',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(157,	'VC132',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(158,	'VC139',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(159,	'VC145',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(160,	'VC143',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(161,	'VC144',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(162,	'VC154',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(163,	'MC033',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(164,	'VC161',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(165,	'VC140',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(166,	'VC141',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(167,	'VC149',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(168,	'VC131',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(169,	'VC152',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(170,	'VC151',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(171,	'VC146',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(172,	'VC147',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(173,	'VC142',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(174,	'VC127',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(175,	'VC133',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(176,	'VC155',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(177,	'MC018',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(178,	'MC019',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(179,	'MC020',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(180,	'MC021',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(181,	'VC115',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(182,	'VC114',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(183,	'VC112',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(184,	'VC111',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(185,	'VC075',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(186,	'VC0125',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(187,	'VC113',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(188,	'VC080',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(189,	'VC135',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(190,	'VC110',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(191,	'VC130',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(192,	'VC128',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(193,	'VC137',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(194,	'VC117',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(195,	'VC136',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(196,	'VC156',	'2023-01',	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-01-12 07:47:44',	'2023-01-12 07:47:44'),
(197,	'FRNFTE01',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(198,	'FRNFTE02',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(199,	'FRNFTE03',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(200,	'FRNFTE04',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(201,	'FRNFTE05',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(202,	'FRNFTE06',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(203,	'FRNFTE07',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(204,	'FRNFTE08',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(205,	'FRNFTE09',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(206,	'FRNFTE10',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(207,	'FRNFTE11',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(208,	'FRNFTE12',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(209,	'FRNFTE13',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(210,	'FRNFTE14',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(211,	'FRNFTE15',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(212,	'FRNFTE16',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(213,	'FRNFTE17',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(214,	'FRNFTE18',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(215,	'FRNFTE19',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(216,	'FRNFTE20',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(217,	'FRNFTE21',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(218,	'FRNS10',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(219,	'FRNS113',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(220,	'FRNS126',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(221,	'FRNS13',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(222,	'FRNS130',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(223,	'FRNS132',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(224,	'FRNS133',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(225,	'FRNS134',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(226,	'FRNS135',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(227,	'FRNS142',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(228,	'FRNS143',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(229,	'FRNS146',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(230,	'FRNS148',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(231,	'FRNS149',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:50',	'2023-02-24 12:15:50'),
(232,	'FRNS153',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(233,	'FRNS161',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(234,	'FRNS162',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(235,	'FRNS163',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(236,	'FRNS167',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(237,	'FRNS172',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(238,	'FRNS175',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(239,	'FRNS176',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(240,	'FRNS182',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(241,	'FRNS183',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(242,	'FRNS184',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(243,	'FRNS186',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(244,	'FRNS187',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(245,	'FRNW05',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(246,	'FRNW06',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(247,	'FRNW08',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(248,	'FRNW17',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(249,	'FRNW18',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(250,	'FRNW26',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(251,	'FRNW32',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(252,	'FRNW33',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(253,	'FRNW34',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(254,	'FRNW38',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(255,	'FRNW39',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(256,	'FRNW48',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(257,	'FRNW49',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(258,	'FRNW50',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(259,	'FRNW51',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(260,	'FRNW52',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(261,	'FRNW53',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(262,	'FRNW54',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(263,	'FRNW57',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(264,	'FRNW59',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(265,	'FRNW62',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(266,	'FRNW65',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51'),
(267,	'FRNW66',	'2023-02',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	'2',	'2',	'2',	'2',	NULL,	'2',	'2',	NULL,	NULL,	NULL,	NULL,	1,	1,	'2023-02-24 12:15:51',	'2023-02-24 12:15:51');

DROP TABLE IF EXISTS `food_allowance_deduction_rules`;
CREATE TABLE `food_allowance_deduction_rules` (
  `food_allowance_deduction_rule_id` int unsigned NOT NULL,
  `breakfast_cost` int NOT NULL,
  `lunch_cost` int NOT NULL,
  `dinner_cost` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `food_allowance_deduction_rules` (`food_allowance_deduction_rule_id`, `breakfast_cost`, `lunch_cost`, `dinner_cost`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1,	5,	10,	5,	1,	'None',	'2022-06-11 14:10:55',	'2022-12-30 05:49:34');

DROP TABLE IF EXISTS `front_settings`;
CREATE TABLE `front_settings` (
  `id` int unsigned NOT NULL,
  `company_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `home_page_big_title` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `short_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `about_us_image` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `footer_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `about_us_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_website` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `counter_1_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `counter_1_value` int NOT NULL,
  `counter_2_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `counter_2_value` int NOT NULL,
  `counter_3_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `counter_3_value` int NOT NULL,
  `counter_4_title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `counter_4_value` int NOT NULL,
  `show_job` tinyint DEFAULT '1',
  `show_service` tinyint DEFAULT '1',
  `show_about` tinyint DEFAULT '1',
  `show_contact` tinyint DEFAULT '1',
  `show_counter` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `front_settings` (`id`, `company_title`, `home_page_big_title`, `short_description`, `service_title`, `job_title`, `about_us_image`, `logo`, `footer_text`, `about_us_description`, `contact_website`, `contact_phone`, `contact_email`, `contact_address`, `counter_1_title`, `counter_1_value`, `counter_2_title`, `counter_2_value`, `counter_3_title`, `counter_3_value`, `counter_4_title`, `counter_4_value`, `show_job`, `show_service`, `show_about`, `show_contact`, `show_counter`, `created_at`, `updated_at`) VALUES
(1,	'Royex',	'Royex - HR and Payroll Management Software',	'Aenean eros et nisl sagittis as vestibulum at Nullam nulla eros ultricies site amet nonummy id imperdiet feugiat pede as Sed lectuse Donec mollis hendrerit Phasellus at nec sem in at pellentesque facilisis at Praesent congue erat at massa Sed sit cursus turpis vitae tortor that a Donec posuere as vulputate arcu Phasellus accumsan velit.\r\n\r\nMaecenas tempus tellus eget as that condimentum rhoncus sem quam semper libero amete adipiscing sem neque sed ipsum Nam quam nunce blandit at luctus pulvinar hendrerit id lorem Maecenas nec et ante tincidunt tempus.\r\n\r\nSed consequat leo eget bibendum sodales augue at velit cursus nunc.',	'Service We Provide',	'Start Your Career With US',	'about_us.webp',	'logo.png',	'© 2020 Royex by BDWEBTRICKS',	'Aenean eros et nisl sagittis as vestibulum at Nullam nulla eros ultricies site amet nonummy id imperdiet feugiat pede as Sed lectuse Donec mollis hendrerit Phasellus at nec sem in at pellentesque facilisis at Praesent congue erat at massa Sed sit cursus turpis vitae tortor that a Donec posuere as vulputate arcu Phasellus accumsan velit.\r\n\r\nMaecenas tempus tellus eget as that condimentum rhoncus sem quam semper libero amete adipiscing sem neque sed ipsum Nam quam nunce blandit at luctus pulvinar hendrerit id lorem Maecenas nec et ante tincidunt tempus.\r\n\r\nSed consequat leo eget bibendum sodales augue at velit cursus nunc.',	'https//:royexbd.com',	'0283932949',	'example@gmail.com',	'Royex LTd, 12005 NY',	'Project  Done',	120,	'Content Written',	220,	'Client',	200,	'Training',	230,	1,	1,	1,	1,	1,	'2020-09-23 10:43:29',	'2020-09-23 11:07:51');

DROP TABLE IF EXISTS `holiday`;
CREATE TABLE `holiday` (
  `holiday_id` int unsigned NOT NULL AUTO_INCREMENT,
  `holiday_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`holiday_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `holiday` (`holiday_id`, `holiday_name`, `created_at`, `updated_at`) VALUES
(6,	'Republic Day',	'2023-02-15 12:29:39',	'2023-02-15 12:29:39'),
(7,	'Ugadhi',	'2023-02-15 12:30:20',	'2023-02-15 12:30:20'),
(8,	'Telugu New Year',	'2023-02-15 12:30:29',	'2023-02-15 12:30:29'),
(9,	'Good Friday',	'2023-02-15 12:30:43',	'2023-02-15 12:30:43'),
(10,	'May Day',	'2023-02-15 12:30:55',	'2023-02-15 12:30:55'),
(11,	'Independence Day',	'2023-02-15 12:31:55',	'2023-02-15 12:31:55'),
(12,	'Gandhi Jayanthi',	'2023-02-15 12:32:15',	'2023-02-15 12:32:15'),
(13,	'Ayudhapooja',	'2023-02-15 12:32:27',	'2023-02-15 12:32:27'),
(14,	'Diwali',	'2023-02-15 12:32:40',	'2023-02-15 12:32:40'),
(15,	'Christmas',	'2023-02-15 12:32:56',	'2023-02-15 12:32:56'),
(16,	'Thiruvalluar Day',	'2023-02-15 12:34:29',	'2023-02-15 12:34:29');

DROP TABLE IF EXISTS `holiday_details`;
CREATE TABLE `holiday_details` (
  `holiday_details_id` int unsigned NOT NULL AUTO_INCREMENT,
  `holiday_id` int unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`holiday_details_id`),
  KEY `from_date` (`from_date`),
  KEY `to_date` (`to_date`),
  KEY `holiday_id` (`holiday_id`),
  KEY `holiday_details_id` (`holiday_details_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `holiday_details` (`holiday_details_id`, `holiday_id`, `from_date`, `to_date`, `comment`, `created_at`, `updated_at`) VALUES
(4,	16,	'2023-01-16',	'2023-01-16',	NULL,	'2023-02-15 12:36:40',	'2023-02-15 12:36:40'),
(5,	6,	'2023-01-26',	'2023-01-26',	NULL,	'2023-02-15 12:37:02',	'2023-02-15 12:37:02'),
(7,	7,	'2023-03-22',	'2023-03-22',	NULL,	'2023-02-15 12:37:42',	'2023-02-15 12:37:42'),
(8,	8,	'2023-03-23',	'2023-03-23',	NULL,	'2023-02-15 12:37:59',	'2023-02-15 12:37:59'),
(9,	9,	'2023-04-07',	'2023-04-07',	NULL,	'2023-02-15 12:38:33',	'2023-02-15 12:38:33'),
(10,	10,	'2023-05-01',	'2023-05-01',	NULL,	'2023-02-15 12:38:57',	'2023-02-15 12:38:57'),
(11,	11,	'2023-08-15',	'2023-08-15',	NULL,	'2023-02-15 12:39:21',	'2023-02-15 12:39:21'),
(12,	12,	'2023-10-02',	'2023-10-02',	NULL,	'2023-02-15 12:39:49',	'2023-02-15 12:39:49'),
(13,	13,	'2023-10-23',	'2023-10-23',	NULL,	'2023-02-15 12:41:36',	'2023-02-15 12:41:36'),
(14,	14,	'2023-11-13',	'2023-11-13',	NULL,	'2023-02-15 12:42:08',	'2023-02-15 12:42:08'),
(15,	15,	'2023-12-25',	'2023-12-25',	NULL,	'2023-02-15 12:42:35',	'2023-02-15 12:42:35');

DROP TABLE IF EXISTS `hourly_salaries`;
CREATE TABLE `hourly_salaries` (
  `hourly_salaries_id` int unsigned NOT NULL,
  `hourly_grade` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `hourly_rate` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `interview`;
CREATE TABLE `interview` (
  `interview_id` int unsigned NOT NULL,
  `job_applicant_id` int unsigned NOT NULL,
  `interview_date` date NOT NULL,
  `interview_time` time NOT NULL,
  `interview_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `ip_settings`;
CREATE TABLE `ip_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_status` tinyint NOT NULL DEFAULT '0' COMMENT '0 = not checking it 1 = checking ip',
  `status` tinyint NOT NULL COMMENT '0 = not providing employee self attendance 1 = providing',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ip_settings` (`id`, `ip_address`, `ip_status`, `status`, `created_at`, `updated_at`) VALUES
(1,	'127.0.0.1',	0,	0,	NULL,	'2022-11-14 12:54:53');

DROP TABLE IF EXISTS `job`;
CREATE TABLE `job` (
  `job_id` int unsigned NOT NULL,
  `job_title` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `post` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_end_date` date NOT NULL,
  `publish_date` date NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `job_applicant`;
CREATE TABLE `job_applicant` (
  `job_applicant_id` int unsigned NOT NULL,
  `job_id` int unsigned NOT NULL,
  `applicant_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `applicant_email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` int NOT NULL,
  `cover_letter` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attached_resume` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `application_date` date NOT NULL,
  `status` tinyint NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `leave_application`;
CREATE TABLE `leave_application` (
  `leave_application_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int unsigned NOT NULL,
  `leave_type_id` int unsigned NOT NULL,
  `application_from_date` date NOT NULL,
  `application_to_date` date NOT NULL,
  `application_date` date NOT NULL,
  `number_of_day` int NOT NULL,
  `approve_date` date DEFAULT NULL,
  `reject_date` date DEFAULT NULL,
  `approve_by` int DEFAULT NULL,
  `reject_by` int DEFAULT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'status(1,2,3) = Pending,Approve,Reject',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`leave_application_id`),
  KEY `leave_application_id` (`leave_application_id`),
  KEY `employee_id` (`employee_id`),
  KEY `leave_type_id` (`leave_type_id`),
  KEY `application_from_date` (`application_from_date`),
  KEY `application_to_date` (`application_to_date`),
  KEY `number_of_day` (`number_of_day`),
  KEY `application_date` (`application_date`),
  KEY `approve_by` (`approve_by`),
  KEY `reject_by` (`reject_by`),
  KEY `approve_date` (`approve_date`),
  KEY `reject_date` (`reject_date`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `leave_application` (`leave_application_id`, `employee_id`, `leave_type_id`, `application_from_date`, `application_to_date`, `application_date`, `number_of_day`, `approve_date`, `reject_date`, `approve_by`, `reject_by`, `purpose`, `remarks`, `status`, `created_at`, `updated_at`) VALUES
(1,	16,	1,	'2022-11-23',	'2022-11-23',	'2022-11-23',	1,	'2022-11-23',	NULL,	1,	NULL,	'test',	'test',	'2',	'2022-11-23 13:39:50',	'2022-11-23 13:39:58'),
(2,	1,	2,	'2022-11-24',	'2022-11-24',	'2022-11-23',	1,	'2022-11-23',	NULL,	1,	NULL,	'test',	'test',	'2',	'2022-11-23 13:40:14',	'2022-11-23 13:40:21'),
(3,	1,	4,	'2022-11-25',	'2022-11-25',	'2022-11-23',	1,	'2022-11-23',	NULL,	1,	NULL,	'test',	'test',	'2',	'2022-11-23 13:40:50',	'2022-11-23 13:40:57');

DROP TABLE IF EXISTS `leave_type`;
CREATE TABLE `leave_type` (
  `leave_type_id` int unsigned NOT NULL AUTO_INCREMENT,
  `leave_type_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `num_of_day` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`leave_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `leave_type` (`leave_type_id`, `leave_type_name`, `num_of_day`, `created_at`, `updated_at`) VALUES
(1,	'Casual Leave',	0,	'2022-06-11 14:10:50',	'2022-06-11 14:10:50'),
(2,	'Paid Leave',	0,	'2022-06-11 14:10:50',	'2022-06-11 14:10:50'),
(4,	'Sick Leave',	12,	'2022-06-11 14:10:50',	'2022-11-23 13:00:59');

DROP TABLE IF EXISTS `live_record_id`;
CREATE TABLE `live_record_id` (
  `live_id` int NOT NULL AUTO_INCREMENT,
  `ms_sql_id` int NOT NULL,
  `attendance_id` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`live_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `live_record_id` (`live_id`, `ms_sql_id`, `attendance_id`, `created_at`, `updated_at`) VALUES
(1,	0,	0,	'2022-11-16 14:45:56',	'2022-11-16 15:22:12');

DROP TABLE IF EXISTS `manual_attendance`;
CREATE TABLE `manual_attendance` (
  `primary_id` int NOT NULL AUTO_INCREMENT,
  `ID` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(11) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `device_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `devuid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`primary_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `manual_attendance` (`primary_id`, `ID`, `type`, `datetime`, `status`, `device_name`, `devuid`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(59,	'ADM1001',	'IN',	'2023-01-09 07:20:30',	0,	'Manual',	'Manual',	1,	1,	'2023-01-11 13:01:41',	'2023-01-11 13:01:41'),
(60,	'ADM1001',	'OUT',	'2023-01-09 16:20:30',	0,	'Manual',	'Manual',	1,	1,	'2023-01-11 13:01:41',	'2023-01-11 13:01:41'),
(67,	'ADM1001',	'IN',	'2023-01-10 20:00:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-11 13:37:27',	'2023-01-11 13:37:27'),
(68,	'ADM1001',	'OUT',	'2023-01-11 08:00:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-11 13:37:27',	'2023-01-11 13:37:27'),
(69,	'ADM1001',	'IN',	'2023-01-01 08:05:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 07:59:07',	'2023-01-12 07:59:07'),
(70,	'ADM1001',	'OUT',	'2023-01-01 20:00:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 07:59:07',	'2023-01-12 07:59:07'),
(79,	'VC001',	'IN',	'2022-09-25 07:27:39',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 15:23:23',	'2023-01-12 15:23:23'),
(80,	'VC001',	'OUT',	'2022-09-25 15:30:06',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 15:23:23',	'2023-01-12 15:23:23'),
(83,	'VC001',	'IN',	'2022-12-28 07:30:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 15:26:51',	'2023-01-12 15:26:51'),
(84,	'VC001',	'OUT',	'2022-12-28 16:00:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 15:26:51',	'2023-01-12 15:26:51'),
(85,	'ADM1001',	'IN',	'2023-01-12 07:30:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 20:12:51',	'2023-01-12 20:12:51'),
(86,	'ADM1001',	'OUT',	'2023-01-12 15:30:00',	0,	'Manual',	'Manual',	1,	1,	'2023-01-12 20:12:51',	'2023-01-12 20:12:51'),
(89,	'ADM1001',	'IN',	'2023-02-04 07:30:00',	0,	'Manual',	'Manual',	1,	1,	'2023-02-04 16:22:27',	'2023-02-04 16:22:27'),
(90,	'ADM1001',	'OUT',	'2023-02-04 13:30:00',	0,	'Manual',	'Manual',	1,	1,	'2023-02-04 16:22:27',	'2023-02-04 16:22:27'),
(91,	'ADM1001',	'IN',	'2023-02-11 07:15:00',	0,	'Manual',	'Manual',	1,	1,	'2023-02-11 13:31:48',	'2023-02-11 13:31:48'),
(92,	'ADM1001',	'OUT',	'2023-02-11 13:20:00',	0,	'Manual',	'Manual',	1,	1,	'2023-02-11 13:31:48',	'2023-02-11 13:31:48');

DROP TABLE IF EXISTS `menu_permission`;
CREATE TABLE `menu_permission` (
  `id` int unsigned NOT NULL,
  `role_id` int NOT NULL,
  `menu_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menu_permission` (`id`, `role_id`, `menu_id`) VALUES
(0,	2,	2),
(0,	2,	4),
(0,	2,	102),
(0,	2,	3),
(0,	2,	62),
(0,	2,	9),
(0,	2,	8),
(0,	2,	7),
(0,	2,	6),
(0,	2,	5),
(0,	2,	10),
(0,	2,	12),
(0,	2,	11),
(0,	2,	18),
(0,	2,	19),
(0,	2,	20),
(0,	2,	21),
(0,	2,	24),
(0,	2,	50),
(0,	2,	64),
(0,	2,	93),
(0,	2,	94),
(0,	2,	122),
(0,	3,	102),
(0,	3,	20),
(0,	3,	50),
(0,	5,	102),
(0,	5,	20),
(0,	5,	50),
(0,	1,	2),
(0,	1,	4),
(0,	1,	102),
(0,	1,	3),
(0,	1,	101),
(0,	1,	62),
(0,	1,	9),
(0,	1,	8),
(0,	1,	7),
(0,	1,	6),
(0,	1,	5),
(0,	1,	22),
(0,	1,	126),
(0,	1,	10),
(0,	1,	11),
(0,	1,	12),
(0,	1,	124),
(0,	1,	18),
(0,	1,	19),
(0,	1,	20),
(0,	1,	21),
(0,	1,	24),
(0,	1,	50),
(0,	1,	64),
(0,	1,	93),
(0,	1,	94),
(0,	1,	122),
(0,	1,	125),
(0,	1,	58),
(0,	1,	30),
(0,	1,	84),
(0,	1,	86),
(0,	1,	95),
(0,	1,	96),
(0,	1,	97),
(0,	1,	98),
(0,	1,	99),
(0,	1,	100),
(0,	1,	113),
(0,	1,	114),
(0,	1,	115),
(0,	1,	116),
(0,	1,	119),
(0,	1,	120),
(0,	1,	127);

DROP TABLE IF EXISTS `menus`;
CREATE TABLE `menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int NOT NULL DEFAULT '0',
  `action` int DEFAULT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `menu_url` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module_id` int NOT NULL,
  `status` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `menus` (`id`, `parent_id`, `action`, `name`, `menu_url`, `module_id`, `status`) VALUES
(1,	0,	NULL,	'User',	'user.index',	1,	2),
(2,	0,	NULL,	'Manage Role',	NULL,	1,	1),
(3,	2,	NULL,	'Add Role',	'userRole.index',	1,	1),
(4,	2,	NULL,	'Add Role Permission',	'rolePermission.index',	1,	1),
(5,	0,	NULL,	'Department',	'department.index',	2,	1),
(6,	0,	NULL,	'Sub Department',	'sub_department.index',	2,	1),
(7,	0,	NULL,	'Cost Center',	'costcenter.index',	2,	1),
(8,	0,	NULL,	'Branch',	'branch.index',	2,	1),
(9,	0,	NULL,	'Designation',	'designation.index',	2,	1),
(10,	0,	NULL,	'Setup',	NULL,	3,	1),
(11,	10,	NULL,	'Manage Holiday',	'holiday.index',	3,	1),
(12,	10,	NULL,	'Public Holiday',	'publicHoliday.index',	3,	1),
(13,	10,	NULL,	'Weekly Holiday',	'weeklyHoliday.index',	3,	0),
(14,	10,	NULL,	'Leave Type',	'leaveType.index',	3,	0),
(15,	0,	NULL,	'Leave Application',	NULL,	3,	0),
(16,	15,	NULL,	'Apply for Leave',	'applyForLeave.index',	3,	0),
(17,	15,	NULL,	'Requested Application',	'requestedApplication.index',	3,	0),
(18,	0,	NULL,	'Setup',	NULL,	4,	1),
(19,	18,	NULL,	'Manage Work Shift',	'workShift.index',	4,	1),
(20,	0,	NULL,	'Report',	NULL,	4,	1),
(21,	20,	NULL,	'Daily Attendance',	'dailyAttendance.dailyAttendance',	4,	1),
(22,	0,	NULL,	'Report',	NULL,	3,	1),
(23,	22,	NULL,	'Leave Report',	'leaveReport.leaveReport',	3,	0),
(24,	20,	NULL,	'Monthly Attendance',	'monthlyAttendance.monthlyAttendance',	4,	1),
(25,	0,	NULL,	'Report',	NULL,	5,	0),
(26,	25,	NULL,	'Daily OverTime Report',	'dailyOverTime.dailyOverTime',	5,	0),
(27,	25,	NULL,	'Monthly OverTime Report',	'monthlyOverTime.monthlyOverTime',	5,	0),
(28,	25,	NULL,	'My OverTime Report',	'myOverTimeReport.myOverTimeReport',	5,	0),
(29,	25,	NULL,	'OverTime Summary Report',	'overtimeSummaryReport.overtimeSummaryReport',	5,	0),
(30,	0,	NULL,	'Setup',	NULL,	6,	1),
(31,	30,	NULL,	'Tax Rule Setup',	'taxSetup.index',	6,	0),
(32,	0,	NULL,	'Allowance',	'allowance.index',	6,	0),
(33,	0,	NULL,	'Deduction',	'deduction.index',	6,	0),
(34,	0,	NULL,	'Advance Deduction',	'advanceDeduction.index',	6,	0),
(35,	0,	NULL,	'Paid Leave Application',	NULL,	3,	0),
(36,	0,	NULL,	'Monthly Pay Grade',	'payGrade.index',	6,	0),
(37,	0,	NULL,	'Hourly Pay Grade',	'hourlyWages.index',	6,	0),
(38,	0,	NULL,	'Salary Sheet',	NULL,	6,	0),
(39,	30,	NULL,	'Late Configration',	'salaryDeductionRule.index',	6,	0),
(40,	0,	NULL,	'Report',	NULL,	6,	0),
(41,	40,	NULL,	'Payment History',	'paymentHistory.paymentHistory',	6,	0),
(42,	40,	NULL,	'My Payroll',	'myPayroll.myPayroll',	6,	0),
(43,	0,	NULL,	'Performance Category',	'performanceCategory.index',	7,	0),
(44,	0,	NULL,	'Performance Criteria',	'performanceCriteria.index',	7,	0),
(45,	0,	NULL,	'Employee Performance',	'employeePerformance.index',	7,	0),
(46,	0,	NULL,	'Report',	NULL,	7,	0),
(47,	46,	NULL,	'Summary Report',	'performanceSummaryReport.performanceSummaryReport',	7,	0),
(48,	0,	NULL,	'Job Post',	'jobPost.index',	8,	0),
(49,	0,	NULL,	'Job Candidate',	'jobCandidate.index',	8,	0),
(50,	20,	NULL,	'My Attendance Report',	'myAttendanceReport.myAttendanceReport',	4,	1),
(51,	10,	NULL,	'Earn Leave Configure',	'earnLeaveConfigure.index',	3,	0),
(52,	0,	NULL,	'Training Type',	'trainingType.index',	9,	0),
(53,	0,	NULL,	'Training List',	'trainingInfo.index',	9,	0),
(54,	0,	NULL,	'Training Report',	'employeeTrainingReport.employeeTrainingReport',	9,	0),
(55,	0,	NULL,	'Award',	'award.index',	10,	0),
(56,	0,	NULL,	'Notice',	'notice.index',	11,	0),
(57,	0,	NULL,	'Settings',	'generalSettings.index',	12,	0),
(58,	0,	NULL,	'Manual Attendance',	'manualAttendance.manualAttendance',	4,	1),
(59,	22,	NULL,	'Summary Report',	'summaryReport.summaryReport',	3,	0),
(60,	22,	NULL,	'My Leave Report',	'myLeaveReport.myLeaveReport',	3,	0),
(61,	0,	NULL,	'Warning',	'warning.index',	2,	0),
(62,	0,	NULL,	'Manage Employee',	'employee.index',	2,	1),
(63,	0,	NULL,	'Promotion',	'promotion.index',	2,	0),
(64,	20,	NULL,	'Summary Report',	'attendanceSummaryReport.attendanceSummaryReport',	4,	1),
(65,	0,	NULL,	'Manage Work Hour',	NULL,	6,	0),
(66,	65,	NULL,	'Approve Work Hour',	'workHourApproval.create',	6,	0),
(67,	0,	NULL,	'Employee Permanent',	'permanent.index',	2,	0),
(68,	0,	NULL,	'Manage Bonus',	NULL,	6,	0),
(69,	68,	NULL,	'Bonus Setting',	'bonusSetting.index',	6,	0),
(70,	68,	NULL,	'Generate Bonus',	'generateBonus.index',	6,	0),
(71,	18,	NULL,	'Dashboard Attendance',	'attendance.dashboard',	4,	0),
(72,	0,	NULL,	'Front Setting',	NULL,	12,	0),
(73,	72,	NULL,	'General Setting',	'front.setting',	12,	0),
(74,	72,	NULL,	'Front Service',	'service.index',	12,	0),
(75,	38,	NULL,	'Generate Salary Sheet',	'generateSalarySheet.index',	6,	0),
(76,	38,	NULL,	'Download Payslip',	'downloadPayslip.payslip',	6,	0),
(77,	68,	NULL,	'Bonus Day',	'bonusday.index',	6,	0),
(78,	0,	NULL,	'Upload Attendance',	'uploadAttendance.uploadAttendance',	4,	0),
(79,	38,	NULL,	'Upload Salary Details',	'uploadSalaryDetails.uploadSalaryDetails',	6,	0),
(80,	0,	NULL,	'Paid Leave Report',	NULL,	3,	0),
(81,	80,	NULL,	'Leave Report',	'paidLeaveReport.paidLeaveReport',	3,	0),
(82,	80,	NULL,	'Summary Report',	'paidLeaveReport.paidLeaveSummaryReport',	3,	0),
(83,	10,	NULL,	'Paid Leave Configure',	'paidLeaveConfigure.index',	3,	0),
(84,	30,	NULL,	'Food Deductions Configure',	'foodDeductionConfigure.index',	6,	1),
(85,	30,	NULL,	'Telephone Deductions Configure',	'telephoneDeductionConfigure.index',	6,	0),
(86,	0,	NULL,	'Monthly Deductions',	'monthlyDeduction.monthlyDeduction',	6,	1),
(87,	18,	NULL,	'Configure Devices',	'deviceConfigure.index',	4,	0),
(88,	0,	NULL,	'Employee Access',	'access.index',	4,	0),
(90,	0,	NULL,	'Mobile Attendance',	'mobileAttendance.mobileAttendance',	4,	0),
(93,	20,	NULL,	'Muster Report',	'attendanceMusterReport.attendanceMusterReport',	4,	1),
(94,	0,	NULL,	'Attendance Record',	'attendanceRecord.attendanceRecord',	4,	1),
(95,	30,	NULL,	'Overtime Approval',	'overtimeApproval.overtimeApproval',	6,	1),
(96,	30,	NULL,	'Monthly WorkingDay',	'monthlyWorkingDay.index',	6,	1),
(97,	30,	NULL,	'Payroll Settings',	'payrollSettings.index',	6,	1),
(98,	0,	NULL,	'Salary',	NULL,	6,	1),
(99,	98,	NULL,	'Generation',	'wageSheet.generation',	6,	1),
(100,	98,	NULL,	'Manage Salary',	'salaryInfo.index',	6,	1),
(101,	0,	NULL,	'Termination',	'termination.index',	2,	1),
(102,	0,	NULL,	'Change Password',	'changePassword.index',	1,	1),
(113,	0,	NULL,	'Settlement',	NULL,	6,	1),
(114,	113,	NULL,	'Settlement Pending',	'settlementPendingInfo.pending',	6,	1),
(115,	0,	NULL,	'Report',	NULL,	6,	1),
(116,	115,	NULL,	'Salary Report',	'salaryReport.report',	6,	1),
(119,	115,	NULL,	'Settlement Report',	'settlementReport.report',	6,	1),
(120,	113,	NULL,	'Settlement Details',	'settlementInfo.index',	6,	1),
(122,	18,	NULL,	'Shift Details',	'shiftDetails.index',	4,	1),
(124,	10,	NULL,	'Company Holiday',	'companyHoliday.index',	3,	1),
(125,	0,	NULL,	'Calculate Attendance',	'calculateAttendance.calculateAttendance',	4,	1),
(126,	22,	NULL,	'EL Report',	'earnedLeave.index',	3,	1),
(127,	98,	NULL,	'Regenerate Payroll',	'regeneratePayroll.regeneratePayroll',	6,	1);

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(217,	'2017_09_09_085518_MenuPermissionMigration',	1),
(218,	'2017_09_10_080607_create_menus_table',	1),
(219,	'2017_09_13_095759_create_roles_table',	1),
(220,	'2017_09_19_030632_create_departments_table',	1),
(221,	'2017_09_19_043154_create_designations_table',	1),
(222,	'2017_09_19_053209_create_employees_table',	1),
(223,	'2017_09_19_060623_create_employee_experiences_table',	1),
(224,	'2017_09_19_062907_create_employee_education_qualifications_table',	1),
(225,	'2017_09_1_000000_create_users_table',	1),
(226,	'2017_09_27_033248_create_branches_table',	1),
(227,	'2017_09_2_081056_create_modules_table',	1),
(228,	'2017_10_02_042807_create_holidays_table',	1),
(229,	'2017_10_04_035502_create_holiday_details_table',	1),
(230,	'2017_10_04_050224_create_weekly_holidays_table',	1),
(231,	'2017_10_04_050517_create_leave_types_table',	1),
(232,	'2017_10_04_093455_create_leave_applications_table',	1),
(233,	'2017_10_05_094341_create_SP_weekly_holiday_store_procedure',	1),
(234,	'2017_10_05_095235_create_SP_get_holiday_store_procedure',	1),
(235,	'2017_10_05_095429_create_SP_get_employee_leave_balance_store_procedure',	1),
(236,	'2017_10_09_043228_create_work_shifts_table',	1),
(237,	'2017_10_09_074500_create_employee_attendances_table',	1),
(238,	'2017_10_09_095518_create_view_get_employee_in_out_data',	1),
(239,	'2017_10_11_051354_create_SP_daily_attendance_store_procedure',	1),
(240,	'2017_10_11_083952_create_SP_monthly_attendance_store_procedure',	1),
(241,	'2017_10_11_084031_create_allownce_table',	1),
(242,	'2017_10_11_084043_create_deduction_table',	1),
(243,	'2017_10_23_051619_create_pay_grades_table',	1),
(244,	'2017_10_26_064948_create_tax_rules_table',	1),
(245,	'2017_10_29_075627_create_pay_grade_to_allowances_table',	1),
(246,	'2017_10_29_075706_create_pay_grade_to_deductions_table',	1),
(247,	'2017_10_30_065329_create_SP_get_employee_info_store_procedure',	1),
(248,	'2017_11_01_045130_create_salary_deduction_for_late_attendances_table',	1),
(249,	'2017_11_02_051338_create_salary_details_table',	1),
(250,	'2017_11_02_053649_create_salary_details_to_allowances_table',	1),
(251,	'2017_11_02_054000_create_salary_details_to_deductions_table',	1),
(252,	'2017_11_07_042136_create_performance_categories_table',	1),
(253,	'2017_11_07_042334_create_performance_criterias_table',	1),
(254,	'2017_11_08_035959_create_employee_performances_table',	1),
(255,	'2017_11_08_040029_create_employee_performance_details_table',	1),
(256,	'2017_11_14_061231_create_earn_leave_rules_table',	1),
(257,	'2017_11_14_092829_create_company_address_settings_table',	1),
(258,	'2017_11_15_090514_create_employee_awards_table',	1),
(259,	'2017_11_15_105135_create_notices_table',	1),
(260,	'2017_11_23_102429_create_print_head_settings_table',	1),
(261,	'2017_12_03_112226_create_training_types_table',	1),
(262,	'2017_12_03_112805_create_training_infos_table',	1),
(263,	'2017_12_04_114921_create_warnings_table',	1),
(264,	'2017_12_04_140839_create_terminations_table',	1),
(265,	'2017_12_05_154824_create_promotions_table',	1),
(266,	'2017_12_10_122540_create_hourly_salaries_table',	1),
(267,	'2017_12_13_144211_create_jobs_table',	1),
(268,	'2017_12_13_144259_create_job_applicants_table',	1),
(269,	'2017_12_13_144320_create_interviews_table',	1),
(270,	'2017_12_31_222850_create_salary_details_to_leaves_table',	1),
(271,	'2018_01_08_144502_create_employee_attendance_approves_table',	1),
(272,	'2018_01_10_150238_create_bonus_settings_table',	1),
(273,	'2018_01_10_161034_create_employee_bonuses_table',	1),
(274,	'2020_07_18_212110_create_ip_settings_table',	1),
(275,	'2020_07_18_212205_create_white_listed_ips_table',	1),
(276,	'2020_09_21_065536_create_services_table',	1),
(277,	'2020_09_23_082756_create_front_settings_table',	1),
(278,	'2022_04_07_013826_create_employee_over_times_table',	1),
(279,	'2022_04_07_234411_create_SP_daily_over_time_store_procedure',	1),
(280,	'2022_04_07_234800_create_SP_monthly_over_time_store_procedure',	1),
(281,	'2022_04_20_232433_create_advance_deduction_table',	1),
(282,	'2022_04_21_002054_SP_department_daily_attendance_store_procedure',	1),
(283,	'2022_05_03_010955_create_paid_leave_applications_table',	1),
(284,	'2022_05_04_144800_create_paid_leave_rules_table',	1),
(285,	'2022_05_06_183305_create_food_allowance_deduction_rules_table',	1),
(286,	'2022_05_06_183433_create_telephone_allowance_deduction_rules_table',	1),
(287,	'2022_05_06_232208_create_employee_food_and_telephone_deductions_table',	1),
(288,	'2030_09_17_062133_KeyContstraintsMigration',	1);

DROP TABLE IF EXISTS `modules`;
CREATE TABLE `modules` (
  `id` int unsigned NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon_class` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `modules` (`id`, `name`, `icon_class`) VALUES
(1,	'Administration',	'mdi mdi-contacts'),
(2,	'Employee Management',	'mdi mdi-account-multiple-plus'),
(3,	'Leave Management',	'mdi mdi-format-line-weight'),
(4,	'Attendance',	'mdi mdi-clock-fast'),
(5,	'OverTime Report',	'mdi mdi-alarm-check'),
(6,	'Payroll',	'mdi mdi-cash'),
(7,	'Performance',	'mdi mdi-calculator'),
(8,	'Recruitment',	'mdi mdi-newspaper'),
(9,	'Training',	'mdi mdi-web'),
(10,	'Award',	'mdi mdi-trophy-variant'),
(11,	'Notice Board',	'mdi mdi-flag'),
(12,	'Settings',	'mdi mdi-settings');

DROP TABLE IF EXISTS `monthly_workingdays`;
CREATE TABLE `monthly_workingdays` (
  `working_id` int NOT NULL AUTO_INCREMENT,
  `year` int DEFAULT NULL,
  `jan` int DEFAULT NULL,
  `feb` int DEFAULT NULL,
  `mar` int DEFAULT NULL,
  `apr` int DEFAULT NULL,
  `may` int DEFAULT NULL,
  `jun` int DEFAULT NULL,
  `july` int DEFAULT NULL,
  `aug` int DEFAULT NULL,
  `sep` int DEFAULT NULL,
  `oct` int DEFAULT NULL,
  `nov` int DEFAULT NULL,
  `dec` int DEFAULT NULL,
  `payroll_month` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`working_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

INSERT INTO `monthly_workingdays` (`working_id`, `year`, `jan`, `feb`, `mar`, `apr`, `may`, `jun`, `july`, `aug`, `sep`, `oct`, `nov`, `dec`, `payroll_month`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1,	2022,	25,	24,	25,	26,	25,	26,	25,	26,	25,	26,	25,	26,	NULL,	'2022-12-28 02:28:12',	1,	'2022-12-30 11:30:40',	1),
(2,	2023,	26,	24,	26,	25,	26,	25,	26,	26,	25,	26,	25,	26,	NULL,	'2023-01-04 12:42:33',	1,	'2023-01-04 12:42:33',	1);

DROP TABLE IF EXISTS `ms_sql`;
CREATE TABLE `ms_sql` (
  `primary_id` int NOT NULL AUTO_INCREMENT,
  `local_primary_id` int DEFAULT NULL,
  `evtlguid` int DEFAULT NULL,
  `devdt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ID` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `punching_time` datetime DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `employee` int DEFAULT NULL,
  `device` int DEFAULT NULL,
  `device_employee_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `sms_log` text COLLATE utf8mb4_unicode_ci,
  `device_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `devuid` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_status` tinyint DEFAULT '0',
  `mobile_att` tinyint DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`primary_id`),
  KEY `primary_id` (`primary_id`),
  KEY `ID` (`ID`),
  KEY `datetime` (`datetime`),
  KEY `status` (`status`),
  KEY `updated_at` (`updated_at`),
  KEY `updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=34241 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `notice`;
CREATE TABLE `notice` (
  `notice_id` int unsigned NOT NULL,
  `title` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `publish_date` date NOT NULL,
  `attach_file` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `overtime_rules`;
CREATE TABLE `overtime_rules` (
  `overtime_rule_id` int unsigned NOT NULL AUTO_INCREMENT,
  `per_min` int NOT NULL,
  `amount_of_deduction` tinyint NOT NULL,
  `status` tinyint NOT NULL,
  `created_by` tinyint DEFAULT NULL,
  `updated_by` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`overtime_rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `overtime_rules` (`overtime_rule_id`, `per_min`, `amount_of_deduction`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1,	1,	1,	1,	NULL,	NULL,	'2022-12-20 18:44:11',	'2022-12-20 18:44:11');

DROP TABLE IF EXISTS `paid_leave_applications`;
CREATE TABLE `paid_leave_applications` (
  `paid_leave_application_id` int unsigned NOT NULL AUTO_INCREMENT,
  `leave_type_id` int unsigned NOT NULL,
  `employee_id` int unsigned NOT NULL,
  `application_from_date` date NOT NULL,
  `application_to_date` date NOT NULL,
  `application_date` date NOT NULL,
  `number_of_day` int NOT NULL,
  `approve_date` date DEFAULT NULL,
  `reject_date` date DEFAULT NULL,
  `approve_by` int DEFAULT NULL,
  `reject_by` int DEFAULT NULL,
  `purpose` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'status(1,2,3) = Pending,Approve,Reject',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`paid_leave_application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `paid_leave_rules`;
CREATE TABLE `paid_leave_rules` (
  `paid_leave_rule_id` int unsigned NOT NULL,
  `for_year` int NOT NULL,
  `day_of_paid_leave` double(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `paid_leave_rules` (`paid_leave_rule_id`, `for_year`, `day_of_paid_leave`, `created_at`, `updated_at`) VALUES
(1,	1,	0.00,	'2022-06-11 14:10:54',	'2022-12-25 10:16:28');

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `pay_grade`;
CREATE TABLE `pay_grade` (
  `pay_grade_id` int unsigned NOT NULL,
  `pay_grade_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gross_salary` int NOT NULL,
  `percentage_of_basic` int NOT NULL,
  `basic_salary` int NOT NULL,
  `overtime_rate` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pay_grade` (`pay_grade_id`, `pay_grade_name`, `gross_salary`, `percentage_of_basic`, `basic_salary`, `overtime_rate`, `created_at`, `updated_at`) VALUES
(1,	'A',	25000,	50,	12500,	101,	'2022-06-11 14:10:10',	'2022-12-22 13:36:14'),
(2,	'B',	10000,	50,	5000,	0,	'2022-06-11 14:10:10',	'2022-06-11 14:10:10');

DROP TABLE IF EXISTS `pay_grade_to_allowance`;
CREATE TABLE `pay_grade_to_allowance` (
  `pay_grade_to_allowance_id` int unsigned NOT NULL AUTO_INCREMENT,
  `pay_grade_id` int NOT NULL,
  `allowance_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pay_grade_to_allowance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pay_grade_to_allowance` (`pay_grade_to_allowance_id`, `pay_grade_id`, `allowance_id`, `created_at`, `updated_at`) VALUES
(1,	1,	2,	'2022-12-22 16:52:36',	'2022-12-22 16:52:36'),
(2,	1,	3,	'2022-12-22 16:52:36',	'2022-12-22 16:52:36');

DROP TABLE IF EXISTS `pay_grade_to_deduction`;
CREATE TABLE `pay_grade_to_deduction` (
  `pay_grade_to_deduction_id` int unsigned NOT NULL AUTO_INCREMENT,
  `pay_grade_id` int NOT NULL,
  `deduction_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`pay_grade_to_deduction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pay_grade_to_deduction` (`pay_grade_to_deduction_id`, `pay_grade_id`, `deduction_id`, `created_at`, `updated_at`) VALUES
(7,	1,	2,	'2022-12-22 16:52:36',	'2022-12-22 16:52:36'),
(8,	1,	3,	'2022-12-22 16:52:36',	'2022-12-22 16:52:36'),
(9,	1,	4,	'2022-12-22 16:52:36',	'2022-12-22 16:52:36');

DROP TABLE IF EXISTS `payroll`;
CREATE TABLE `payroll` (
  `payroll_id` int NOT NULL AUTO_INCREMENT,
  `employee` int DEFAULT NULL,
  `finger_print_id` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `fdate` date DEFAULT NULL,
  `tdate` date DEFAULT NULL,
  `month` int DEFAULT NULL,
  `year` int DEFAULT NULL,
  `unit` int DEFAULT NULL,
  `tom` int DEFAULT NULL,
  `service_provider` int DEFAULT NULL,
  `department` int DEFAULT NULL,
  `branch` int DEFAULT NULL,
  `costcenter` int DEFAULT NULL,
  `no_day_wages` decimal(10,2) DEFAULT NULL,
  `ph` decimal(10,2) DEFAULT NULL,
  `company_holiday` decimal(10,2) DEFAULT NULL,
  `total_days` decimal(10,2) DEFAULT NULL,
  `per_day_basic_da` decimal(10,2) DEFAULT NULL,
  `per_day_basic` decimal(10,2) DEFAULT NULL,
  `per_day_da` decimal(10,2) DEFAULT NULL,
  `per_day_hra` decimal(10,2) DEFAULT NULL,
  `per_day_wages` decimal(10,2) DEFAULT NULL,
  `basic_da_amount` decimal(10,2) DEFAULT NULL,
  `basic_amount` decimal(10,2) DEFAULT NULL,
  `da_amount` decimal(10,2) DEFAULT NULL,
  `hra_amount` decimal(10,2) DEFAULT NULL,
  `wages_amount` decimal(10,2) DEFAULT NULL,
  `attendance_bonus` decimal(10,2) DEFAULT NULL,
  `ot_hours` varchar(10) DEFAULT NULL,
  `ot_per_hours` decimal(10,2) DEFAULT NULL,
  `ot_amount` decimal(10,2) DEFAULT NULL,
  `gross_salary` decimal(10,2) DEFAULT NULL,
  `employee_pf` decimal(10,2) DEFAULT NULL,
  `employee_pf_percentage` decimal(10,2) DEFAULT NULL,
  `employee_esic` decimal(10,2) DEFAULT NULL,
  `employee_esic_percentage` decimal(10,2) DEFAULT NULL,
  `canteen` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) DEFAULT NULL,
  `employer_pf` decimal(10,2) DEFAULT NULL,
  `employer_pf_percentage` decimal(10,2) DEFAULT NULL,
  `employer_esic` decimal(10,2) DEFAULT NULL,
  `employer_esic_percentage` decimal(10,2) DEFAULT NULL,
  `service_charge_percentage` decimal(10,2) DEFAULT NULL,
  `service_charge` decimal(10,2) DEFAULT NULL,
  `bonus_percentage` decimal(10,2) DEFAULT NULL,
  `bonus_amount` decimal(10,2) DEFAULT NULL,
  `earned_leave_balance` decimal(10,2) DEFAULT NULL,
  `earned_leave` decimal(10,2) DEFAULT NULL,
  `leave_amount` decimal(10,2) DEFAULT NULL,
  `manhours` decimal(10,2) DEFAULT NULL,
  `manhours_amount` decimal(10,2) DEFAULT NULL,
  `manhour_days` decimal(10,2) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `retained_bonus` decimal(10,2) DEFAULT NULL,
  `retained_service_charge` decimal(10,2) DEFAULT NULL,
  `retained_attendance_bonus` decimal(10,2) DEFAULT NULL,
  `retained_leave_amount` decimal(10,2) DEFAULT NULL,
  `employer_total_deduction` decimal(10,2) DEFAULT NULL,
  `employee_total_deduction` decimal(10,2) DEFAULT NULL,
  `other_allowance` decimal(10,2) DEFAULT NULL,
  `other_deduction` decimal(10,2) DEFAULT NULL,
  `el_bonus` int DEFAULT '0',
  `lwf` decimal(10,2) DEFAULT NULL,
  `bank_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_branch` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_account_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_of_the_city` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`payroll_id`)
) ENGINE=InnoDB AUTO_INCREMENT=197 DEFAULT CHARSET=utf8mb3;

INSERT INTO `payroll` (`payroll_id`, `employee`, `finger_print_id`, `date`, `fdate`, `tdate`, `month`, `year`, `unit`, `tom`, `service_provider`, `department`, `branch`, `costcenter`, `no_day_wages`, `ph`, `company_holiday`, `total_days`, `per_day_basic_da`, `per_day_basic`, `per_day_da`, `per_day_hra`, `per_day_wages`, `basic_da_amount`, `basic_amount`, `da_amount`, `hra_amount`, `wages_amount`, `attendance_bonus`, `ot_hours`, `ot_per_hours`, `ot_amount`, `gross_salary`, `employee_pf`, `employee_pf_percentage`, `employee_esic`, `employee_esic_percentage`, `canteen`, `net_salary`, `employer_pf`, `employer_pf_percentage`, `employer_esic`, `employer_esic_percentage`, `service_charge_percentage`, `service_charge`, `bonus_percentage`, `bonus_amount`, `earned_leave_balance`, `earned_leave`, `leave_amount`, `manhours`, `manhours_amount`, `manhour_days`, `salary`, `status`, `created_by`, `updated_at`, `updated_by`, `created_at`, `retained_bonus`, `retained_service_charge`, `retained_attendance_bonus`, `retained_leave_amount`, `employer_total_deduction`, `employee_total_deduction`, `other_allowance`, `other_deduction`, `el_bonus`, `lwf`, `bank_name`, `bank_branch`, `bank_account_no`, `bank_of_the_city`) VALUES
(1,	1,	'ADM1001',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	6,	11,	2.00,	0.00,	0.00,	26.00,	377.73,	175.00,	202.73,	272.27,	650.00,	755.46,	350.00,	405.46,	544.54,	1300.00,	0.00,	'3:30',	162.50,	568.75,	1868.75,	90.66,	12.00,	14.02,	0.75,	0.00,	1764.07,	98.21,	13.00,	60.73,	3.25,	5.00,	0.00,	8.33,	62.93,	0.00,	0.00,	0.00,	19.00,	NULL,	2.00,	1772.74,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	158.94,	104.68,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(2,	4,	'MC006',	'2023-02-07',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	10.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	237.81,	611.00,	4105.09,	1875.06,	2230.03,	2615.91,	6721.00,	0.00,	'0:00',	152.75,	0.00,	6721.00,	492.61,	12.00,	50.41,	0.75,	0.00,	6177.98,	533.66,	13.00,	218.43,	3.25,	5.00,	336.05,	8.33,	341.95,	0.00,	0.00,	0.00,	83.00,	NULL,	10.00,	6646.91,	1,	1,	'2023-02-07 09:37:43',	1,	'2023-02-06 13:28:45',	341.95,	336.05,	0.00,	0.00,	752.09,	543.02,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(3,	5,	'MC005',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	18.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	161.73,	530.00,	7365.40,	3310.80,	4054.60,	3234.60,	10600.00,	0.00,	'0:00',	132.50,	0.00,	10600.00,	883.85,	12.00,	79.50,	0.75,	0.00,	9636.65,	957.50,	13.00,	344.50,	3.25,	5.00,	530.00,	8.33,	583.10,	0.00,	0.00,	0.00,	153.00,	NULL,	18.00,	10411.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1302.00,	963.35,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(4,	6,	'MC004',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	21.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	186.81,	560.00,	8583.37,	3920.58,	4662.79,	4296.63,	12880.00,	0.00,	'0:00',	140.00,	0.00,	12880.00,	1030.00,	12.00,	96.60,	0.75,	0.00,	11753.40,	1115.84,	13.00,	418.60,	3.25,	5.00,	644.00,	8.33,	583.10,	0.00,	1.05,	391.85,	179.00,	NULL,	21.00,	12964.51,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1534.44,	1126.60,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(5,	7,	'VC005',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	116.73,	485.00,	6628.86,	2979.72,	3649.14,	2101.14,	8730.00,	0.00,	'0:00',	121.25,	0.00,	8730.00,	795.46,	12.00,	65.48,	0.75,	0.00,	7869.06,	861.75,	13.00,	283.73,	3.25,	5.00,	436.50,	8.33,	552.18,	0.00,	0.00,	0.00,	133.00,	NULL,	16.00,	8573.20,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1145.48,	860.94,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(6,	8,	'MC003',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	14.00,	2.00,	0.00,	26.00,	424.20,	221.47,	202.73,	282.80,	707.00,	6787.20,	3543.52,	3243.68,	4524.80,	11312.00,	0.00,	'7:30',	176.75,	1325.63,	12637.63,	814.46,	12.00,	94.78,	0.75,	0.00,	11728.39,	882.34,	13.00,	410.72,	3.25,	5.00,	631.88,	8.33,	565.37,	0.00,	0.00,	0.00,	125.00,	NULL,	14.00,	12541.82,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1293.06,	909.24,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(7,	9,	'VC006',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	26.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	198.81,	572.00,	10449.32,	4772.88,	5676.44,	5566.68,	16016.00,	250.00,	'0:00',	143.00,	0.00,	16266.00,	1253.92,	12.00,	122.00,	0.75,	0.00,	14890.08,	1358.41,	13.00,	528.65,	3.25,	5.00,	813.30,	8.33,	583.10,	0.00,	1.30,	485.15,	220.00,	NULL,	26.00,	16260.49,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1887.06,	1375.92,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(8,	10,	'VC007',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	9.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	186.81,	560.00,	3731.90,	1704.60,	2027.30,	1868.10,	5600.00,	0.00,	'0:00',	140.00,	0.00,	5600.00,	447.83,	12.00,	42.00,	0.75,	0.00,	5110.17,	485.15,	13.00,	182.00,	3.25,	5.00,	280.00,	8.33,	310.87,	0.00,	0.00,	0.00,	75.00,	NULL,	9.00,	5523.72,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	667.15,	489.83,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(9,	11,	'VC008',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	228.81,	602.00,	8956.56,	4091.04,	4865.52,	5491.44,	14448.00,	0.00,	'0:00',	150.50,	0.00,	14448.00,	1074.79,	12.00,	108.36,	0.75,	0.00,	13264.85,	1164.35,	13.00,	469.56,	3.25,	5.00,	722.40,	8.33,	583.10,	0.00,	1.10,	410.51,	186.00,	NULL,	22.00,	14530.10,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1633.91,	1183.15,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(10,	12,	'MC007',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	22.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	126.73,	495.00,	8470.21,	3807.42,	4662.79,	2914.79,	11385.00,	0.00,	'0:00',	123.75,	0.00,	11385.00,	1016.43,	12.00,	85.39,	0.75,	0.00,	10283.18,	1101.13,	13.00,	370.01,	3.25,	5.00,	569.25,	8.33,	583.10,	0.00,	1.10,	405.10,	186.00,	NULL,	22.00,	11471.31,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1471.14,	1101.82,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(11,	13,	'VC009',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	136.73,	505.00,	5155.78,	2317.56,	2838.22,	1914.22,	7070.00,	0.00,	'0:00',	126.25,	0.00,	7070.00,	618.69,	12.00,	53.03,	0.75,	0.00,	6398.28,	670.25,	13.00,	229.78,	3.25,	5.00,	353.50,	8.33,	429.48,	0.00,	0.00,	0.00,	110.00,	NULL,	13.00,	6952.95,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	900.03,	671.72,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(12,	14,	'VC010',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	14.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	116.73,	485.00,	5524.05,	2483.10,	3040.95,	1750.95,	7275.00,	0.00,	'16:00',	121.25,	1940.00,	9215.00,	662.89,	12.00,	69.11,	0.75,	0.00,	8483.00,	718.13,	13.00,	299.49,	3.25,	5.00,	460.75,	8.33,	460.15,	0.00,	0.00,	0.00,	133.00,	NULL,	14.00,	9118.28,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1017.62,	732.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(13,	15,	'VC011',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	20.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	136.73,	505.00,	8101.94,	3641.88,	4460.06,	3008.06,	11110.00,	0.00,	'4:00',	126.25,	505.00,	11615.00,	972.23,	12.00,	87.11,	0.75,	0.00,	10555.66,	1053.25,	13.00,	377.49,	3.25,	5.00,	580.75,	8.33,	583.10,	0.00,	1.00,	368.27,	170.00,	NULL,	20.00,	11716.38,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1430.74,	1059.34,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(14,	16,	'VC012',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	106.73,	475.00,	8838.48,	3972.96,	4865.52,	2561.52,	11400.00,	0.00,	'1:00',	118.75,	118.75,	11518.75,	1060.62,	12.00,	86.39,	0.75,	0.00,	10371.74,	1149.00,	13.00,	374.36,	3.25,	5.00,	575.94,	8.33,	583.10,	0.00,	1.10,	405.10,	184.00,	NULL,	22.00,	11559.53,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1523.36,	1147.01,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(15,	17,	'VC013',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	118.54,	500.00,	9155.04,	4289.52,	4865.52,	2844.96,	12000.00,	0.00,	'0:00',	125.00,	0.00,	12000.00,	1098.60,	12.00,	90.00,	0.75,	0.00,	10811.40,	1190.16,	13.00,	390.00,	3.25,	5.00,	600.00,	8.33,	583.10,	0.00,	1.10,	419.61,	189.00,	NULL,	22.00,	12022.55,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1580.16,	1188.60,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(16,	18,	'VC014',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	231.81,	605.00,	9329.75,	4261.50,	5068.25,	5795.25,	15125.00,	0.00,	'3:30',	151.25,	529.38,	15654.38,	1119.57,	12.00,	117.41,	0.75,	0.00,	14417.40,	1212.87,	13.00,	508.77,	3.25,	5.00,	782.72,	8.33,	583.10,	0.00,	1.15,	429.17,	200.00,	NULL,	23.00,	15727.73,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1721.64,	1236.98,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(17,	19,	'VC015',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	136.73,	505.00,	4787.51,	2152.02,	2635.49,	1777.49,	6565.00,	0.00,	'0:00',	126.25,	0.00,	6565.00,	574.50,	12.00,	49.24,	0.75,	0.00,	5941.26,	622.38,	13.00,	213.36,	3.25,	5.00,	328.25,	8.33,	398.80,	0.00,	0.00,	0.00,	101.00,	NULL,	12.00,	6456.31,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	835.74,	623.74,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(18,	20,	'MC010',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	23.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	101.73,	470.00,	9206.75,	4138.50,	5068.25,	2543.25,	11750.00,	0.00,	'1:30',	117.50,	176.25,	11926.25,	1104.81,	12.00,	89.45,	0.75,	0.00,	10731.99,	1196.88,	13.00,	387.60,	3.25,	5.00,	596.31,	8.33,	583.10,	0.00,	1.15,	423.51,	196.00,	NULL,	23.00,	11944.69,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1584.48,	1194.26,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(19,	21,	'VC016',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	18.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	101.73,	470.00,	6997.13,	3145.26,	3851.87,	1932.87,	8930.00,	0.00,	'0:00',	117.50,	0.00,	8930.00,	839.66,	12.00,	66.98,	0.75,	0.00,	8023.36,	909.63,	13.00,	290.23,	3.25,	5.00,	446.50,	8.33,	582.86,	0.00,	0.00,	0.00,	151.00,	NULL,	18.00,	8759.50,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1199.86,	906.64,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(20,	22,	'VC017',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	81.73,	450.00,	4787.51,	2152.02,	2635.49,	1062.49,	5850.00,	0.00,	'0:00',	112.50,	0.00,	5850.00,	574.50,	12.00,	43.88,	0.75,	0.00,	5231.62,	622.38,	13.00,	190.13,	3.25,	5.00,	292.50,	8.33,	398.80,	0.00,	0.00,	0.00,	92.00,	NULL,	11.00,	5728.79,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	812.51,	618.38,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(21,	23,	'VC018',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	281.81,	655.00,	8583.37,	3920.58,	4662.79,	6481.63,	15065.00,	0.00,	'10:00',	163.75,	1637.50,	16702.50,	1030.00,	12.00,	125.27,	0.75,	0.00,	15547.23,	1115.84,	13.00,	542.83,	3.25,	5.00,	835.13,	8.33,	583.10,	0.00,	1.10,	410.51,	193.00,	NULL,	22.00,	16872.57,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1658.67,	1155.27,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(22,	24,	'VC020',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	7.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2558.22,	1139.11,	1419.11,	325.22,	2883.44,	0.00,	'0:00',	102.98,	0.00,	2883.44,	306.99,	12.00,	21.63,	0.75,	0.00,	2554.82,	332.57,	13.00,	93.71,	3.25,	5.00,	144.17,	8.33,	213.10,	0.00,	0.00,	0.00,	58.00,	NULL,	7.00,	2814.43,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	426.28,	328.62,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(23,	25,	'VC021',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	91.73,	460.00,	5155.78,	2317.56,	2838.22,	1284.22,	6440.00,	0.00,	'0:00',	115.00,	0.00,	6440.00,	618.69,	12.00,	48.30,	0.75,	0.00,	5773.01,	670.25,	13.00,	209.30,	3.25,	5.00,	322.00,	8.33,	429.48,	0.00,	0.00,	0.00,	101.00,	NULL,	12.00,	6311.93,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	879.55,	666.99,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(24,	26,	'MC009',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	18.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	83.65,	451.92,	7365.40,	3310.80,	4054.60,	1673.00,	9038.40,	0.00,	'1:30',	112.98,	169.47,	9207.87,	883.85,	12.00,	69.06,	0.75,	0.00,	8254.96,	957.50,	13.00,	299.26,	3.25,	5.00,	460.39,	8.33,	583.10,	0.00,	0.00,	0.00,	155.00,	NULL,	18.00,	8994.60,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1256.76,	952.91,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(25,	27,	'MC008',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	24.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	216.81,	590.00,	9702.94,	4431.96,	5270.98,	5637.06,	15340.00,	250.00,	'0:00',	147.50,	0.00,	15590.00,	1164.35,	12.00,	116.93,	0.75,	0.00,	14308.72,	1261.38,	13.00,	506.68,	3.25,	5.00,	779.50,	8.33,	583.10,	0.00,	1.20,	447.83,	204.00,	NULL,	24.00,	15632.37,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1768.06,	1281.28,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(26,	28,	'MC012',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	22.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	91.73,	460.00,	8838.48,	3972.96,	4865.52,	2201.52,	11040.00,	0.00,	'1:00',	115.00,	115.00,	11155.00,	1060.62,	12.00,	83.66,	0.75,	0.00,	10010.72,	1149.00,	13.00,	362.54,	3.25,	5.00,	557.75,	8.33,	583.10,	0.00,	1.10,	405.10,	186.00,	NULL,	22.00,	11189.41,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1511.54,	1144.28,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(27,	29,	'MC013',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	23.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	111.73,	480.00,	9206.75,	4138.50,	5068.25,	2793.25,	12000.00,	0.00,	'0:00',	120.00,	0.00,	12000.00,	1104.81,	12.00,	90.00,	0.75,	0.00,	10805.19,	1196.88,	13.00,	390.00,	3.25,	5.00,	600.00,	8.33,	583.10,	0.00,	1.15,	423.51,	195.00,	NULL,	23.00,	12019.73,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1586.88,	1194.81,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(28,	30,	'VC022',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	368.27,	165.54,	202.73,	91.73,	460.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	115.00,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(29,	31,	'VC023',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	106.73,	475.00,	5155.78,	2317.56,	2838.22,	1494.22,	6650.00,	0.00,	'4:00',	118.75,	475.00,	7125.00,	618.69,	12.00,	53.44,	0.75,	0.00,	6452.87,	670.25,	13.00,	231.56,	3.25,	5.00,	356.25,	8.33,	429.48,	0.00,	0.00,	0.00,	114.00,	NULL,	13.00,	7008.92,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	901.81,	672.13,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(30,	32,	'VC024',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	81.73,	450.00,	4419.24,	1986.48,	2432.76,	980.76,	5400.00,	0.00,	'8:00',	112.50,	900.00,	6300.00,	530.31,	12.00,	47.25,	0.75,	0.00,	5722.44,	574.50,	13.00,	204.75,	3.25,	5.00,	315.00,	8.33,	368.12,	0.00,	0.00,	0.00,	100.00,	NULL,	11.00,	6203.87,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	779.25,	577.56,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(31,	33,	'MC014',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	23.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	106.73,	475.00,	9206.75,	4138.50,	5068.25,	2668.25,	11875.00,	0.00,	'0:00',	118.75,	0.00,	11875.00,	1104.81,	12.00,	89.06,	0.75,	0.00,	10681.13,	1196.88,	13.00,	385.94,	3.25,	5.00,	593.75,	8.33,	583.10,	0.00,	1.15,	423.51,	195.00,	NULL,	23.00,	11892.54,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1582.82,	1193.87,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(32,	34,	'VC025',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	3.00,	0.00,	0.00,	26.00,	368.27,	165.54,	202.73,	91.73,	460.00,	1104.81,	496.62,	608.19,	275.19,	1380.00,	0.00,	'0:00',	115.00,	0.00,	1380.00,	132.58,	12.00,	10.35,	0.75,	0.00,	1237.07,	143.63,	13.00,	44.85,	3.25,	5.00,	69.00,	8.33,	92.03,	0.00,	0.00,	0.00,	25.00,	NULL,	3.00,	1352.55,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	188.48,	142.93,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(33,	35,	'VC001',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	26.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	79.54,	445.00,	10232.88,	4556.44,	5676.44,	2227.12,	12460.00,	250.00,	'1:30',	111.25,	166.88,	12876.88,	1227.95,	12.00,	96.58,	0.75,	0.00,	11552.35,	1330.27,	13.00,	418.50,	3.25,	5.00,	643.84,	8.33,	583.10,	0.00,	1.30,	475.10,	219.00,	NULL,	26.00,	12830.15,	1,	NULL,	'2023-02-06 13:28:45',	NULL,	'2023-02-06 13:28:45',	0.00,	0.00,	0.00,	0.00,	1748.77,	1324.53,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(34,	36,	'VC002',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	26.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	191.81,	565.00,	10449.32,	4772.88,	5676.44,	5370.68,	15820.00,	250.00,	'0:00',	141.25,	0.00,	16070.00,	1253.92,	12.00,	120.53,	0.75,	0.00,	14695.55,	1358.41,	13.00,	522.28,	3.25,	5.00,	803.50,	8.33,	583.10,	0.00,	1.30,	485.15,	219.00,	NULL,	26.00,	16061.06,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1880.69,	1374.45,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(35,	37,	'VC003',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	191.81,	565.00,	5224.66,	2386.44,	2838.22,	2685.34,	7910.00,	0.00,	'2:30',	141.25,	353.13,	8263.13,	626.96,	12.00,	61.97,	0.75,	0.00,	7574.20,	679.21,	13.00,	268.55,	3.25,	5.00,	413.16,	8.33,	435.21,	0.00,	0.00,	0.00,	115.00,	NULL,	13.00,	8163.74,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	947.76,	688.93,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(36,	38,	'VC026',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	116.73,	485.00,	5155.78,	2317.56,	2838.22,	1634.22,	6790.00,	0.00,	'10:30',	121.25,	1273.13,	8063.13,	618.69,	12.00,	60.47,	0.75,	0.00,	7383.97,	670.25,	13.00,	262.05,	3.25,	5.00,	403.16,	8.33,	429.48,	0.00,	0.00,	0.00,	121.00,	NULL,	13.00,	7963.47,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	932.30,	679.16,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(37,	39,	'VC027',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	21.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	76.73,	445.00,	8101.94,	3641.88,	4460.06,	1688.06,	9790.00,	0.00,	'0:00',	111.25,	0.00,	9790.00,	972.23,	12.00,	73.43,	0.75,	0.00,	8744.34,	1053.25,	13.00,	318.18,	3.25,	5.00,	489.50,	8.33,	583.10,	0.00,	1.05,	386.68,	179.00,	NULL,	21.00,	9877.85,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1371.43,	1045.66,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(38,	40,	'VC004',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	236.81,	610.00,	5224.66,	2386.44,	2838.22,	3315.34,	8540.00,	0.00,	'2:00',	152.50,	305.00,	8845.00,	626.96,	12.00,	66.34,	0.75,	0.00,	8151.70,	679.21,	13.00,	287.46,	3.25,	5.00,	442.25,	8.33,	435.21,	0.00,	0.00,	0.00,	110.00,	NULL,	13.00,	8755.79,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	966.67,	693.30,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(39,	41,	'MC015',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	15.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	121.73,	490.00,	5892.32,	2648.64,	3243.68,	1947.68,	7840.00,	0.00,	'16:00',	122.50,	1960.00,	9800.00,	707.08,	12.00,	73.50,	0.75,	0.00,	9019.42,	766.00,	13.00,	318.50,	3.25,	5.00,	490.00,	8.33,	490.83,	0.00,	0.00,	0.00,	142.00,	NULL,	15.00,	9696.33,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1084.50,	780.58,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(40,	42,	'VC042',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	8771.04,	3905.52,	4865.52,	1835.04,	10606.08,	0.00,	'29:00',	110.48,	3203.92,	13810.00,	1052.52,	12.00,	103.58,	0.75,	0.00,	12653.90,	1140.24,	13.00,	448.83,	3.25,	5.00,	690.50,	8.33,	583.10,	0.00,	1.10,	402.01,	214.00,	NULL,	22.00,	13896.54,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1589.07,	1156.10,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(41,	43,	'VC028',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	73.65,	441.92,	6628.86,	2979.72,	3649.14,	1325.70,	7954.56,	0.00,	'0:00',	110.48,	0.00,	7954.56,	795.46,	12.00,	59.66,	0.75,	0.00,	7099.44,	861.75,	13.00,	258.52,	3.25,	5.00,	397.73,	8.33,	552.18,	0.00,	0.00,	0.00,	136.00,	NULL,	16.00,	7784.20,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1120.27,	855.12,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(42,	44,	'VC029',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	121.73,	490.00,	5155.78,	2317.56,	2838.22,	1704.22,	6860.00,	0.00,	'12:30',	122.50,	1531.25,	8391.25,	618.69,	12.00,	62.93,	0.75,	0.00,	7709.63,	670.25,	13.00,	272.72,	3.25,	5.00,	419.56,	8.33,	429.48,	0.00,	0.00,	0.00,	122.00,	NULL,	13.00,	8297.32,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	942.97,	681.62,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(43,	45,	'VC030',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	2.00,	0.00,	26.00,	373.19,	170.46,	202.73,	466.81,	840.00,	9329.75,	4261.50,	5068.25,	11670.25,	21000.00,	0.00,	'1:00',	210.00,	210.00,	21210.00,	1119.57,	12.00,	0.00,	0.75,	0.00,	20090.43,	1212.87,	13.00,	0.00,	3.25,	5.00,	1060.50,	8.33,	583.10,	0.00,	1.15,	429.17,	196.00,	NULL,	23.00,	22069.90,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1212.87,	1119.57,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(44,	46,	'VC031',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	121.73,	490.00,	8838.48,	3972.96,	4865.52,	2921.52,	11760.00,	0.00,	'0:00',	122.50,	0.00,	11760.00,	1060.62,	12.00,	88.20,	0.75,	0.00,	10611.18,	1149.00,	13.00,	382.20,	3.25,	5.00,	588.00,	8.33,	583.10,	0.00,	1.10,	405.10,	188.00,	NULL,	22.00,	11805.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1531.20,	1148.82,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(45,	47,	'LS005',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	25.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	111.73,	480.00,	9943.29,	4469.58,	5473.71,	3016.71,	12960.00,	250.00,	'0:00',	120.00,	0.00,	13210.00,	1193.19,	12.00,	99.08,	0.75,	0.00,	11917.73,	1292.63,	13.00,	429.33,	3.25,	5.00,	660.50,	8.33,	583.10,	0.00,	1.25,	460.34,	210.00,	NULL,	25.00,	13191.98,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1721.96,	1292.27,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(46,	48,	'LS004',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	23.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	56.73,	425.00,	9206.75,	4138.50,	5068.25,	1418.25,	10625.00,	0.00,	'0:00',	106.25,	0.00,	10625.00,	1104.81,	12.00,	79.69,	0.75,	0.00,	9440.50,	1196.88,	13.00,	345.31,	3.25,	5.00,	531.25,	8.33,	583.10,	0.00,	1.15,	423.51,	192.00,	NULL,	23.00,	10620.67,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1542.19,	1184.50,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(47,	49,	'LS003',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	26.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	86.73,	455.00,	10311.56,	4635.12,	5676.44,	2428.44,	12740.00,	250.00,	'0:00',	113.75,	0.00,	12990.00,	1237.39,	12.00,	97.43,	0.75,	0.00,	11655.18,	1340.50,	13.00,	422.18,	3.25,	5.00,	649.50,	8.33,	583.10,	0.00,	1.30,	478.75,	219.00,	NULL,	26.00,	12938.67,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1762.68,	1334.82,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(48,	50,	'LS010',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	24.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	56.73,	425.00,	9575.02,	4304.04,	5270.98,	1474.98,	11050.00,	250.00,	'0:00',	106.25,	0.00,	11300.00,	1149.00,	12.00,	84.75,	0.75,	0.00,	10066.25,	1244.75,	13.00,	367.25,	3.25,	5.00,	565.00,	8.33,	583.10,	0.00,	1.20,	441.92,	201.00,	NULL,	24.00,	11278.02,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1612.00,	1233.75,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(49,	51,	'LS009',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	25.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	56.73,	425.00,	9943.29,	4469.58,	5473.71,	1531.71,	11475.00,	250.00,	'0:00',	106.25,	0.00,	11725.00,	1193.19,	12.00,	87.94,	0.75,	0.00,	10443.87,	1292.63,	13.00,	381.06,	3.25,	5.00,	586.25,	8.33,	583.10,	0.00,	1.25,	460.34,	211.00,	NULL,	25.00,	11681.00,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1673.69,	1281.13,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(50,	52,	'LS008',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	18.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	56.73,	425.00,	7365.40,	3310.80,	4054.60,	1134.60,	8500.00,	0.00,	'0:00',	106.25,	0.00,	8500.00,	883.85,	12.00,	63.75,	0.75,	0.00,	7552.40,	957.50,	13.00,	276.25,	3.25,	5.00,	425.00,	8.33,	583.10,	0.00,	0.00,	0.00,	151.00,	NULL,	18.00,	8274.35,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1233.75,	947.60,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(51,	53,	'LS007',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	24.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	56.73,	425.00,	9575.02,	4304.04,	5270.98,	1474.98,	11050.00,	250.00,	'0:00',	106.25,	0.00,	11300.00,	1149.00,	12.00,	84.75,	0.75,	0.00,	10066.25,	1244.75,	13.00,	367.25,	3.25,	5.00,	565.00,	8.33,	583.10,	0.00,	1.20,	441.92,	202.00,	NULL,	24.00,	11278.02,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1612.00,	1233.75,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(52,	54,	'LS001',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	24.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	100.73,	469.00,	9575.02,	4304.04,	5270.98,	2618.98,	12194.00,	250.00,	'0:00',	117.25,	0.00,	12444.00,	1149.00,	12.00,	93.33,	0.75,	0.00,	11201.67,	1244.75,	13.00,	404.43,	3.25,	5.00,	622.20,	8.33,	583.10,	0.00,	1.20,	441.92,	203.00,	NULL,	24.00,	12442.04,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1649.18,	1242.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(53,	55,	'LS006',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	23.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	56.46,	421.92,	9136.50,	4068.25,	5068.25,	1411.50,	10548.00,	0.00,	'0:00',	105.48,	0.00,	10548.00,	1096.38,	12.00,	79.11,	0.75,	0.00,	9372.51,	1187.75,	13.00,	342.81,	3.25,	5.00,	527.40,	8.33,	583.10,	0.00,	1.15,	420.28,	195.00,	NULL,	23.00,	10548.22,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1530.56,	1175.49,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(54,	56,	'LS002',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	24.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	100.73,	469.00,	9575.02,	4304.04,	5270.98,	2618.98,	12194.00,	250.00,	'0:00',	117.25,	0.00,	12444.00,	1149.00,	12.00,	93.33,	0.75,	0.00,	11201.67,	1244.75,	13.00,	404.43,	3.25,	5.00,	622.20,	8.33,	583.10,	0.00,	1.20,	441.92,	202.00,	NULL,	24.00,	12442.04,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1649.18,	1242.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(55,	57,	'VC032',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	66.46,	431.92,	9136.50,	4068.25,	5068.25,	1661.50,	10798.00,	0.00,	'0:00',	107.98,	0.00,	10798.00,	1096.38,	12.00,	80.99,	0.75,	0.00,	9620.63,	1187.75,	13.00,	350.94,	3.25,	5.00,	539.90,	8.33,	583.10,	0.00,	1.15,	420.28,	196.00,	NULL,	23.00,	10802.59,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1538.69,	1177.37,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(56,	58,	'VC033',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	111.73,	480.00,	6628.86,	2979.72,	3649.14,	2011.14,	8640.00,	0.00,	'2:00',	120.00,	240.00,	8880.00,	795.46,	12.00,	66.60,	0.75,	0.00,	8017.94,	861.75,	13.00,	288.60,	3.25,	5.00,	444.00,	8.33,	552.18,	0.00,	0.00,	0.00,	136.00,	NULL,	16.00,	8725.83,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1150.35,	862.06,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(57,	59,	'VC035',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	26.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	10232.88,	4556.44,	5676.44,	1300.88,	11533.76,	250.00,	'0:00',	102.98,	0.00,	11783.76,	1227.95,	12.00,	88.38,	0.75,	0.00,	10467.43,	1330.27,	13.00,	382.97,	3.25,	5.00,	589.19,	8.33,	583.10,	0.00,	1.30,	475.10,	219.00,	NULL,	26.00,	11717.91,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1713.24,	1316.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(58,	60,	'MC016',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	66.46,	431.92,	9501.96,	4230.98,	5270.98,	1727.96,	11229.92,	250.00,	'2:30',	107.98,	269.95,	11749.87,	1140.24,	12.00,	88.12,	0.75,	0.00,	10521.51,	1235.25,	13.00,	381.87,	3.25,	5.00,	587.49,	8.33,	583.10,	0.00,	1.20,	438.55,	207.00,	NULL,	24.00,	11741.89,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1617.12,	1228.36,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(59,	61,	'VC037',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	8771.04,	3905.52,	4865.52,	1835.04,	10606.08,	0.00,	'0:00',	110.48,	0.00,	10606.08,	1052.52,	12.00,	79.55,	0.75,	0.00,	9474.01,	1140.24,	13.00,	344.70,	3.25,	5.00,	530.30,	8.33,	583.10,	0.00,	1.10,	402.01,	187.00,	NULL,	22.00,	10636.55,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1484.94,	1132.07,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(60,	62,	'VC038',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	21.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	194.46,	559.92,	8405.58,	3742.79,	4662.79,	4472.58,	12878.16,	0.00,	'0:00',	139.98,	0.00,	12878.16,	1008.67,	12.00,	96.59,	0.75,	0.00,	11772.90,	1092.73,	13.00,	418.54,	3.25,	5.00,	643.91,	8.33,	583.10,	0.00,	1.05,	383.73,	177.00,	NULL,	21.00,	12977.63,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1511.27,	1105.26,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(61,	63,	'VC039',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	26.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	10232.88,	4556.44,	5676.44,	2140.88,	12373.76,	250.00,	'0:00',	110.48,	0.00,	12623.76,	1227.95,	12.00,	94.68,	0.75,	0.00,	11301.13,	1330.27,	13.00,	410.27,	3.25,	5.00,	631.19,	8.33,	583.10,	0.00,	1.30,	475.10,	220.00,	NULL,	26.00,	12572.61,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1740.54,	1322.63,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(62,	64,	'VC040',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	5847.36,	2603.68,	3243.68,	1223.36,	7070.72,	0.00,	'41:30',	110.48,	4584.92,	11655.64,	701.68,	12.00,	87.42,	0.75,	0.00,	10866.54,	760.16,	13.00,	378.81,	3.25,	5.00,	582.78,	8.33,	487.09,	0.00,	0.00,	0.00,	173.00,	NULL,	16.00,	11586.54,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1138.97,	789.10,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(63,	65,	'VC041',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	121.73,	490.00,	9206.75,	4138.50,	5068.25,	3043.25,	12250.00,	0.00,	'1:30',	122.50,	183.75,	12433.75,	1104.81,	12.00,	93.25,	0.75,	0.00,	11235.69,	1196.88,	13.00,	404.10,	3.25,	5.00,	621.69,	8.33,	583.10,	0.00,	1.15,	423.51,	201.00,	NULL,	23.00,	12461.07,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1600.98,	1198.06,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(64,	66,	'VC043',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	466.81,	840.00,	9329.75,	4261.50,	5068.25,	11670.25,	21000.00,	0.00,	'15:00',	210.00,	3150.00,	24150.00,	1119.57,	12.00,	0.00,	0.75,	0.00,	23030.43,	1212.87,	13.00,	0.00,	3.25,	5.00,	1207.50,	8.33,	583.10,	0.00,	1.20,	447.83,	218.00,	NULL,	24.00,	25175.56,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1212.87,	1119.57,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(65,	67,	'VC044',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	224.54,	590.00,	6212.82,	2766.41,	3446.41,	3817.18,	10030.00,	0.00,	'14:30',	147.50,	2138.75,	12168.75,	745.54,	12.00,	91.27,	0.75,	0.00,	11331.94,	807.67,	13.00,	395.48,	3.25,	5.00,	608.44,	8.33,	517.53,	0.00,	0.00,	0.00,	149.00,	NULL,	16.00,	12091.57,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1203.15,	836.81,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(66,	68,	'VC045',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	8405.58,	3742.79,	4662.79,	1068.58,	9474.16,	0.00,	'1:00',	102.98,	102.98,	9577.14,	1008.67,	12.00,	71.83,	0.75,	0.00,	8496.64,	1092.73,	13.00,	311.26,	3.25,	5.00,	478.86,	8.33,	583.10,	0.00,	1.10,	402.01,	191.00,	NULL,	22.00,	9637.12,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1403.99,	1080.50,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(67,	69,	'VC046',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	14.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	234.54,	600.00,	5847.36,	2603.68,	3243.68,	3752.64,	9600.00,	0.00,	'0:00',	150.00,	0.00,	9600.00,	701.68,	12.00,	72.00,	0.75,	0.00,	8826.32,	760.16,	13.00,	312.00,	3.25,	5.00,	480.00,	8.33,	487.09,	0.00,	0.00,	0.00,	117.00,	NULL,	14.00,	9494.93,	1,	NULL,	'2023-02-06 13:28:46',	NULL,	'2023-02-06 13:28:46',	0.00,	0.00,	0.00,	0.00,	1072.16,	773.68,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(68,	70,	'MC017',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	86.46,	451.92,	8771.04,	3905.52,	4865.52,	2075.04,	10846.08,	0.00,	'0:00',	112.98,	0.00,	10846.08,	1052.52,	12.00,	81.35,	0.75,	0.00,	9712.21,	1140.24,	13.00,	352.50,	3.25,	5.00,	542.30,	8.33,	583.10,	0.00,	1.10,	402.01,	186.00,	NULL,	22.00,	10880.75,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1492.74,	1133.87,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(69,	71,	'VC047',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	25.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	9501.96,	4230.98,	5270.98,	1987.96,	11489.92,	250.00,	'0:00',	110.48,	0.00,	11739.92,	1140.24,	12.00,	88.05,	0.75,	0.00,	10511.63,	1235.25,	13.00,	381.55,	3.25,	5.00,	587.00,	8.33,	583.10,	0.00,	1.25,	456.83,	212.00,	NULL,	25.00,	11750.05,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1616.80,	1228.29,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(70,	72,	'VC048',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	84.54,	450.00,	9136.50,	4068.25,	5068.25,	2113.50,	11250.00,	0.00,	'11:00',	112.50,	1237.50,	12487.50,	1096.38,	12.00,	93.66,	0.75,	0.00,	11297.46,	1187.75,	13.00,	405.84,	3.25,	5.00,	624.38,	8.33,	583.10,	0.00,	1.15,	420.28,	203.00,	NULL,	23.00,	12521.67,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1593.59,	1190.04,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(71,	73,	'VC049',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	8.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	3289.14,	1464.57,	1824.57,	688.14,	3977.28,	0.00,	'17:30',	110.48,	1933.40,	5910.68,	394.70,	12.00,	44.33,	0.75,	0.00,	5471.65,	427.59,	13.00,	192.10,	3.25,	5.00,	295.53,	8.33,	273.99,	0.00,	0.00,	0.00,	84.00,	NULL,	8.00,	5860.51,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	619.69,	439.03,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(72,	74,	'MC001',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	6.00,	0.00,	0.00,	26.00,	402.54,	199.81,	202.73,	194.46,	597.00,	2415.24,	1198.86,	1216.38,	1166.76,	3582.00,	0.00,	'0:00',	149.25,	0.00,	3582.00,	289.83,	12.00,	26.87,	0.75,	0.00,	3265.30,	313.98,	13.00,	116.42,	3.25,	5.00,	179.10,	8.33,	201.19,	0.00,	0.00,	0.00,	50.00,	NULL,	6.00,	3531.89,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	430.40,	316.70,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(73,	75,	'VC050',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	14.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	124.54,	490.00,	5847.36,	2603.68,	3243.68,	1992.64,	7840.00,	0.00,	'3:00',	122.50,	367.50,	8207.50,	701.68,	12.00,	61.56,	0.75,	0.00,	7444.26,	760.16,	13.00,	266.74,	3.25,	5.00,	410.38,	8.33,	487.09,	0.00,	0.00,	0.00,	121.00,	NULL,	14.00,	8078.07,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1026.90,	763.24,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(74,	76,	'VC051',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9501.96,	4230.98,	5270.98,	1207.96,	10709.92,	250.00,	'0:00',	102.98,	0.00,	10959.92,	1140.24,	12.00,	82.20,	0.75,	0.00,	9737.48,	1235.25,	13.00,	356.20,	3.25,	5.00,	548.00,	8.33,	583.10,	0.00,	1.20,	438.55,	204.00,	NULL,	24.00,	10938.12,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1591.45,	1222.44,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(75,	77,	'VC052',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	4385.52,	1952.76,	2432.76,	917.52,	5303.04,	0.00,	'6:30',	110.48,	718.12,	6021.16,	526.26,	12.00,	45.16,	0.75,	0.00,	5449.74,	570.12,	13.00,	195.69,	3.25,	5.00,	301.06,	8.33,	365.31,	0.00,	0.00,	0.00,	99.00,	NULL,	11.00,	5921.72,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	765.81,	571.42,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(76,	78,	'VC053',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	4385.52,	1952.76,	2432.76,	917.52,	5303.04,	0.00,	'11:30',	110.48,	1270.52,	6573.56,	526.26,	12.00,	49.30,	0.75,	0.00,	5998.00,	570.12,	13.00,	213.64,	3.25,	5.00,	328.68,	8.33,	365.31,	0.00,	0.00,	0.00,	103.00,	NULL,	11.00,	6483.79,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	783.76,	575.56,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(77,	79,	'VC054',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	86.46,	451.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	112.98,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(78,	80,	'VC055',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	10.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	4020.06,	1790.03,	2230.03,	841.06,	4861.12,	0.00,	'4:00',	110.48,	441.92,	5303.04,	482.41,	12.00,	39.77,	0.75,	0.00,	4780.86,	522.61,	13.00,	172.35,	3.25,	5.00,	265.15,	8.33,	334.87,	0.00,	0.00,	0.00,	88.00,	NULL,	10.00,	5208.10,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	694.96,	522.18,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(79,	81,	'LS011',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	56.46,	421.92,	9501.96,	4230.98,	5270.98,	1467.96,	10969.92,	250.00,	'0:00',	105.48,	0.00,	11219.92,	1140.24,	12.00,	84.15,	0.75,	0.00,	9995.53,	1235.25,	13.00,	364.65,	3.25,	5.00,	561.00,	8.33,	583.10,	0.00,	1.20,	438.55,	201.00,	NULL,	24.00,	11202.67,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1599.90,	1224.39,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(80,	82,	'VC056',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	86.46,	451.92,	4385.52,	1952.76,	2432.76,	1037.52,	5423.04,	0.00,	'0:00',	112.98,	0.00,	5423.04,	526.26,	12.00,	40.67,	0.75,	0.00,	4856.11,	570.12,	13.00,	176.25,	3.25,	5.00,	271.15,	8.33,	365.31,	0.00,	0.00,	0.00,	94.00,	NULL,	11.00,	5313.13,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	746.37,	566.93,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(81,	83,	'MC022',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	16.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	66.46,	431.92,	6578.28,	2929.14,	3649.14,	1196.28,	7774.56,	0.00,	'0:00',	107.98,	0.00,	7774.56,	789.39,	12.00,	58.31,	0.75,	0.00,	6926.86,	855.18,	13.00,	252.67,	3.25,	5.00,	388.73,	8.33,	547.97,	0.00,	0.00,	0.00,	136.00,	NULL,	16.00,	7603.41,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1107.85,	847.70,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(82,	84,	'VC057',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	8771.04,	3905.52,	4865.52,	1835.04,	10606.08,	0.00,	'4:30',	110.48,	497.16,	11103.24,	1052.52,	12.00,	83.27,	0.75,	0.00,	9967.45,	1140.24,	13.00,	360.86,	3.25,	5.00,	555.16,	8.33,	583.10,	0.00,	1.15,	420.28,	199.00,	NULL,	23.00,	11160.68,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1501.10,	1135.79,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(83,	85,	'VC058',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	9136.50,	4068.25,	5068.25,	1911.50,	11048.00,	0.00,	'0:00',	110.48,	0.00,	11048.00,	1096.38,	12.00,	82.86,	0.75,	0.00,	9868.76,	1187.75,	13.00,	359.06,	3.25,	5.00,	552.40,	8.33,	583.10,	0.00,	1.20,	438.55,	203.00,	NULL,	24.00,	11075.24,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1546.81,	1179.24,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(84,	86,	'VC059',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	110.48,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(85,	87,	'VC060',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	254.54,	620.00,	4385.52,	1952.76,	2432.76,	3054.48,	7440.00,	0.00,	'0:00',	155.00,	0.00,	7440.00,	526.26,	12.00,	55.80,	0.75,	0.00,	6857.94,	570.12,	13.00,	241.80,	3.25,	5.00,	372.00,	8.33,	365.31,	0.00,	0.00,	0.00,	91.00,	NULL,	11.00,	7365.39,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	811.92,	582.06,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(86,	88,	'VC093',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	84.54,	450.00,	8771.04,	3905.52,	4865.52,	2028.96,	10800.00,	0.00,	'14:00',	112.50,	1575.00,	12375.00,	1052.52,	12.00,	92.81,	0.75,	0.00,	11229.67,	1140.24,	13.00,	402.19,	3.25,	5.00,	618.75,	8.33,	583.10,	0.00,	1.10,	402.01,	201.00,	NULL,	22.00,	12436.43,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1542.43,	1145.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(87,	89,	'VC061',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	14.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	254.54,	620.00,	5481.90,	2440.95,	3040.95,	3818.10,	9300.00,	0.00,	'2:00',	155.00,	310.00,	9610.00,	657.83,	12.00,	72.08,	0.75,	0.00,	8880.09,	712.65,	13.00,	312.33,	3.25,	5.00,	480.50,	8.33,	456.64,	0.00,	0.00,	0.00,	121.00,	NULL,	14.00,	9522.16,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1024.98,	729.91,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(88,	90,	'MC031',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	19.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	33.54,	415.00,	8010.66,	3753.33,	4257.33,	704.34,	8715.00,	0.00,	'2:30',	103.75,	259.38,	8974.38,	961.28,	12.00,	67.31,	0.75,	0.00,	7945.79,	1041.39,	13.00,	291.67,	3.25,	5.00,	448.72,	8.33,	583.10,	0.00,	0.00,	0.00,	164.00,	NULL,	19.00,	8673.14,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1333.06,	1028.59,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(89,	91,	'VC087',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	8771.04,	3905.52,	4865.52,	1115.04,	9886.08,	0.00,	'0:00',	102.98,	0.00,	9886.08,	1052.52,	12.00,	74.15,	0.75,	0.00,	8759.41,	1140.24,	13.00,	321.30,	3.25,	5.00,	494.30,	8.33,	583.10,	0.00,	1.10,	402.01,	186.00,	NULL,	22.00,	9903.95,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1461.54,	1126.67,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(90,	92,	'VC063',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	6212.82,	2766.41,	3446.41,	789.82,	7002.64,	0.00,	'12:30',	102.98,	1287.25,	8289.89,	745.54,	12.00,	62.17,	0.75,	0.00,	7482.18,	807.67,	13.00,	269.42,	3.25,	5.00,	414.49,	8.33,	517.53,	0.00,	0.00,	0.00,	146.00,	NULL,	16.00,	8144.82,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1077.09,	807.71,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(91,	93,	'VC095',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	434.54,	800.00,	8771.04,	3905.52,	4865.52,	10428.96,	19200.00,	0.00,	'13:30',	200.00,	2700.00,	21900.00,	1052.52,	12.00,	0.00,	0.75,	0.00,	20847.48,	1140.24,	13.00,	0.00,	3.25,	5.00,	1095.00,	8.33,	583.10,	0.00,	1.10,	402.01,	200.00,	NULL,	22.00,	22839.87,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1140.24,	1052.52,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(92,	94,	'VC064',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	214.54,	580.00,	6578.28,	2929.14,	3649.14,	3861.72,	10440.00,	0.00,	'0:00',	145.00,	0.00,	10440.00,	789.39,	12.00,	78.30,	0.75,	0.00,	9572.31,	855.18,	13.00,	339.30,	3.25,	5.00,	522.00,	8.33,	547.97,	0.00,	0.00,	0.00,	137.00,	NULL,	16.00,	10315.49,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1194.48,	867.69,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(93,	95,	'LS012',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	56.46,	421.92,	8771.04,	3905.52,	4865.52,	1355.04,	10126.08,	0.00,	'0:00',	105.48,	0.00,	10126.08,	1052.52,	12.00,	75.95,	0.75,	0.00,	8997.61,	1140.24,	13.00,	329.10,	3.25,	5.00,	506.30,	8.33,	583.10,	0.00,	1.10,	402.01,	183.00,	NULL,	22.00,	10148.15,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1469.34,	1128.47,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(94,	96,	'VC065',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	20.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	7674.66,	3417.33,	4257.33,	975.66,	8650.32,	0.00,	'0:00',	102.98,	0.00,	8650.32,	920.96,	12.00,	64.88,	0.75,	0.00,	7664.48,	997.71,	13.00,	281.14,	3.25,	5.00,	432.52,	8.33,	583.10,	0.00,	1.00,	365.46,	169.00,	NULL,	20.00,	8752.55,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1278.85,	985.84,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(95,	97,	'VC066',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4750.98,	2115.49,	2635.49,	603.98,	5354.96,	0.00,	'20:30',	102.98,	2111.09,	7466.05,	570.12,	12.00,	56.00,	0.75,	0.00,	6839.93,	617.63,	13.00,	242.65,	3.25,	5.00,	373.30,	8.33,	395.76,	0.00,	0.00,	0.00,	121.00,	NULL,	12.00,	7374.83,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	860.28,	626.12,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(96,	98,	'VC067',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4750.98,	2115.49,	2635.49,	603.98,	5354.96,	0.00,	'0:00',	102.98,	0.00,	5354.96,	570.12,	12.00,	40.16,	0.75,	0.00,	4744.68,	617.63,	13.00,	174.04,	3.25,	5.00,	267.75,	8.33,	395.76,	0.00,	0.00,	0.00,	92.00,	NULL,	11.00,	5226.80,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	791.67,	610.28,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(97,	99,	'VC068',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	21.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	8405.58,	3742.79,	4662.79,	1758.58,	10164.16,	0.00,	'3:30',	110.48,	386.68,	10550.84,	1008.67,	12.00,	79.13,	0.75,	0.00,	9463.04,	1092.73,	13.00,	342.90,	3.25,	5.00,	527.54,	8.33,	583.10,	0.00,	1.05,	383.73,	179.00,	NULL,	21.00,	10609.58,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1435.63,	1087.80,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(98,	100,	'MC029',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	209.54,	575.00,	8771.04,	3905.52,	4865.52,	5028.96,	13800.00,	0.00,	'0:00',	143.75,	0.00,	13800.00,	1052.52,	12.00,	103.50,	0.75,	0.00,	12643.98,	1140.24,	13.00,	448.50,	3.25,	5.00,	690.00,	8.33,	583.10,	0.00,	1.10,	402.01,	186.00,	NULL,	22.00,	13886.37,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	1588.74,	1156.02,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(99,	101,	'MC024',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	10.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4020.06,	1790.03,	2230.03,	511.06,	4531.12,	0.00,	'1:00',	102.98,	102.98,	4634.10,	482.41,	12.00,	34.76,	0.75,	0.00,	4116.93,	522.61,	13.00,	150.61,	3.25,	5.00,	231.71,	8.33,	334.87,	0.00,	0.00,	0.00,	85.00,	NULL,	10.00,	4527.46,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	673.22,	517.17,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(100,	102,	'VC071',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4385.52,	1952.76,	2432.76,	557.52,	4943.04,	0.00,	'0:00',	102.98,	0.00,	4943.04,	526.26,	12.00,	37.07,	0.75,	0.00,	4379.71,	570.12,	13.00,	160.65,	3.25,	5.00,	247.15,	8.33,	365.31,	0.00,	0.00,	0.00,	101.00,	NULL,	12.00,	4824.73,	1,	NULL,	'2023-02-06 13:28:47',	NULL,	'2023-02-06 13:28:47',	0.00,	0.00,	0.00,	0.00,	730.77,	563.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(101,	103,	'VC072',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	20.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	134.54,	500.00,	8040.12,	3580.06,	4460.06,	2959.88,	11000.00,	0.00,	'11:00',	125.00,	1375.00,	12375.00,	964.81,	12.00,	92.81,	0.75,	0.00,	11317.38,	1045.22,	13.00,	402.19,	3.25,	5.00,	618.75,	8.33,	583.10,	0.00,	1.00,	365.46,	178.00,	NULL,	20.00,	12494.90,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1447.41,	1057.62,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(102,	104,	'VC074',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	21.00,	1.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	8392.12,	3932.06,	4460.06,	1507.88,	9900.00,	0.00,	'0:00',	112.50,	0.00,	9900.00,	1007.05,	12.00,	74.25,	0.75,	0.00,	8818.70,	1090.98,	13.00,	321.75,	3.25,	5.00,	495.00,	8.33,	583.10,	0.00,	1.05,	400.53,	177.00,	NULL,	21.00,	9965.90,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1412.73,	1081.30,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(103,	105,	'MC002',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	15.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	197.81,	571.00,	5971.04,	2727.36,	3243.68,	3164.96,	9136.00,	0.00,	'0:00',	142.75,	0.00,	9136.00,	716.52,	12.00,	68.52,	0.75,	0.00,	8350.96,	776.24,	13.00,	296.92,	3.25,	5.00,	456.80,	8.33,	497.39,	0.00,	0.00,	0.00,	128.00,	NULL,	15.00,	9017.03,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1073.16,	785.04,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(104,	106,	'VC104',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4750.98,	2115.49,	2635.49,	603.98,	5354.96,	0.00,	'4:30',	102.98,	463.41,	5818.37,	570.12,	12.00,	43.64,	0.75,	0.00,	5204.61,	617.63,	13.00,	189.10,	3.25,	5.00,	290.92,	8.33,	395.76,	0.00,	0.00,	0.00,	105.00,	NULL,	12.00,	5698.32,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	806.73,	613.76,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(105,	107,	'LS013',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	25.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	56.46,	421.92,	9867.42,	4393.71,	5473.71,	1524.42,	11391.84,	250.00,	'0:00',	105.48,	0.00,	11641.84,	1184.09,	12.00,	87.31,	0.75,	0.00,	10370.44,	1282.76,	13.00,	378.36,	3.25,	5.00,	582.09,	8.33,	583.10,	0.00,	1.25,	456.83,	210.00,	NULL,	25.00,	11602.74,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1661.12,	1271.40,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(106,	108,	'VC094',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	15.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	434.54,	800.00,	5847.36,	2603.68,	3243.68,	6952.64,	12800.00,	0.00,	'5:30',	200.00,	1100.00,	13900.00,	701.68,	12.00,	104.25,	0.75,	0.00,	13094.07,	760.16,	13.00,	451.75,	3.25,	5.00,	695.00,	8.33,	487.09,	0.00,	0.00,	0.00,	132.00,	NULL,	15.00,	13870.18,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1211.91,	805.93,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(107,	109,	'MC025',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	102.98,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(108,	110,	'VC102',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	7.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2923.68,	1301.84,	1621.84,	371.68,	3295.36,	0.00,	'0:00',	102.98,	0.00,	3295.36,	350.84,	12.00,	24.72,	0.75,	0.00,	2919.80,	380.08,	13.00,	107.10,	3.25,	5.00,	164.77,	8.33,	243.54,	0.00,	0.00,	0.00,	59.00,	NULL,	7.00,	3216.49,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	487.18,	375.56,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(109,	111,	'VC076',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	20.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	118.54,	500.00,	8392.12,	3932.06,	4460.06,	2607.88,	11000.00,	0.00,	'3:30',	125.00,	437.50,	11437.50,	1007.05,	12.00,	85.78,	0.75,	0.00,	10344.67,	1090.98,	13.00,	371.72,	3.25,	5.00,	571.88,	8.33,	583.10,	0.00,	1.00,	381.46,	170.00,	NULL,	20.00,	11511.24,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1462.70,	1092.83,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(110,	112,	'MC026',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	5.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	1827.30,	813.65,	1013.65,	232.30,	2059.60,	0.00,	'0:00',	102.98,	0.00,	2059.60,	219.28,	12.00,	15.45,	0.75,	0.00,	1824.87,	237.55,	13.00,	66.94,	3.25,	5.00,	102.98,	8.33,	152.21,	0.00,	0.00,	0.00,	42.00,	NULL,	5.00,	2010.30,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	304.49,	234.73,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(111,	113,	'VC083',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	10.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	88.54,	470.00,	4577.52,	2144.76,	2432.76,	1062.48,	5640.00,	0.00,	'0:00',	117.50,	0.00,	5640.00,	549.30,	12.00,	42.30,	0.75,	0.00,	5048.40,	595.08,	13.00,	183.30,	3.25,	5.00,	282.00,	8.33,	381.31,	0.00,	0.00,	0.00,	84.00,	NULL,	10.00,	5524.93,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	778.38,	591.60,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(112,	114,	'VC089',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	163.54,	545.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	136.25,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(113,	115,	'VC097',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	19.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	7674.66,	3417.33,	4257.33,	975.66,	8650.32,	0.00,	'12:00',	102.98,	1235.76,	9886.08,	920.96,	12.00,	74.15,	0.75,	0.00,	8890.97,	997.71,	13.00,	321.30,	3.25,	5.00,	494.30,	8.33,	583.10,	0.00,	0.00,	0.00,	173.00,	NULL,	19.00,	9644.47,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1319.01,	995.11,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(114,	116,	'VC118',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	6103.36,	2859.68,	3243.68,	1096.64,	7200.00,	0.00,	'8:00',	112.50,	900.00,	8100.00,	732.40,	12.00,	60.75,	0.75,	0.00,	7306.85,	793.44,	13.00,	263.25,	3.25,	5.00,	405.00,	8.33,	508.41,	0.00,	0.00,	0.00,	143.00,	NULL,	16.00,	7956.72,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1056.69,	793.15,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(115,	117,	'VC019',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	17.00,	2.00,	0.00,	26.00,	416.40,	213.67,	202.73,	277.60,	694.00,	7911.60,	4059.73,	3851.87,	5274.40,	13186.00,	0.00,	'6:00',	173.50,	1041.00,	14227.00,	949.39,	12.00,	106.70,	0.75,	0.00,	13170.91,	1028.51,	13.00,	462.38,	3.25,	5.00,	711.35,	8.33,	583.10,	0.00,	0.00,	0.00,	151.00,	NULL,	17.00,	14030.56,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1490.89,	1056.09,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(116,	118,	'VC084',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	21.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	129.54,	495.00,	8405.58,	3742.79,	4662.79,	2979.42,	11385.00,	0.00,	'7:00',	123.75,	866.25,	12251.25,	1008.67,	12.00,	91.88,	0.75,	0.00,	11150.70,	1092.73,	13.00,	398.17,	3.25,	5.00,	612.56,	8.33,	583.10,	0.00,	1.05,	383.73,	182.00,	NULL,	21.00,	12339.74,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1490.90,	1100.55,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(117,	119,	'VC101',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9501.96,	4230.98,	5270.98,	1207.96,	10709.92,	250.00,	'5:00',	102.98,	514.90,	11474.82,	1140.24,	12.00,	86.06,	0.75,	0.00,	10248.52,	1235.25,	13.00,	372.93,	3.25,	5.00,	573.74,	8.33,	583.10,	0.00,	1.20,	438.55,	207.00,	NULL,	24.00,	11462.03,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1608.18,	1226.30,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(118,	120,	'VC100',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	18.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	6943.74,	3091.87,	3851.87,	882.74,	7826.48,	0.00,	'0:00',	102.98,	0.00,	7826.48,	833.25,	12.00,	58.70,	0.75,	0.00,	6934.53,	902.69,	13.00,	254.36,	3.25,	5.00,	391.32,	8.33,	578.41,	0.00,	0.00,	0.00,	152.00,	NULL,	18.00,	7639.16,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1157.05,	891.95,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(119,	121,	'VC092',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	1.00,	0.00,	26.00,	646.80,	444.07,	202.73,	431.20,	1078.00,	16170.00,	11101.75,	5068.25,	10780.00,	26950.00,	0.00,	'0:00',	269.50,	0.00,	26950.00,	1940.40,	12.00,	0.00,	0.75,	0.00,	25009.60,	2102.10,	13.00,	0.00,	3.25,	5.00,	1347.50,	8.33,	583.10,	0.00,	1.20,	776.16,	205.00,	NULL,	24.00,	27554.66,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	2102.10,	1940.40,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(120,	122,	'VC121',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	18.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	7629.20,	3574.60,	4054.60,	1370.80,	9000.00,	0.00,	'37:00',	112.50,	4162.50,	13162.50,	915.50,	12.00,	98.72,	0.75,	0.00,	12148.28,	991.80,	13.00,	427.78,	3.25,	5.00,	658.13,	8.33,	583.10,	0.00,	0.00,	0.00,	188.00,	NULL,	18.00,	12984.15,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1419.58,	1014.22,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(121,	123,	'VC148',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	8.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2923.68,	1301.84,	1621.84,	371.68,	3295.36,	0.00,	'0:00',	102.98,	0.00,	3295.36,	350.84,	12.00,	24.72,	0.75,	0.00,	2919.80,	380.08,	13.00,	107.10,	3.25,	5.00,	164.77,	8.33,	243.54,	0.00,	0.00,	0.00,	67.00,	NULL,	8.00,	3216.49,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	487.18,	375.56,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(122,	124,	'VC099',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	6578.28,	2929.14,	3649.14,	836.28,	7414.56,	0.00,	'4:30',	102.98,	463.41,	7877.97,	789.39,	12.00,	59.08,	0.75,	0.00,	7029.50,	855.18,	13.00,	256.03,	3.25,	5.00,	393.90,	8.33,	547.97,	0.00,	0.00,	0.00,	140.00,	NULL,	16.00,	7708.63,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1111.21,	848.47,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(123,	125,	'VC150',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9136.50,	4068.25,	5068.25,	1161.50,	10298.00,	0.00,	'0:00',	102.98,	0.00,	10298.00,	1096.38,	12.00,	77.24,	0.75,	0.00,	9124.38,	1187.75,	13.00,	334.69,	3.25,	5.00,	514.90,	8.33,	583.10,	0.00,	1.20,	438.55,	204.00,	NULL,	24.00,	10312.11,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1522.44,	1173.62,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(124,	126,	'VC120',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	22.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	8771.04,	3905.52,	4865.52,	1115.04,	9886.08,	0.00,	'0:00',	102.98,	0.00,	9886.08,	1052.52,	12.00,	74.15,	0.75,	0.00,	8759.41,	1140.24,	13.00,	321.30,	3.25,	5.00,	494.30,	8.33,	583.10,	0.00,	1.10,	402.01,	185.00,	NULL,	22.00,	9903.95,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1461.54,	1126.67,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(125,	127,	'VC078',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	20.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	8040.12,	3580.06,	4460.06,	1022.12,	9062.24,	0.00,	'5:00',	102.98,	514.90,	9577.14,	964.81,	12.00,	71.83,	0.75,	0.00,	8540.50,	1045.22,	13.00,	311.26,	3.25,	5.00,	478.86,	8.33,	583.10,	0.00,	1.00,	365.46,	171.00,	NULL,	20.00,	9648.08,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1356.48,	1036.64,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(126,	128,	'VC090',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	21.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	434.54,	800.00,	8405.58,	3742.79,	4662.79,	9994.42,	18400.00,	0.00,	'5:30',	200.00,	1100.00,	19500.00,	1008.67,	12.00,	146.25,	0.75,	0.00,	18345.08,	1092.73,	13.00,	633.75,	3.25,	5.00,	975.00,	8.33,	583.10,	0.00,	1.05,	383.73,	183.00,	NULL,	21.00,	19715.35,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1726.48,	1154.92,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(127,	129,	'VC107',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	102.98,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(128,	130,	'MC027',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9501.96,	4230.98,	5270.98,	1207.96,	10709.92,	250.00,	'0:00',	102.98,	0.00,	10959.92,	1140.24,	12.00,	82.20,	0.75,	0.00,	9737.48,	1235.25,	13.00,	356.20,	3.25,	5.00,	548.00,	8.33,	583.10,	0.00,	1.20,	438.55,	203.00,	NULL,	24.00,	10938.12,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1591.45,	1222.44,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(129,	131,	'MC028',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9501.96,	4230.98,	5270.98,	1207.96,	10709.92,	250.00,	'0:00',	102.98,	0.00,	10959.92,	1140.24,	12.00,	82.20,	0.75,	0.00,	9737.48,	1235.25,	13.00,	356.20,	3.25,	5.00,	548.00,	8.33,	583.10,	0.00,	1.20,	438.55,	203.00,	NULL,	24.00,	10938.12,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1591.45,	1222.44,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(130,	132,	'VC079',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	17.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	6943.74,	3091.87,	3851.87,	882.74,	7826.48,	0.00,	'10:00',	102.98,	1029.80,	8856.28,	833.25,	12.00,	66.42,	0.75,	0.00,	7956.61,	902.69,	13.00,	287.83,	3.25,	5.00,	442.81,	8.33,	578.41,	0.00,	0.00,	0.00,	152.00,	NULL,	17.00,	8686.98,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	1190.52,	899.67,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(131,	133,	'VC081',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	112.50,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(132,	134,	'VC123',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4750.98,	2115.49,	2635.49,	603.98,	5354.96,	0.00,	'13:30',	102.98,	1390.23,	6745.19,	570.12,	12.00,	50.59,	0.75,	0.00,	6124.48,	617.63,	13.00,	219.22,	3.25,	5.00,	337.26,	8.33,	395.76,	0.00,	0.00,	0.00,	114.00,	NULL,	12.00,	6641.36,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	836.85,	620.71,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(133,	135,	'VC088',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	15.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5847.36,	2603.68,	3243.68,	743.36,	6590.72,	0.00,	'3:00',	102.98,	308.94,	6899.66,	701.68,	12.00,	51.75,	0.75,	0.00,	6146.23,	760.16,	13.00,	224.24,	3.25,	5.00,	344.98,	8.33,	487.09,	0.00,	0.00,	0.00,	130.00,	NULL,	15.00,	6747.33,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	984.40,	753.43,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(134,	136,	'VC069',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	102.98,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:48',	NULL,	'2023-02-06 13:28:48',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(135,	137,	'VC091',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	17.00,	1.00,	0.00,	26.00,	381.46,	178.73,	202.73,	88.54,	470.00,	6866.28,	3217.14,	3649.14,	1593.72,	8460.00,	0.00,	'0:00',	117.50,	0.00,	8460.00,	823.95,	12.00,	63.45,	0.75,	0.00,	7572.60,	892.62,	13.00,	274.95,	3.25,	5.00,	423.00,	8.33,	571.96,	0.00,	0.00,	0.00,	144.00,	NULL,	17.00,	8287.39,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1167.57,	887.40,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(136,	138,	'VC119',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	4.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	1827.30,	813.65,	1013.65,	232.30,	2059.60,	0.00,	'0:00',	102.98,	0.00,	2059.60,	219.28,	12.00,	15.45,	0.75,	0.00,	1824.87,	237.55,	13.00,	66.94,	3.25,	5.00,	102.98,	8.33,	152.21,	0.00,	0.00,	0.00,	33.00,	NULL,	4.00,	2010.30,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	304.49,	234.73,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(137,	139,	'VC108',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4385.52,	1952.76,	2432.76,	557.52,	4943.04,	0.00,	'7:00',	102.98,	720.86,	5663.90,	526.26,	12.00,	42.48,	0.75,	0.00,	5095.16,	570.12,	13.00,	184.08,	3.25,	5.00,	283.20,	8.33,	365.31,	0.00,	0.00,	0.00,	100.00,	NULL,	11.00,	5558.21,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	754.20,	568.74,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(138,	140,	'VC082',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	23.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9136.50,	4068.25,	5068.25,	1161.50,	10298.00,	0.00,	'11:00',	102.98,	1132.78,	11430.78,	1096.38,	12.00,	85.73,	0.75,	0.00,	10248.67,	1187.75,	13.00,	371.50,	3.25,	5.00,	571.54,	8.33,	583.10,	0.00,	1.15,	420.28,	203.00,	NULL,	23.00,	11446.45,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1559.25,	1182.11,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(139,	141,	'VC034',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	10.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	3654.60,	1627.30,	2027.30,	764.60,	4419.20,	0.00,	'4:00',	110.48,	441.92,	4861.12,	438.55,	12.00,	36.46,	0.75,	0.00,	4386.11,	475.10,	13.00,	157.99,	3.25,	5.00,	243.06,	8.33,	304.43,	0.00,	0.00,	0.00,	88.00,	NULL,	10.00,	4775.52,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	633.09,	475.01,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(140,	142,	'VC126',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	6578.28,	2929.14,	3649.14,	836.28,	7414.56,	0.00,	'5:30',	102.98,	566.39,	7980.95,	789.39,	12.00,	59.86,	0.75,	0.00,	7131.70,	855.18,	13.00,	259.38,	3.25,	5.00,	399.05,	8.33,	547.97,	0.00,	0.00,	0.00,	141.00,	NULL,	16.00,	7813.41,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1114.56,	849.25,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(141,	143,	'LS014',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	4,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	84.54,	450.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	112.50,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(142,	144,	'VC073',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	25.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	76.46,	441.92,	9867.42,	4393.71,	5473.71,	2064.42,	11931.84,	250.00,	'2:00',	110.48,	220.96,	12402.80,	1184.09,	12.00,	93.02,	0.75,	0.00,	11125.69,	1282.76,	13.00,	403.09,	3.25,	5.00,	620.14,	8.33,	583.10,	0.00,	1.25,	456.83,	220.00,	NULL,	25.00,	12377.02,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1685.85,	1277.11,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(143,	145,	'VC106',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	10.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	3654.60,	1627.30,	2027.30,	464.60,	4119.20,	0.00,	'0:00',	102.98,	0.00,	4119.20,	438.55,	12.00,	30.89,	0.75,	0.00,	3649.76,	475.10,	13.00,	133.87,	3.25,	5.00,	205.96,	8.33,	304.43,	0.00,	0.00,	0.00,	84.00,	NULL,	10.00,	4020.62,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	608.97,	469.44,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(144,	146,	'VC122',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	4.00,	1.00,	0.00,	26.00,	380.46,	177.73,	202.73,	219.54,	600.00,	1902.30,	888.65,	1013.65,	1097.70,	3000.00,	0.00,	'0:00',	150.00,	0.00,	3000.00,	228.28,	12.00,	22.50,	0.75,	0.00,	2749.22,	247.30,	13.00,	97.50,	3.25,	5.00,	150.00,	8.33,	158.46,	0.00,	0.00,	0.00,	33.00,	NULL,	4.00,	2963.66,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	344.80,	250.78,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(145,	147,	'VC096',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	5.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	66.46,	431.92,	1827.30,	813.65,	1013.65,	332.30,	2159.60,	0.00,	'0:00',	107.98,	0.00,	2159.60,	219.28,	12.00,	16.20,	0.75,	0.00,	1924.12,	237.55,	13.00,	70.19,	3.25,	5.00,	107.98,	8.33,	152.21,	0.00,	0.00,	0.00,	42.00,	NULL,	5.00,	2112.05,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	307.74,	235.48,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(146,	148,	'VC116',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	15.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	134.54,	500.00,	5847.36,	2603.68,	3243.68,	2152.64,	8000.00,	0.00,	'3:00',	125.00,	375.00,	8375.00,	701.68,	12.00,	62.81,	0.75,	0.00,	7610.51,	760.16,	13.00,	272.19,	3.25,	5.00,	418.75,	8.33,	487.09,	0.00,	0.00,	0.00,	129.00,	NULL,	15.00,	8248.49,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1032.35,	764.49,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(147,	149,	'VC077',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	19.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	7674.66,	3417.33,	4257.33,	975.66,	8650.32,	0.00,	'11:30',	102.98,	1184.27,	9834.59,	920.96,	12.00,	73.76,	0.75,	0.00,	8839.87,	997.71,	13.00,	319.62,	3.25,	5.00,	491.73,	8.33,	583.10,	0.00,	0.00,	0.00,	170.00,	NULL,	19.00,	9592.09,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1317.33,	994.72,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(148,	150,	'VC070',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	19.00,	1.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	7629.20,	3574.60,	4054.60,	1370.80,	9000.00,	0.00,	'0:00',	112.50,	0.00,	9000.00,	915.50,	12.00,	67.50,	0.75,	0.00,	8017.00,	991.80,	13.00,	292.50,	3.25,	5.00,	450.00,	8.33,	583.10,	0.00,	0.00,	0.00,	159.00,	NULL,	19.00,	8748.80,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1284.30,	983.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(149,	151,	'VC103',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4385.52,	1952.76,	2432.76,	557.52,	4943.04,	0.00,	'12:00',	102.98,	1235.76,	6178.80,	526.26,	12.00,	46.34,	0.75,	0.00,	5606.20,	570.12,	13.00,	200.81,	3.25,	5.00,	308.94,	8.33,	365.31,	0.00,	0.00,	0.00,	104.00,	NULL,	11.00,	6082.12,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	770.93,	572.60,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(150,	152,	'VC098',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4385.52,	1952.76,	2432.76,	557.52,	4943.04,	0.00,	'0:00',	102.98,	0.00,	4943.04,	526.26,	12.00,	37.07,	0.75,	0.00,	4379.71,	570.12,	13.00,	160.65,	3.25,	5.00,	247.15,	8.33,	365.31,	0.00,	0.00,	0.00,	92.00,	NULL,	11.00,	4824.73,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	730.77,	563.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(151,	153,	'VC086',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5116.44,	2278.22,	2838.22,	650.44,	5766.88,	0.00,	'16:00',	102.98,	1647.68,	7414.56,	613.97,	12.00,	55.61,	0.75,	0.00,	6744.98,	665.14,	13.00,	240.97,	3.25,	5.00,	370.73,	8.33,	426.20,	0.00,	0.00,	0.00,	124.00,	NULL,	13.00,	7305.38,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	906.11,	669.58,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(152,	154,	'VC085',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	102.98,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(153,	155,	'VC105',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	8.00,	1.00,	0.00,	26.00,	373.19,	170.46,	202.73,	251.81,	625.00,	3358.71,	1534.14,	1824.57,	2266.29,	5625.00,	0.00,	'4:00',	156.25,	625.00,	6250.00,	403.05,	12.00,	46.88,	0.75,	0.00,	5800.07,	436.63,	13.00,	203.13,	3.25,	5.00,	312.50,	8.33,	279.78,	0.00,	0.00,	0.00,	71.00,	NULL,	8.00,	6202.52,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	639.76,	449.93,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(154,	156,	'VC062',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	294.54,	660.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	165.00,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(155,	157,	'MC032',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	25.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	66.46,	431.92,	9867.42,	4393.71,	5473.71,	1794.42,	11661.84,	250.00,	'0:00',	107.98,	0.00,	11911.84,	1184.09,	12.00,	89.34,	0.75,	0.00,	10638.41,	1282.76,	13.00,	387.13,	3.25,	5.00,	595.59,	8.33,	583.10,	0.00,	1.25,	456.83,	211.00,	NULL,	25.00,	11877.47,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1669.89,	1273.43,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(156,	158,	'MC023',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	12.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4385.52,	1952.76,	2432.76,	557.52,	4943.04,	0.00,	'0:00',	102.98,	0.00,	4943.04,	526.26,	12.00,	37.07,	0.75,	0.00,	4379.71,	570.12,	13.00,	160.65,	3.25,	5.00,	247.15,	8.33,	365.31,	0.00,	0.00,	0.00,	103.00,	NULL,	12.00,	4824.73,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	730.77,	563.33,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(157,	159,	'VC132',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	15.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5847.36,	2603.68,	3243.68,	743.36,	6590.72,	0.00,	'2:00',	102.98,	205.96,	6796.68,	701.68,	12.00,	50.98,	0.75,	0.00,	6044.02,	760.16,	13.00,	220.89,	3.25,	5.00,	339.83,	8.33,	487.09,	0.00,	0.00,	0.00,	129.00,	NULL,	15.00,	6642.55,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	981.05,	752.66,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(158,	160,	'VC139',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	18.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	6866.28,	3217.14,	3649.14,	1233.72,	8100.00,	0.00,	'0:00',	112.50,	0.00,	8100.00,	823.95,	12.00,	60.75,	0.75,	0.00,	7215.30,	892.62,	13.00,	263.25,	3.25,	5.00,	405.00,	8.33,	571.96,	0.00,	0.00,	0.00,	153.00,	NULL,	18.00,	7921.09,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1155.87,	884.70,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(159,	161,	'VC145',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	1.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	365.46,	162.73,	202.73,	46.46,	411.92,	0.00,	'0:00',	102.98,	0.00,	411.92,	43.86,	12.00,	3.09,	0.75,	0.00,	364.97,	47.51,	13.00,	13.39,	3.25,	5.00,	20.60,	8.33,	30.44,	0.00,	0.00,	0.00,	8.00,	NULL,	1.00,	402.06,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	60.90,	46.95,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(160,	162,	'VC143',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	84.54,	450.00,	6212.82,	2766.41,	3446.41,	1437.18,	7650.00,	0.00,	'3:00',	112.50,	337.50,	7987.50,	745.54,	12.00,	59.91,	0.75,	0.00,	7182.05,	807.67,	13.00,	259.59,	3.25,	5.00,	399.38,	8.33,	517.53,	0.00,	0.00,	0.00,	139.00,	NULL,	16.00,	7837.15,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1067.26,	805.45,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(161,	163,	'VC144',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5116.44,	2278.22,	2838.22,	650.44,	5766.88,	0.00,	'8:30',	102.98,	875.33,	6642.21,	613.97,	12.00,	49.82,	0.75,	0.00,	5978.42,	665.14,	13.00,	215.87,	3.25,	5.00,	332.11,	8.33,	426.20,	0.00,	0.00,	0.00,	118.00,	NULL,	13.00,	6519.51,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	881.01,	663.79,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(162,	164,	'VC154',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9501.96,	4230.98,	5270.98,	1207.96,	10709.92,	250.00,	'0:00',	102.98,	0.00,	10959.92,	1140.24,	12.00,	82.20,	0.75,	0.00,	9737.48,	1235.25,	13.00,	356.20,	3.25,	5.00,	548.00,	8.33,	583.10,	0.00,	1.20,	438.55,	203.00,	NULL,	24.00,	10938.12,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1591.45,	1222.44,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(163,	165,	'MC033',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	14.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5481.90,	2440.95,	3040.95,	696.90,	6178.80,	0.00,	'0:00',	102.98,	0.00,	6178.80,	657.83,	12.00,	46.34,	0.75,	0.00,	5474.63,	712.65,	13.00,	200.81,	3.25,	5.00,	308.94,	8.33,	456.64,	0.00,	0.00,	0.00,	118.00,	NULL,	14.00,	6030.92,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	913.46,	704.17,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(164,	166,	'VC161',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	19.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	8010.66,	3753.33,	4257.33,	1439.34,	9450.00,	0.00,	'9:00',	112.50,	1012.50,	10462.50,	961.28,	12.00,	78.47,	0.75,	0.00,	9422.75,	1041.39,	13.00,	340.03,	3.25,	5.00,	523.13,	8.33,	583.10,	0.00,	0.00,	0.00,	171.00,	NULL,	19.00,	10187.31,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	1381.42,	1039.75,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(165,	167,	'VC140',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	8.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2923.68,	1301.84,	1621.84,	371.68,	3295.36,	0.00,	'10:30',	102.98,	1081.29,	4376.65,	350.84,	12.00,	32.82,	0.75,	0.00,	3992.99,	380.08,	13.00,	142.24,	3.25,	5.00,	218.83,	8.33,	243.54,	0.00,	0.00,	0.00,	78.00,	NULL,	8.00,	4316.70,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	522.32,	383.66,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(166,	168,	'VC141',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	102.98,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(167,	169,	'VC149',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	3.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	1096.38,	488.19,	608.19,	139.38,	1235.76,	0.00,	'0:00',	102.98,	0.00,	1235.76,	131.57,	12.00,	9.27,	0.75,	0.00,	1094.92,	142.53,	13.00,	40.16,	3.25,	5.00,	61.79,	8.33,	91.33,	0.00,	0.00,	0.00,	25.00,	NULL,	3.00,	1206.19,	1,	NULL,	'2023-02-06 13:28:49',	NULL,	'2023-02-06 13:28:49',	0.00,	0.00,	0.00,	0.00,	182.69,	140.84,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(168,	170,	'VC131',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	7.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	2670.22,	1251.11,	1419.11,	479.78,	3150.00,	0.00,	'0:00',	112.50,	0.00,	3150.00,	320.43,	12.00,	23.63,	0.75,	0.00,	2805.94,	347.13,	13.00,	102.38,	3.25,	5.00,	157.50,	8.33,	222.43,	0.00,	0.00,	0.00,	58.00,	NULL,	7.00,	3080.42,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	449.51,	344.06,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(169,	171,	'VC152',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	18.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	7309.20,	3254.60,	4054.60,	929.20,	8238.40,	0.00,	'0:00',	102.98,	0.00,	8238.40,	877.10,	12.00,	61.79,	0.75,	0.00,	7299.51,	950.20,	13.00,	267.75,	3.25,	5.00,	411.92,	8.33,	583.10,	0.00,	0.00,	0.00,	152.00,	NULL,	18.00,	8015.47,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1217.95,	938.89,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(170,	172,	'VC151',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	6.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2558.22,	1139.11,	1419.11,	325.22,	2883.44,	0.00,	'0:00',	102.98,	0.00,	2883.44,	306.99,	12.00,	21.63,	0.75,	0.00,	2554.82,	332.57,	13.00,	93.71,	3.25,	5.00,	144.17,	8.33,	213.10,	0.00,	0.00,	0.00,	51.00,	NULL,	6.00,	2814.43,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	426.28,	328.62,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(171,	173,	'VC146',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	9501.96,	4230.98,	5270.98,	1207.96,	10709.92,	250.00,	'0:00',	102.98,	0.00,	10959.92,	1140.24,	12.00,	82.20,	0.75,	0.00,	9737.48,	1235.25,	13.00,	356.20,	3.25,	5.00,	548.00,	8.33,	583.10,	0.00,	1.20,	438.55,	203.00,	NULL,	24.00,	10938.12,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1591.45,	1222.44,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(172,	174,	'VC147',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	6.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2558.22,	1139.11,	1419.11,	325.22,	2883.44,	0.00,	'2:00',	102.98,	205.96,	3089.40,	306.99,	12.00,	23.17,	0.75,	0.00,	2759.24,	332.57,	13.00,	100.41,	3.25,	5.00,	154.47,	8.33,	213.10,	0.00,	0.00,	0.00,	52.00,	NULL,	6.00,	3023.99,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	432.98,	330.16,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(173,	175,	'VC142',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	15.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5847.36,	2603.68,	3243.68,	743.36,	6590.72,	0.00,	'1:00',	102.98,	102.98,	6693.70,	701.68,	12.00,	50.20,	0.75,	0.00,	5941.82,	760.16,	13.00,	217.55,	3.25,	5.00,	334.69,	8.33,	487.09,	0.00,	0.00,	0.00,	126.00,	NULL,	15.00,	6537.77,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	977.71,	751.88,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(174,	176,	'VC127',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	8.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2923.68,	1301.84,	1621.84,	371.68,	3295.36,	0.00,	'10:30',	102.98,	1081.29,	4376.65,	350.84,	12.00,	32.82,	0.75,	0.00,	3992.99,	380.08,	13.00,	142.24,	3.25,	5.00,	218.83,	8.33,	243.54,	0.00,	0.00,	0.00,	78.00,	NULL,	8.00,	4316.70,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	522.32,	383.66,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(175,	177,	'VC133',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	14.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	5481.90,	2440.95,	3040.95,	696.90,	6178.80,	0.00,	'2:30',	102.98,	257.45,	6436.25,	657.83,	12.00,	48.27,	0.75,	0.00,	5730.15,	712.65,	13.00,	209.18,	3.25,	5.00,	321.81,	8.33,	456.64,	0.00,	0.00,	0.00,	121.00,	NULL,	14.00,	6292.87,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	921.83,	706.10,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(176,	178,	'VC155',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	3.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	1096.38,	488.19,	608.19,	139.38,	1235.76,	0.00,	'0:00',	102.98,	0.00,	1235.76,	131.57,	12.00,	9.27,	0.75,	0.00,	1094.92,	142.53,	13.00,	40.16,	3.25,	5.00,	61.79,	8.33,	91.33,	0.00,	0.00,	0.00,	25.00,	NULL,	3.00,	1206.19,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	182.69,	140.84,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(177,	179,	'MC018',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	18.00,	1.00,	0.00,	26.00,	368.27,	165.54,	202.73,	156.73,	525.00,	6997.13,	3145.26,	3851.87,	2977.87,	9975.00,	0.00,	'3:30',	131.25,	459.38,	10434.38,	839.66,	12.00,	78.26,	0.75,	0.00,	9516.46,	909.63,	13.00,	339.12,	3.25,	5.00,	521.72,	8.33,	582.86,	0.00,	0.00,	0.00,	155.00,	NULL,	18.00,	10290.21,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1248.75,	917.92,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(178,	180,	'MC019',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	26.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	131.73,	500.00,	10311.56,	4635.12,	5676.44,	3688.44,	14000.00,	250.00,	'5:00',	125.00,	625.00,	14875.00,	1237.39,	12.00,	111.56,	0.75,	0.00,	13526.05,	1340.50,	13.00,	483.44,	3.25,	5.00,	743.75,	8.33,	583.10,	0.00,	1.30,	478.75,	227.00,	NULL,	26.00,	14856.66,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1823.94,	1348.95,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(179,	181,	'MC020',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	23.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	146.73,	515.00,	9206.75,	4138.50,	5068.25,	3668.25,	12875.00,	0.00,	'0:00',	128.75,	0.00,	12875.00,	1104.81,	12.00,	96.56,	0.75,	0.00,	11673.63,	1196.88,	13.00,	418.44,	3.25,	5.00,	643.75,	8.33,	583.10,	0.00,	1.15,	423.51,	194.00,	NULL,	23.00,	12910.04,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1615.32,	1201.37,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(180,	182,	'MC021',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	5,	11,	16.00,	2.00,	0.00,	26.00,	368.27,	165.54,	202.73,	131.73,	500.00,	6628.86,	2979.72,	3649.14,	2371.14,	9000.00,	0.00,	'1:30',	125.00,	187.50,	9187.50,	795.46,	12.00,	68.91,	0.75,	0.00,	8323.13,	861.75,	13.00,	298.59,	3.25,	5.00,	459.38,	8.33,	552.18,	0.00,	0.00,	0.00,	137.00,	NULL,	16.00,	9038.72,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1160.34,	864.37,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(181,	183,	'VC115',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	16.00,	1.00,	0.00,	26.00,	381.46,	178.73,	202.73,	118.54,	500.00,	6484.82,	3038.41,	3446.41,	2015.18,	8500.00,	0.00,	'0:00',	125.00,	0.00,	8500.00,	778.18,	12.00,	63.75,	0.75,	0.00,	7658.07,	843.03,	13.00,	276.25,	3.25,	5.00,	425.00,	8.33,	540.19,	0.00,	0.00,	0.00,	134.00,	NULL,	16.00,	8345.91,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1119.28,	841.93,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(182,	184,	'VC114',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	12.00,	1.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	4958.98,	2323.49,	2635.49,	891.02,	5850.00,	0.00,	'0:00',	112.50,	0.00,	5850.00,	595.08,	12.00,	43.88,	0.75,	0.00,	5211.04,	644.67,	13.00,	190.13,	3.25,	5.00,	292.50,	8.33,	413.08,	0.00,	0.00,	0.00,	101.00,	NULL,	12.00,	5720.78,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	834.80,	638.96,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(183,	185,	'VC112',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	25.00,	2.00,	0.00,	26.00,	381.46,	178.73,	202.73,	118.54,	500.00,	10299.42,	4825.71,	5473.71,	3200.58,	13500.00,	250.00,	'0:00',	125.00,	0.00,	13750.00,	1235.93,	12.00,	103.13,	0.75,	0.00,	12410.94,	1338.92,	13.00,	446.88,	3.25,	5.00,	687.50,	8.33,	583.10,	0.00,	1.25,	476.83,	214.00,	NULL,	25.00,	13711.63,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1785.80,	1339.06,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(184,	186,	'VC111',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	14.00,	1.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	5721.90,	2680.95,	3040.95,	1028.10,	6750.00,	0.00,	'0:00',	112.50,	0.00,	6750.00,	686.63,	12.00,	50.63,	0.75,	0.00,	6012.74,	743.85,	13.00,	219.38,	3.25,	5.00,	337.50,	8.33,	476.63,	0.00,	0.00,	0.00,	120.00,	NULL,	14.00,	6600.90,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	963.23,	737.26,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(185,	187,	'VC075',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	24.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	134.54,	500.00,	9136.50,	4068.25,	5068.25,	3363.50,	12500.00,	0.00,	'0:00',	125.00,	0.00,	12500.00,	1096.38,	12.00,	93.75,	0.75,	0.00,	11309.87,	1187.75,	13.00,	406.25,	3.25,	5.00,	625.00,	8.33,	583.10,	0.00,	1.20,	438.55,	203.00,	NULL,	24.00,	12552.65,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1594.00,	1190.13,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(186,	188,	'VC0125',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	368.27,	165.54,	202.73,	121.73,	490.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	122.50,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(187,	189,	'VC113',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	13.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	118.54,	500.00,	4958.98,	2323.49,	2635.49,	1541.02,	6500.00,	0.00,	'0:00',	125.00,	0.00,	6500.00,	595.08,	12.00,	48.75,	0.75,	0.00,	5856.17,	644.67,	13.00,	211.25,	3.25,	5.00,	325.00,	8.33,	413.08,	0.00,	0.00,	0.00,	111.00,	NULL,	13.00,	6382.16,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	855.92,	643.83,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(188,	190,	'VC080',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	19.00,	1.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	7309.20,	3254.60,	4054.60,	929.20,	8238.40,	0.00,	'0:00',	102.98,	0.00,	8238.40,	877.10,	12.00,	61.79,	0.75,	0.00,	7299.51,	950.20,	13.00,	267.75,	3.25,	5.00,	411.92,	8.33,	583.10,	0.00,	0.00,	0.00,	161.00,	NULL,	19.00,	8015.47,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1217.95,	938.89,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(189,	191,	'VC135',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	7.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2558.22,	1139.11,	1419.11,	325.22,	2883.44,	0.00,	'0:00',	102.98,	0.00,	2883.44,	306.99,	12.00,	21.63,	0.75,	0.00,	2554.82,	332.57,	13.00,	93.71,	3.25,	5.00,	144.17,	8.33,	213.10,	0.00,	0.00,	0.00,	59.00,	NULL,	7.00,	2814.43,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	426.28,	328.62,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(190,	192,	'VC110',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	11.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	4750.98,	2115.49,	2635.49,	603.98,	5354.96,	0.00,	'0:00',	102.98,	0.00,	5354.96,	570.12,	12.00,	40.16,	0.75,	0.00,	4744.68,	617.63,	13.00,	174.04,	3.25,	5.00,	267.75,	8.33,	395.76,	0.00,	0.00,	0.00,	92.00,	NULL,	11.00,	5226.80,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	791.67,	610.28,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(191,	193,	'VC130',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	0.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	'0:00',	112.50,	0.00,	0.00,	0.00,	12.00,	0.00,	0.75,	0.00,	0.00,	0.00,	13.00,	0.00,	3.25,	5.00,	0.00,	8.33,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	0.00,	0.00,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	0.00,	0.00,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(192,	194,	'VC128',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	2.00,	0.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	730.92,	325.46,	405.46,	92.92,	823.84,	0.00,	'0:00',	102.98,	0.00,	823.84,	87.71,	12.00,	6.18,	0.75,	0.00,	729.95,	95.02,	13.00,	26.77,	3.25,	5.00,	41.19,	8.33,	60.89,	0.00,	0.00,	0.00,	16.00,	NULL,	2.00,	804.13,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	121.79,	93.89,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(193,	195,	'VC137',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	4.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	1525.84,	714.92,	810.92,	274.16,	1800.00,	0.00,	'0:00',	112.50,	0.00,	1800.00,	183.10,	12.00,	13.50,	0.75,	0.00,	1603.40,	198.36,	13.00,	58.50,	3.25,	5.00,	90.00,	8.33,	127.10,	0.00,	0.00,	0.00,	34.00,	NULL,	4.00,	1760.24,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	256.86,	196.60,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(194,	196,	'VC117',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	20.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	8040.12,	3580.06,	4460.06,	1022.12,	9062.24,	0.00,	'0:00',	102.98,	0.00,	9062.24,	964.81,	12.00,	67.97,	0.75,	0.00,	8029.46,	1045.22,	13.00,	294.52,	3.25,	5.00,	453.11,	8.33,	583.10,	0.00,	1.00,	365.46,	170.00,	NULL,	20.00,	9124.17,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	1339.74,	1032.78,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(195,	197,	'VC136',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	10.00,	0.00,	0.00,	26.00,	381.46,	178.73,	202.73,	68.54,	450.00,	3814.60,	1787.30,	2027.30,	685.40,	4500.00,	0.00,	'0:00',	112.50,	0.00,	4500.00,	457.75,	12.00,	33.75,	0.75,	0.00,	4008.50,	495.90,	13.00,	146.25,	3.25,	5.00,	225.00,	8.33,	317.76,	0.00,	0.00,	0.00,	84.00,	NULL,	10.00,	4400.61,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	642.15,	491.50,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL),
(196,	198,	'VC156',	'2023-02-06',	'2022-12-26',	'2023-01-25',	1,	2023,	12,	NULL,	NULL,	2,	8,	11,	6.00,	2.00,	0.00,	26.00,	365.46,	162.73,	202.73,	46.46,	411.92,	2923.68,	1301.84,	1621.84,	371.68,	3295.36,	0.00,	'0:00',	102.98,	0.00,	3295.36,	350.84,	12.00,	24.72,	0.75,	0.00,	2919.80,	380.08,	13.00,	107.10,	3.25,	5.00,	164.77,	8.33,	243.54,	0.00,	0.00,	0.00,	50.00,	NULL,	6.00,	3216.49,	1,	NULL,	'2023-02-06 13:28:50',	NULL,	'2023-02-06 13:28:50',	0.00,	0.00,	0.00,	0.00,	487.18,	375.56,	NULL,	NULL,	0,	0.00,	NULL,	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `payroll_settings`;
CREATE TABLE `payroll_settings` (
  `payset_id` int NOT NULL AUTO_INCREMENT,
  `basic_da` decimal(10,2) DEFAULT NULL,
  `hra` decimal(10,2) DEFAULT NULL,
  `attendance_bonus` decimal(10,2) DEFAULT NULL,
  `ot_per_hour` decimal(10,2) DEFAULT NULL COMMENT 'dailywage / working hr (605/8)*2',
  `employee_esic` decimal(10,2) DEFAULT NULL COMMENT ' (< 21000,F22*0.75%,0) from gross',
  `employer_esic` decimal(10,2) DEFAULT NULL COMMENT '12% from basic & da',
  `employee_pf` decimal(10,2) DEFAULT NULL,
  `employer_pf` decimal(10,2) DEFAULT NULL COMMENT '13% from basic & da',
  `service_charge` decimal(10,2) DEFAULT NULL COMMENT '5% from gross',
  `bonus` decimal(10,2) DEFAULT NULL COMMENT '8.33% if >7000 , 7000*8.33% else  amount *8.33% year closing time / settlement',
  `el_amount` decimal(10,2) DEFAULT NULL COMMENT 'Basic + Da * Earned leave  year closing time / settlement',
  `el_day_limit` int DEFAULT NULL COMMENT '20days',
  `lwf` decimal(10,2) DEFAULT NULL COMMENT 'Labour Welfare Fund, 20 rs calculated per year / when settlement ',
  `year_closing` tinyint DEFAULT NULL COMMENT '1 Finantial year,2 end of the year ',
  `basic_earns` decimal(10,2) DEFAULT NULL,
  `da_earns` decimal(10,2) DEFAULT NULL,
  `other_allowance` decimal(10,2) DEFAULT NULL,
  `other_deduction` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`payset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

INSERT INTO `payroll_settings` (`payset_id`, `basic_da`, `hra`, `attendance_bonus`, `ot_per_hour`, `employee_esic`, `employer_esic`, `employee_pf`, `employer_pf`, `service_charge`, `bonus`, `el_amount`, `el_day_limit`, `lwf`, `year_closing`, `basic_earns`, `da_earns`, `other_allowance`, `other_deduction`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1,	60.00,	40.00,	250.00,	2.00,	0.75,	3.25,	12.00,	13.00,	5.00,	8.33,	60.00,	20,	20.00,	0,	66.67,	33.34,	0.00,	0.00,	'2023-01-03 14:33:35',	1,	'2023-01-04 12:43:05',	1);

DROP TABLE IF EXISTS `performance_category`;
CREATE TABLE `performance_category` (
  `performance_category_id` int unsigned NOT NULL,
  `performance_category_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `performance_criteria`;
CREATE TABLE `performance_criteria` (
  `performance_criteria_id` int unsigned NOT NULL,
  `performance_category_id` int unsigned NOT NULL,
  `performance_criteria_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `permanent_employees`;
CREATE TABLE `permanent_employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `finger_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ctc` float DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permanent_employees` (`id`, `finger_id`, `ctc`) VALUES
(1,	'DPL58',	4438),
(2,	'DPL59',	6231),
(3,	'DPL296',	2817),
(4,	'DPL372',	2731),
(5,	'FRNS186',	1245),
(6,	'FRNS167',	1290),
(7,	'FRNS10',	1647),
(8,	'FRNS113',	1441),
(9,	'FRNS115',	1760),
(10,	'FRNS122',	1219),
(11,	'FRNS126',	1590),
(12,	'DPL535',	1352),
(13,	'FRNS129',	1529),
(14,	'FRNS13',	1601),
(15,	'FRNS130',	1309),
(16,	'FRNS132',	1622),
(17,	'FRNS133',	1382),
(18,	'FRNS134',	1684),
(19,	'FRNS135',	1437),
(20,	'FRNS141',	1108),
(21,	'FRNS142',	1009),
(22,	'FRNS143',	1090),
(23,	'FRNS145',	1260),
(24,	'FRNS146',	1186),
(25,	'FRNS148',	1161),
(26,	'FRNS149',	1166),
(27,	'FRNS152',	1050),
(28,	'FRNS153',	1225),
(29,	'DPL534',	1300),
(30,	'FRNS158',	1165),
(31,	'FRNS161',	1588),
(32,	'FRNS162',	1047),
(33,	'FRNS163',	1014),
(34,	'FRNS165',	1011),
(35,	'FRNS166',	754),
(36,	'FRNW05',	1668),
(37,	'FRNW08',	1668),
(38,	'FRNW14',	1200),
(39,	'FRNW17',	1652),
(40,	'FRNW18',	1666),
(41,	'FRNW26',	1649),
(42,	'FRNW32',	1644),
(43,	'FRNW33',	1629),
(44,	'FRNW34',	1643),
(45,	'FRNW38',	1628),
(46,	'FRNW39',	1629),
(47,	'FRNW44',	1628),
(48,	'FRNW48',	1584),
(49,	'FRNW49',	1584),
(50,	'FRNW50',	1584),
(51,	'FRNW51',	1564),
(52,	'FRNW52',	1584),
(53,	'FRNW53',	1564),
(54,	'FRNW54',	1564),
(55,	'FRNW57',	1564),
(56,	'FRNW59',	1564),
(57,	'FRNW62',	1564),
(58,	'FRNW65',	1628),
(59,	'FRNW66',	1627),
(60,	'FRNW67',	1632),
(61,	'FRNW06',	1668),
(62,	'DPL553',	1623),
(63,	'DPL558',	11428),
(64,	'FRNS169',	884),
(65,	'FRNS170',	829),
(66,	'DPL618',	2165),
(67,	'FRNS172',	1146),
(68,	'FRNS173',	1008),
(69,	'DPL653',	967),
(70,	'FRNS175',	838),
(71,	'FRNS176',	906),
(72,	'FRNS177',	1026),
(73,	'FRNS178',	791),
(74,	'FRNS179',	810),
(75,	'DPL669',	1288),
(76,	'FRNS182',	1070),
(77,	'FRNS183',	1170),
(78,	'FRNS184',	818),
(79,	'DPL688',	5154),
(80,	'DPL712',	9663),
(81,	'FRNS185',	1233),
(82,	'DPL751',	7087),
(83,	'DPL758',	1833),
(84,	'FRNFTE01',	1078),
(85,	'FRNFTE02',	1078),
(86,	'FRNFTE03',	1073),
(87,	'FRNFTE04',	1073),
(88,	'FRNFTE05',	906),
(89,	'FRNFTE07',	835),
(90,	'FRNFTE06',	894),
(91,	'FRNFTE08',	827),
(92,	'FRNFTE09',	834),
(93,	'FRNFTE11',	685),
(94,	'FRNFTE10',	745),
(95,	'FRNFTE12',	675),
(96,	'FRNFTE13',	658),
(97,	'FRNFTE14',	658),
(98,	'FRNFTE15',	658),
(99,	'FRNFTE16',	653),
(100,	'FRNFTE17',	648),
(101,	'FRNFTE18',	636),
(102,	'FRNFTE20',	636),
(103,	'FRNFTE19',	636),
(104,	'FRNFTE21',	636),
(105,	'DPL785',	2410),
(106,	'DPL806',	967),
(107,	'FRNS187',	748),
(108,	'DPL824',	1544),
(109,	'DPL818',	1833),
(110,	'DPL834',	5573),
(111,	'DPL844',	2628),
(112,	'DPL898',	2533);

DROP TABLE IF EXISTS `print_head_settings`;
CREATE TABLE `print_head_settings` (
  `print_head_setting_id` int unsigned NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `promotion`;
CREATE TABLE `promotion` (
  `promotion_id` int unsigned NOT NULL,
  `employee_id` int unsigned NOT NULL,
  `current_department` int unsigned NOT NULL,
  `current_designation` int unsigned NOT NULL,
  `current_pay_grade` int NOT NULL,
  `current_salary` int NOT NULL,
  `promoted_pay_grade` int unsigned NOT NULL,
  `new_salary` int NOT NULL,
  `promoted_department` int unsigned NOT NULL,
  `promoted_designation` int unsigned NOT NULL,
  `promotion_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `role_id` int unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `role` (`role_id`, `role_name`, `created_at`, `updated_at`) VALUES
(1,	'Super Admin',	'2022-06-11 14:10:00',	'2022-06-11 14:10:00'),
(2,	'Admin',	'2022-06-11 14:10:00',	'2022-06-11 14:10:00'),
(3,	'Employer',	'2022-06-11 14:10:00',	'2022-08-23 19:35:49'),
(4,	'Accounts',	'2022-06-11 14:10:00',	'2022-06-11 14:10:00'),
(5,	'Employee',	'2022-06-11 14:10:00',	'2022-06-17 22:02:45'),
(6,	'Common',	'2022-11-28 15:43:18',	'2022-11-28 15:43:18');

DROP TABLE IF EXISTS `salary_deduction_for_late_attendance`;
CREATE TABLE `salary_deduction_for_late_attendance` (
  `salary_deduction_for_late_attendance_id` int unsigned NOT NULL,
  `for_days` int NOT NULL,
  `day_of_salary_deduction` int NOT NULL,
  `status` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_deduction_for_late_attendance` (`salary_deduction_for_late_attendance_id`, `for_days`, `day_of_salary_deduction`, `status`, `created_at`, `updated_at`) VALUES
(1,	3,	1,	'Active',	'2022-06-11 14:10:48',	'2022-06-11 14:10:48');

DROP TABLE IF EXISTS `salary_details`;
CREATE TABLE `salary_details` (
  `salary_details_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `month_of_salary` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic_salary` int NOT NULL DEFAULT '0',
  `daily_allowance` int DEFAULT NULL,
  `house_rent_allowance` int DEFAULT NULL,
  `leave_wages` int DEFAULT NULL,
  `over_time_amount` int DEFAULT NULL,
  `other_allowance` int DEFAULT NULL,
  `total_allowance` int NOT NULL DEFAULT '0',
  `esic_deduction` int DEFAULT NULL,
  `pf_deduction` int DEFAULT NULL,
  `canteen_deduction` int DEFAULT NULL,
  `total_deduction` int NOT NULL DEFAULT '0',
  `total_late` int NOT NULL DEFAULT '0',
  `total_late_amount` int NOT NULL DEFAULT '0',
  `total_absence` int NOT NULL DEFAULT '0',
  `total_absence_amount` int NOT NULL DEFAULT '0',
  `overtime_rate` int NOT NULL DEFAULT '0',
  `per_day_salary` int NOT NULL DEFAULT '0',
  `total_over_time_hour` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `total_overtime_amount` int NOT NULL DEFAULT '0',
  `hourly_rate` int NOT NULL DEFAULT '0',
  `total_present` int NOT NULL DEFAULT '0',
  `total_leave` int NOT NULL DEFAULT '0',
  `total_working_days` int NOT NULL DEFAULT '0',
  `net_salary` int NOT NULL DEFAULT '0',
  `tax` int NOT NULL DEFAULT '0',
  `taxable_salary` int NOT NULL DEFAULT '0',
  `working_hour` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `gross_salary` int NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '0',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payment_method` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`salary_details_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_details` (`salary_details_id`, `employee_id`, `month_of_salary`, `basic_salary`, `daily_allowance`, `house_rent_allowance`, `leave_wages`, `over_time_amount`, `other_allowance`, `total_allowance`, `esic_deduction`, `pf_deduction`, `canteen_deduction`, `total_deduction`, `total_late`, `total_late_amount`, `total_absence`, `total_absence_amount`, `overtime_rate`, `per_day_salary`, `total_over_time_hour`, `total_overtime_amount`, `hourly_rate`, `total_present`, `total_leave`, `total_working_days`, `net_salary`, `tax`, `taxable_salary`, `working_hour`, `gross_salary`, `created_by`, `updated_by`, `status`, `comment`, `payment_method`, `action`, `created_at`, `updated_at`) VALUES
(18,	1,	'2022-11',	12500,	NULL,	NULL,	NULL,	NULL,	NULL,	12500,	NULL,	NULL,	NULL,	12851,	0,	0,	23,	9583,	104,	417,	'8:30',	885,	0,	5,	2,	30,	13035,	0,	0,	'00:00',	25000,	1,	1,	0,	NULL,	NULL,	'monthlySalary',	'2022-12-22 18:30:15',	'2022-12-22 18:30:15');

DROP TABLE IF EXISTS `salary_details_to_allowance`;
CREATE TABLE `salary_details_to_allowance` (
  `salary_details_to_allowance_id` int unsigned NOT NULL AUTO_INCREMENT,
  `salary_details_id` int NOT NULL,
  `allowance_id` int NOT NULL,
  `amount_of_allowance` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`salary_details_to_allowance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_details_to_allowance` (`salary_details_to_allowance_id`, `salary_details_id`, `allowance_id`, `amount_of_allowance`, `created_at`, `updated_at`) VALUES
(25,	18,	2,	6250,	'2022-12-22 18:30:15',	'2022-12-22 18:30:15'),
(26,	18,	3,	1000,	'2022-12-22 18:30:15',	'2022-12-22 18:30:15');

DROP TABLE IF EXISTS `salary_details_to_deduction`;
CREATE TABLE `salary_details_to_deduction` (
  `salary_details_to_deduction_id` int unsigned NOT NULL AUTO_INCREMENT,
  `salary_details_id` int NOT NULL,
  `deduction_id` int NOT NULL,
  `amount_of_deduction` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`salary_details_to_deduction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_details_to_deduction` (`salary_details_to_deduction_id`, `salary_details_id`, `deduction_id`, `amount_of_deduction`, `created_at`, `updated_at`) VALUES
(34,	18,	2,	188,	'2022-12-22 18:30:15',	'2022-12-22 18:30:15'),
(35,	18,	3,	3000,	'2022-12-22 18:30:15',	'2022-12-22 18:30:15');

DROP TABLE IF EXISTS `salary_details_to_leave`;
CREATE TABLE `salary_details_to_leave` (
  `salary_details_to_leave_id` int unsigned NOT NULL,
  `salary_details_id` int NOT NULL,
  `leave_type_id` int NOT NULL,
  `num_of_day` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `salary_details_to_leave` (`salary_details_to_leave_id`, `salary_details_id`, `leave_type_id`, `num_of_day`, `created_at`, `updated_at`) VALUES
(0,	1,	2,	1,	'2022-12-21 11:14:48',	'2022-12-21 11:14:48'),
(0,	1,	4,	1,	'2022-12-21 11:14:48',	'2022-12-21 11:14:48'),
(0,	2,	2,	1,	'2022-12-21 11:49:07',	'2022-12-21 11:49:07'),
(0,	2,	4,	1,	'2022-12-21 11:49:07',	'2022-12-21 11:49:07'),
(0,	3,	2,	1,	'2022-12-21 11:51:23',	'2022-12-21 11:51:23'),
(0,	3,	4,	1,	'2022-12-21 11:51:23',	'2022-12-21 11:51:23'),
(0,	4,	2,	1,	'2022-12-21 11:53:18',	'2022-12-21 11:53:18'),
(0,	4,	4,	1,	'2022-12-21 11:53:18',	'2022-12-21 11:53:18'),
(0,	5,	2,	1,	'2022-12-22 14:28:54',	'2022-12-22 14:28:54'),
(0,	5,	4,	1,	'2022-12-22 14:28:54',	'2022-12-22 14:28:54'),
(0,	6,	2,	1,	'2022-12-22 14:34:40',	'2022-12-22 14:34:40'),
(0,	6,	4,	1,	'2022-12-22 14:34:40',	'2022-12-22 14:34:40'),
(0,	7,	2,	1,	'2022-12-22 14:36:14',	'2022-12-22 14:36:14'),
(0,	7,	4,	1,	'2022-12-22 14:36:14',	'2022-12-22 14:36:14'),
(0,	8,	2,	1,	'2022-12-22 14:37:16',	'2022-12-22 14:37:16'),
(0,	8,	4,	1,	'2022-12-22 14:37:16',	'2022-12-22 14:37:16'),
(0,	9,	2,	1,	'2022-12-22 14:43:41',	'2022-12-22 14:43:41'),
(0,	9,	4,	1,	'2022-12-22 14:43:41',	'2022-12-22 14:43:41'),
(0,	10,	2,	1,	'2022-12-22 14:44:41',	'2022-12-22 14:44:41'),
(0,	10,	4,	1,	'2022-12-22 14:44:41',	'2022-12-22 14:44:41'),
(0,	11,	2,	1,	'2022-12-22 14:48:26',	'2022-12-22 14:48:26'),
(0,	11,	4,	1,	'2022-12-22 14:48:26',	'2022-12-22 14:48:26'),
(0,	12,	2,	1,	'2022-12-22 14:53:16',	'2022-12-22 14:53:16'),
(0,	12,	4,	1,	'2022-12-22 14:53:16',	'2022-12-22 14:53:16'),
(0,	13,	2,	1,	'2022-12-22 15:42:10',	'2022-12-22 15:42:10'),
(0,	13,	4,	1,	'2022-12-22 15:42:10',	'2022-12-22 15:42:10'),
(0,	14,	2,	1,	'2022-12-22 15:44:53',	'2022-12-22 15:44:53'),
(0,	14,	4,	1,	'2022-12-22 15:44:53',	'2022-12-22 15:44:53'),
(0,	15,	2,	1,	'2022-12-22 16:46:47',	'2022-12-22 16:46:47'),
(0,	15,	4,	1,	'2022-12-22 16:46:47',	'2022-12-22 16:46:47'),
(0,	16,	2,	1,	'2022-12-22 16:50:07',	'2022-12-22 16:50:07'),
(0,	16,	4,	1,	'2022-12-22 16:50:07',	'2022-12-22 16:50:07'),
(0,	17,	2,	1,	'2022-12-22 16:55:59',	'2022-12-22 16:55:59'),
(0,	17,	4,	1,	'2022-12-22 16:55:59',	'2022-12-22 16:55:59'),
(0,	18,	2,	1,	'2022-12-22 18:30:15',	'2022-12-22 18:30:15'),
(0,	18,	4,	1,	'2022-12-22 18:30:15',	'2022-12-22 18:30:15');

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` int unsigned NOT NULL,
  `service_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_icon` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `sub_departments`;
CREATE TABLE `sub_departments` (
  `sub_department_id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int NOT NULL,
  `sub_department_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`sub_department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sub_departments` (`sub_department_id`, `department_id`, `sub_department_name`, `created_at`, `updated_at`) VALUES
(10,	1,	'ACCSUBDEPT01',	'2022-12-21 13:41:54',	'2022-12-21 13:41:54'),
(12,	2,	'ADM01',	'2022-12-21 15:02:51',	'2022-12-21 15:02:51'),
(14,	2,	'TESTSUBUNIT',	'2022-12-30 11:36:47',	'2022-12-30 11:36:47'),
(15,	13,	'OPERATIONS',	'2023-02-11 15:02:17',	'2023-02-11 15:03:28'),
(16,	13,	'ROLL PACK - OPERATIONS',	'2023-02-11 15:06:52',	'2023-02-14 11:51:57'),
(17,	4,	'SCM',	'2023-02-13 10:54:36',	'2023-02-13 10:54:36'),
(18,	22,	'STORES',	'2023-02-13 11:00:40',	'2023-02-13 11:00:40'),
(19,	6,	'HOUSEKEEPING',	'2023-02-13 11:00:56',	'2023-02-13 11:00:56'),
(20,	12,	'PACKING',	'2023-02-13 11:07:05',	'2023-02-13 11:07:05'),
(21,	21,	'OPERATIONS',	'2023-02-13 16:31:25',	'2023-02-13 16:31:25'),
(22,	18,	'QUALITY ASSURANCE',	'2023-02-15 11:07:07',	'2023-02-15 11:07:07'),
(23,	10,	'NPD',	'2023-02-15 11:15:52',	'2023-02-15 11:15:52'),
(24,	9,	'MAINTENANCE',	'2023-02-15 12:02:52',	'2023-02-15 12:02:52');

DROP TABLE IF EXISTS `sync_to_live`;
CREATE TABLE `sync_to_live` (
  `id` int NOT NULL,
  `status` tinyint DEFAULT NULL,
  KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `sync_to_live` (`id`, `status`) VALUES
(1,	0);

DROP TABLE IF EXISTS `tax_rule`;
CREATE TABLE `tax_rule` (
  `tax_rule_id` int unsigned NOT NULL,
  `amount` int NOT NULL,
  `percentage_of_tax` double NOT NULL,
  `amount_of_tax` int NOT NULL,
  `gender` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tax_rule` (`tax_rule_id`, `amount`, `percentage_of_tax`, `amount_of_tax`, `gender`, `created_at`, `updated_at`) VALUES
(1,	250000,	0,	0,	'Male',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(2,	400000,	10,	40000,	'Male',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(3,	500000,	15,	75000,	'Male',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(4,	600000,	20,	120000,	'Male',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(5,	3000000,	25,	750000,	'Male',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(6,	0,	30,	0,	'Male',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(7,	300000,	0,	0,	'Female',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(8,	400000,	10,	40000,	'Female',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(9,	500000,	15,	75000,	'Female',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(10,	600000,	20,	120000,	'Female',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(11,	3000000,	25,	750000,	'Female',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47'),
(12,	0,	30,	0,	'Female',	'2022-06-11 14:10:47',	'2022-06-11 14:10:47');

DROP TABLE IF EXISTS `tax_slab`;
CREATE TABLE `tax_slab` (
  `tax_slab_id` int unsigned NOT NULL AUTO_INCREMENT,
  `range_from` double DEFAULT NULL,
  `range_to` double DEFAULT NULL,
  `taxable_amount` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`tax_slab_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `tax_slab` (`tax_slab_id`, `range_from`, `range_to`, `taxable_amount`, `created_at`, `updated_at`) VALUES
(1,	0,	21000,	'0',	'2022-11-02 16:35:15',	'2022-11-02 16:35:15'),
(2,	21001,	30000,	'100',	'2022-11-02 16:35:15',	'2022-11-02 16:35:15'),
(3,	30001,	45000,	'235',	'2022-11-02 16:35:15',	'2022-11-02 16:35:15'),
(4,	45001,	60000,	'510',	'2022-11-02 16:35:15',	'2022-11-02 16:35:15'),
(5,	60001,	75000,	'760',	'2022-11-02 16:35:15',	'2022-11-02 16:35:15'),
(6,	75001,	NULL,	'1095',	'2022-11-02 16:35:15',	'2022-11-02 16:35:15');

DROP TABLE IF EXISTS `telephone_allowance_deduction_rules`;
CREATE TABLE `telephone_allowance_deduction_rules` (
  `telephone_allowance_deduction_rule_id` int unsigned NOT NULL,
  `cost_per_call` int NOT NULL,
  `limit_per_month` int NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `telephone_allowance_deduction_rules` (`telephone_allowance_deduction_rule_id`, `cost_per_call`, `limit_per_month`, `status`, `remarks`, `created_at`, `updated_at`) VALUES
(1,	5,	0,	1,	'',	'2022-06-11 14:10:57',	'2022-12-20 18:22:15');

DROP TABLE IF EXISTS `termination`;
CREATE TABLE `termination` (
  `termination_id` int unsigned NOT NULL AUTO_INCREMENT,
  `finger_print_id` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `terminate_to` int unsigned NOT NULL,
  `terminate_by` int unsigned NOT NULL,
  `termination_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notice_date` date NOT NULL,
  `termination_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`termination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `training_info`;
CREATE TABLE `training_info` (
  `training_info_id` int unsigned NOT NULL,
  `training_type_id` int unsigned NOT NULL,
  `employee_id` int unsigned NOT NULL,
  `subject` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `certificate` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `training_type`;
CREATE TABLE `training_type` (
  `training_type_id` int unsigned NOT NULL,
  `training_type_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `role_id` int unsigned NOT NULL,
  `user_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `device_employee_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=270 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `user` (`user_id`, `role_id`, `user_name`, `email`, `password`, `status`, `remember_token`, `created_by`, `updated_by`, `deleted_at`, `created_at`, `updated_at`, `device_employee_id`) VALUES
(1,	1,	'administrator',	NULL,	'$2y$10$r7J.WrWd2NCtuLaz9kpjcurquLE13pVfOm/73vh2hxe.fn.YuUTfm',	1,	'hSDIWDEKnGK3hhZMR6i9Wx1ACtkDBuPgXRA4qDgmADeYPr5xUxxgPxZB89rQ',	1,	1,	NULL,	'2023-01-07 08:02:16',	'2023-02-06 13:27:35',	'ADM1001'),
(4,	5,	'MC006',	NULL,	'$2y$10$vXxCivss6IlLe/t1BqQgCOj52T.9BKAwLAwSxAfmNMFD8QVx.4CB2',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(5,	5,	'MC005',	NULL,	'$2y$10$EGygYBVczSByNg5D7DwS9.6fMsBBUYQOysRfBOryVneG2vEUOD.F6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(6,	5,	'MC004',	NULL,	'$2y$10$W57CfOPDRqbIK8mHyVGV/u0kqtlOPOU9cGFq7uSKhMcSqgQYQGGRK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(7,	5,	'VC005',	NULL,	'$2y$10$yDI5l9puWKOE7oTsKd8IQeMmpfKDR1OL.xcY3wf9Xi2Yv3aNFRsym',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(8,	5,	'MC003',	NULL,	'$2y$10$vk0f29UTrm3cODQHabp17exrfkPuyrrcKESzo.xAY7/p.iwYAqTtO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(9,	5,	'VC006',	NULL,	'$2y$10$85Fm5O17NSifNgGX0zTAIeIVHlVGsxxjWd6/cJmPl1.S4cdRt3gFe',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(10,	5,	'VC007',	NULL,	'$2y$10$pnPEhVt/JRZVM33ifbEOmuHgCrRWvSkqRnLBPkKj14fIYl/CgXjkW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(11,	5,	'VC008',	NULL,	'$2y$10$41s.FpjFFmZkUSiLRqSkKOc7VIR.2rzIscOLOyyUy/r5poWQ4cy9q',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(12,	5,	'MC007',	NULL,	'$2y$10$.k4cyMwmYrRZJobCR5QO9e6w0kAb8mTYTWoRp8azysSfg6cG9gHpW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(13,	5,	'VC009',	NULL,	'$2y$10$C.002XJkKpxStLuM2OTHzeXTN3VmByabS/fiIEdAj9XMv0U9Wfshi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(14,	5,	'VC010',	NULL,	'$2y$10$QfVufDtk1ftTCxR3txX5WOheJyvuwEtoa3bYp.pm/Kjol36a9ExKS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(15,	5,	'VC011',	NULL,	'$2y$10$.B/2iDYOm1lhLAftZwYU0eVdJuu4gbltwwgwLWjbT08dqiPHcGs1C',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(16,	5,	'VC012',	NULL,	'$2y$10$k7W5B/lvaYbPoclJ7Od2PeIezSnmz4uG0at.2mHguqIEe1CXI2LR.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:36',	'2023-02-15 12:24:03',	''),
(17,	5,	'VC013',	NULL,	'$2y$10$XnsDju6GxYcg7Fhopq3lUO5uKCvPIUMz/FZoyM5N3KXoLVH9uRClS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(18,	5,	'VC014',	NULL,	'$2y$10$C8pGgqVp.1G9qht3VMyglOSNiZ6BUOsPkOEOBxTOj0rwQStmByJ2W',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(19,	5,	'VC015',	NULL,	'$2y$10$vaykAmt4wjDbUB433NnGfOEwmXdIPTgkhDhLubU767LEEwv85RoDS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(20,	5,	'MC010',	NULL,	'$2y$10$.T6uF5Uh5yMGaQyC2CZuNeDSkSuJxMSErUgNztqkfujpBszh.tALO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(21,	5,	'VC016',	NULL,	'$2y$10$76rz0fEHadMb54GeVBiMC.NHwadA6g7YYbSl3.pULPuxNs4GmMSKK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(22,	5,	'VC017',	NULL,	'$2y$10$JM1UB5uj0k8k64mwcfaSe.7BADF3MiKKbziEJOMa7z.Alw90zhaMu',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(23,	5,	'VC018',	NULL,	'$2y$10$3r.i566m9NZk/AXV0NudCuHFa651Vc8l0x93FesQDgpEGxmKauopO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(24,	5,	'VC020',	NULL,	'$2y$10$0ErxfjiUMt5qDjR1EOIOEOunKgJlOMZ7LVSwE59csWpJDhUUNq/WW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(25,	5,	'VC021',	NULL,	'$2y$10$9g0UPk6RkO.g6HyrIl4ip.U4lrY811dEwPWuOofnd/9k0yNUIpTAS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(26,	5,	'MC009',	NULL,	'$2y$10$ghOHH.M3zEi8p5lD8NaUlunwyyDnVUNpJlrTcHmeGAuJzBAmxgBjS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(27,	5,	'MC008',	NULL,	'$2y$10$/VpdK8q8m9b3KoQUXXz5b.VzZEhcCHfcDyTnrN2PhJ04NvzOeMqdm',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(28,	5,	'MC012',	NULL,	'$2y$10$Ld3RlBE3VqBTzy64bCAbceu4MApdS6PVTdwTeRHdhapVWezhoUHAW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(29,	5,	'MC013',	NULL,	'$2y$10$uk6ozj0Z/eYMhn2pbounLO3R7Uqdpp8030FLfC3Yze5akv50U5Iqa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(30,	6,	'VC022',	NULL,	'$2y$10$ZBdZObK1c14blpygWn6KwOgTENAk2N/.Or7rbYWBlXFxwf4aqGNFa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-06 13:27:35',	''),
(31,	5,	'VC023',	NULL,	'$2y$10$NONCmHY6n3hQHuSGbdek/OLvZC0Z2z3BCO03VSuJkn2pT2MYOjeHK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:37',	'2023-02-15 12:24:03',	''),
(32,	5,	'VC024',	NULL,	'$2y$10$3TO.KG2CmcYbPlxeyqJeZ.iNa6XCg2moTHBOQMZUsD0.dFilwmeO6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(33,	5,	'MC014',	NULL,	'$2y$10$uWP4IJDoAQhfx0mpu/P.AunDdvkw4dkep7uZi8p6rgbt/EtKOTswy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(34,	5,	'VC025',	NULL,	'$2y$10$T7I4WksphMhaPoE68LkwuuYDiJ0Mmm1XqatPYB449K.LeGLYTjPPi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(35,	5,	'VC001',	NULL,	'$2y$10$MFnbUmQmVRNUi9ZCHD9wO.qaL4lgrlBgPhc6Vdm7fXlktW.4YOOZG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(36,	5,	'VC002',	NULL,	'$2y$10$WNEhzrqcdwjExA7KS0WkJeRqVN3vDM2MSzMacCvMiFRb3Gkm0IYpG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(37,	5,	'VC003',	NULL,	'$2y$10$54m6Lt4SBugLMx4TgoV7XuE/HBjxzkwHl5aZ3EUyi7AwLIdsWbcna',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(38,	5,	'VC026',	NULL,	'$2y$10$g6S6SIT4oeDcJvkCxsynnOrIc0MOOolIA5RHahnFMd56/h92MllP.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(39,	5,	'VC027',	NULL,	'$2y$10$7cQiC4xYMNiiAoup3qeZJummmoVG83HPzswIlRzKcK0W4lBa0DJDa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(40,	5,	'VC004',	NULL,	'$2y$10$MPwIXg95s6nDP1qVhCS/J.KntjkHqpsm3rmxAd4eKx48Scg7cCiKa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(41,	5,	'MC015',	NULL,	'$2y$10$ux8aY0iRb0reMqsRIiL/duJT5Lw5f5rXw.VKM8IAjPI0vfWCmeM7W',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(42,	5,	'VC042',	NULL,	'$2y$10$JTru3W9U.nsBUxmcpFm4OO1XhsEyJrqCiTCl8EZTaoZclZ8KFiyee',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(43,	5,	'VC028',	NULL,	'$2y$10$dWvTA2BS3Y60UfFOksMVAON5UK/rpMxW3mVTHx0OEX11Tb0gN6ILW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(44,	5,	'VC029',	NULL,	'$2y$10$qwV1Nxg2TPnA0Yi1RANA1OzZ65j9XpyzKgvXlJ7g8nxWwV5AJIXaK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(45,	5,	'VC030',	NULL,	'$2y$10$C9vHW.d1lI/Qt7u0xfvTkeNzIgOraHT7i85K/T2HdOjbbEScAy63K',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(46,	5,	'VC031',	NULL,	'$2y$10$Zkkx/2KSDyJEJvY1B/xMSe/X1txGXceMe2CU6IESXhTeKB/Lp81yy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(47,	5,	'LS005',	NULL,	'$2y$10$ilrQN9TsxgCTGpTw/3mXserr1FpVJJ.phUKuYyKwU.IMWzHRT79z2',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(48,	5,	'LS004',	NULL,	'$2y$10$BUngUh9qruX058bo5.CpJ.6QlkwaMIj9y/lsPp3ViylTYMIaSIZXW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:38',	'2023-02-15 12:24:03',	''),
(49,	5,	'LS003',	NULL,	'$2y$10$xKnVw1kEZmQJQVtFi.hTpOpO2gYFfOecve1Bk7mcliLF9LEpWnWvC',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(50,	5,	'LS010',	NULL,	'$2y$10$SZXvtrWq.t3cid5Otnn0lOL9z7zPGBbmBc.FiwyMJaxoHLLH38XHa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(51,	5,	'LS009',	NULL,	'$2y$10$yuDSu0Ch8BBQgkefMzbDC.5ijWKGkoQ3tNQShqA/bNtopin8tSArG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(52,	5,	'LS008',	NULL,	'$2y$10$3at8JbTfXD1LSno3msl0qeJp1O7mPpb1CTcwdjdlJu0zNYj2QIDDi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(53,	5,	'LS007',	NULL,	'$2y$10$Kd3QfU2WByaDtDpAmkt1CedI8zROgwcL2otFFTD/azOC.UYqPfPXu',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(54,	5,	'LS001',	NULL,	'$2y$10$.fvY9qrpx6DcbPxhtZpm4ua1s4nbeTDQnsFm5fPvVpbeUQ156nMSa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(55,	5,	'LS006',	NULL,	'$2y$10$fGwzt930wXbWlAbVLCbJheXcuBniptBVz4AxyeeailCK7WHhydB5y',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(56,	5,	'LS002',	NULL,	'$2y$10$mePcNaxTmROjU8DmsltOk.1Up3iuh48TuuijCfIRlZybtpe09F42K',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(57,	5,	'VC032',	NULL,	'$2y$10$lfQdy1ezAGUNlK5W55So2ezLCM3qYrbxMwd/BwwOGkgEDEzcJiD.i',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(58,	5,	'VC033',	NULL,	'$2y$10$3JQnMoZzT.rbVOcVfL73buV76GwyERZ0wtwklp/Xy7IvZOEFHVXvu',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(59,	5,	'VC035',	NULL,	'$2y$10$B/375WSUMjH7mH0cKzt0s.045bHgfmtIws1rNjm0GoFbfq1ulED6C',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(60,	5,	'MC016',	NULL,	'$2y$10$iuAKPAWkcULTFbEp7QfED.wjUtWjj7t2Ur3tXkW/g6xpInZ15yqi.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(61,	5,	'VC037',	NULL,	'$2y$10$Nhgo0zT0Bcc4pXEoyUuCt.aNajDfnnXit8Ytu.Kh3uH87nlLVQZOi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(62,	5,	'VC038',	NULL,	'$2y$10$pPsDrOlpfvHsYaSHJWKRLOkuzyLC5tdtR79lMr0YDgcHRvAzSMDwW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(63,	5,	'VC039',	NULL,	'$2y$10$piXON.uamfnDUgaQgDuVOeP1pTBoMQI81X5HZnbpd4QZzeniCZku6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:39',	'2023-02-15 12:24:03',	''),
(64,	5,	'VC040',	NULL,	'$2y$10$mvR3uIk7MiePlggNNmfDEOVhOTlYbR2.fujj4XReHNqFEvUZMvNgW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(65,	5,	'VC041',	NULL,	'$2y$10$XAD.OsfjYLxt2WoGE/WO9urdhgD4xX3pNvlvw6jSiaPvjwdoAzNVS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(66,	5,	'VC043',	NULL,	'$2y$10$xCcXiUaYJ3VtNBXZXMfEeeSuRUm/QY3x6Dh8VZ4ELpvaTtgXBSIRW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(67,	5,	'VC044',	NULL,	'$2y$10$Mh2U7CmE5gDXMieLj1VKi.jNIbf1vgKoax1lRSjXBQEqaJliTqEZK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(68,	5,	'VC045',	NULL,	'$2y$10$PZoWYpWtPQKPUWapL3UeKudEoGyxd9k3/0M6emjyiuXjz3BlaANwa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(69,	5,	'VC046',	NULL,	'$2y$10$SNs9ufNtUSbpiiv2BUtAlOAj8JDfJAxUIe76JaZdKM.Bh1ZS223om',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(70,	5,	'MC017',	NULL,	'$2y$10$Z7ic1ZHnLvZkSsy2R9FBYu9xqgZBAOclROwb35FrcOcEj4/zSCcP2',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(71,	5,	'VC047',	NULL,	'$2y$10$DqjV9A73JgUVH6f.78LpquGSksNgigIBNVUfAj7AZC.tJK1DvcYIa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(72,	5,	'VC048',	NULL,	'$2y$10$hxugWgmRib1MFqlDZVf0xe1cFkSLj.y9AhgUI28haXnL8XZHLeXvi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:03',	''),
(73,	5,	'VC049',	NULL,	'$2y$10$OjvmtNlMwXdL0T5rB/G2l.R5tZ7J9aZhU3KgZh/4T.REGNaUmHPNS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	''),
(74,	5,	'MC001',	NULL,	'$2y$10$NT2FP8q34du5yanh4cdvrOz2YMEw4BRKWqUApp3AkfwWYbihS.jIy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	''),
(75,	5,	'VC050',	NULL,	'$2y$10$93ehQ8ZrsrQ0mxr/M6hHqOQinw/Meg6RbK5U.1Us5LXXas.Cfyx2i',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	''),
(76,	5,	'VC051',	NULL,	'$2y$10$rg54Htu4DRKVwv6.wf4q4u4R4iE3E4G2RTgtwDOJ8oylnCuNGbMYC',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	''),
(77,	5,	'VC052',	NULL,	'$2y$10$Pb2piFXpvZq8RIh9Rzar8uFjlc6xEZ9lgz0ohg2vkyTC3cOwErTVS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	''),
(78,	5,	'VC053',	NULL,	'$2y$10$Wb.Zf6oTe0627e4KsQ3Tru1RmGNUEo80wOGXYIsHzOw2yEcIoCtBi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-15 12:24:04',	''),
(79,	6,	'VC054',	NULL,	'$2y$10$JCaha/PUV5BqrVQfo3Hxmeo/DqQU2h.P8OlfjbacWunUfnImOmuim',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:40',	'2023-02-06 13:27:36',	''),
(80,	5,	'VC055',	NULL,	'$2y$10$ynBDzdbErOpgNLPP1Dv0vOfm1pWYdQrx9TKeQHleoQWTYpLRtMRg2',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(81,	5,	'LS011',	NULL,	'$2y$10$SwBlynOW86LA9Y8umhc19.rJvWfN5Tm9.eSLrDC5aBdK2FPqQiB4S',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(82,	5,	'VC056',	NULL,	'$2y$10$Ag2t5YJAAfu9FnMKnNdhH.FnoofDGyTFqS7mjoKR08jilqoWkE9ja',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(83,	5,	'MC022',	NULL,	'$2y$10$aK7QwDGFMPfIQUmeW6BpsOvi0RK6/vadMvdSwQnqfF1gmF0/O/Z0y',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(84,	5,	'VC057',	NULL,	'$2y$10$lGzD9dg5dewb70dV4RkcBeOTbB8RlP1AHAoEBu0H7evTgJyv0K.cW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(85,	5,	'VC058',	NULL,	'$2y$10$knTkVXICTycixS7T/JdEquI653vBINsGGCCDLQZIA6QVsdUBjbl.y',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(86,	6,	'VC059',	NULL,	'$2y$10$fIQ1NO47.oEP.RU.ADVEYOMNLs4YmALMspla8SNV2t4iL82D2xH9u',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-06 13:27:36',	''),
(87,	5,	'VC060',	NULL,	'$2y$10$65V99ti8IXEo0vcomGIGNukVrTtHyHv8IIQCiiyc/Wykou9gLs13a',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(88,	5,	'VC093',	NULL,	'$2y$10$b7OoQQYuejvgkz190HjCfu5kSsIxx2AtNq/oaAjhy/6rjz5T1jYj.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(89,	5,	'VC061',	NULL,	'$2y$10$4MdxgZIsgu4yPrUy5t90/eQXN9bEn23kjis0KeTSMTz0.UP1J1M4u',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(90,	5,	'MC031',	NULL,	'$2y$10$ML3upXJTX.QRxNJxJlk95.S.NRzPklw852oevfqFDJ4hHhiZSE196',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(91,	5,	'VC087',	NULL,	'$2y$10$/1uECmDK5fK7Tf6nizpIyegedeM2UpA9o2Wvo96LmdyiZP8Wzv/Rm',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(92,	5,	'VC063',	NULL,	'$2y$10$R1Ses6fes.p861FqEOkBTeUbSIQ2tWtUm6rmptUkX1K6NzUjqxr7C',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(93,	5,	'VC095',	NULL,	'$2y$10$RudShZ/Vt2p5P6k4XolYfOWW0SelJNRETrGf/y/hmPFO.nxLq8PK.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(94,	5,	'VC064',	NULL,	'$2y$10$umjm2oMBbuAe.Sudorlpz.iZ.sHg8T0MgGUSmwOl8IFSFFdHjJh6u',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(95,	5,	'LS012',	NULL,	'$2y$10$SsPBrigCcpr0qmWQxxXG/u9JGUZM8.zUBl8mIL7FRXGNppQGPNpXy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:41',	'2023-02-15 12:24:04',	''),
(96,	5,	'VC065',	NULL,	'$2y$10$Qs74G2E7buvo4iIZ/dE0/e3vXH0jtjelVUIfBakuzhyWi875qNzVa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(97,	5,	'VC066',	NULL,	'$2y$10$rm8tkwJcft6Z.bvgHFgq/uKTfv4mOb7wr5Gsv4rs15kpJouU1PEiG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(98,	5,	'VC067',	NULL,	'$2y$10$o12sbIGmxHPG2ULImJr3oO18i/VSl9sI7dGSbkaUUnv7KZNV8Qp9q',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(99,	5,	'VC068',	NULL,	'$2y$10$wrmDYx7rmv4B8rRKbfEykeJfjbQbEMZcOvTqCja4.iIAA5P7s/t9K',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(100,	5,	'MC029',	NULL,	'$2y$10$K5xuISiVN4CBJCPcP8o9ie332rZGx.6p3aI/1hlko26ql03yOf/Gq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(101,	5,	'MC024',	NULL,	'$2y$10$00R/6kEFkUZjCTYsKCJ1Te6eOklI67NX9vfJHVUH1UoMUjp7167Ga',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(102,	5,	'VC071',	NULL,	'$2y$10$U3vj/i2s2E6WK2KomjBK3uh5Uc0XqjLuyJBOSeJ5TxdcJh7wXtv2K',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(103,	5,	'VC072',	NULL,	'$2y$10$9VY.rRbdK3bCk8Qfvy4cx.rrrPeDv/vnoJPfReUgbJvugcHsSAoJi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(104,	5,	'VC074',	NULL,	'$2y$10$D5q7tn5wtjuFYtuiaqu10ukt0UnnQCYwj1dy9eGQy1lkQ4f5kqmCa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(105,	5,	'MC002',	NULL,	'$2y$10$QNoc8qvKW6vejKJMMmMAmu3Z7zd65gUxgna8Zlu0Qc.3lDWcQbgIK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(106,	5,	'VC104',	NULL,	'$2y$10$ckYTxea5ByPNmkuRe2dU.eUguRm6GoQ/OQh3TDsqULOmMsC05eewy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(107,	5,	'LS013',	NULL,	'$2y$10$f3kf7MnbppzQedOvB1MLa.dZ.Auzb2IHYM8qwJbl3u.xjffP2yOGq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(108,	5,	'VC094',	NULL,	'$2y$10$bcBHK4hZs2LddDLxMTOiV.IkK3ZYePZdmPPSuQ1UvTJ1lgVbzFZfa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(109,	6,	'MC025',	NULL,	'$2y$10$4UGRTQzAnLJjzlJ1zYzsTOWNG8LPSJCt0GOb4ZBfMLIszNv9sZEFq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-06 13:27:36',	''),
(110,	5,	'VC102',	NULL,	'$2y$10$YZC87c1rggiIQMXeTdZ8YO42Rt0oped1hd1E/ntd3h15kwsHx2sne',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(111,	5,	'VC076',	NULL,	'$2y$10$juFZ6nvsJITjPNF34Tz3zOk.kKybaf5E.Do1fv8WJnJAiyduhN/b6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:42',	'2023-02-15 12:24:04',	''),
(112,	5,	'MC026',	NULL,	'$2y$10$V7yMFMh05czg4ZAvKJcRB..TgN8Vp/tWFJxg7sfZieyxq3X/pz51u',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(113,	5,	'VC083',	NULL,	'$2y$10$teLhA367qcRjp1Tl1ddCD.yTiO74xM3g7D5aRj3W4UhOHbGfQQI.W',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(114,	6,	'VC089',	NULL,	'$2y$10$wwEkoG0lMNYzq5ppElCd5urcNGhvdTZxYpSUgMwXNkG9zmttwTEie',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-06 13:27:36',	''),
(115,	5,	'VC097',	NULL,	'$2y$10$UMwL043RZYgYEQTa3A9Rcuo4FDIGsm4pnCxCjHOIj7fuFp.58zAg6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(116,	5,	'VC118',	NULL,	'$2y$10$pA9wFt5TjJbr6W8dF9nCxujDELukLtCmaadtWzhU2g0BEtlx7WN0W',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(117,	5,	'VC019',	NULL,	'$2y$10$1PQTkOKZgcOZfOT7fm3Qle7.7/atTypR/i..VObqFb6Cislv8OoIO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(118,	5,	'VC084',	NULL,	'$2y$10$M181K6C51s1pRhXI9rr0GunCDKuEycxlH65Ri24csGmP6LCGV.96m',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(119,	5,	'VC101',	NULL,	'$2y$10$mvB40EJW5oWQWzlMUHdr9uQ5GirQQb7ZqJFFLWbaUL/yHX3xL1lE.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(120,	5,	'VC100',	NULL,	'$2y$10$3/HfVXt6co8NsumGCU3gYuSl7uoNKDEAxKfdqFoFioTK7ayx8xZOa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(121,	5,	'VC092',	NULL,	'$2y$10$UlJAA24nyWWDi7.iq9ScmOCNvZLiF676/BxcG6c1cZ91y0mUxIja6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(122,	5,	'VC121',	NULL,	'$2y$10$TG0zAKqLRmK0P5Jw8hpHi.sV5MBMIWtirwABNer.xLqgzo6ihGRzq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(123,	5,	'VC148',	NULL,	'$2y$10$RITQXOE7do1tJyxYIS0hZu.ySYdq04g/XIKUxiVs07OtJMSO4el9i',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(124,	5,	'VC099',	NULL,	'$2y$10$s1Dxf8p.FbumTPT/1/IVOu/zCZz1HanYtYiy70YaUw43HhYRCWrdu',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(125,	5,	'VC150',	NULL,	'$2y$10$NnLt3IUpYodlMgZavkv3De7S4ud9slHyTUKJpy7I1WZ8i9HW21.UK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(126,	5,	'VC120',	NULL,	'$2y$10$SiHMXKqS6w3yObcZf0xFFujreDjQEabvPKzVJV1vCu2wlpWrP2Rfu',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(127,	5,	'VC078',	NULL,	'$2y$10$yvCD66ktDUl9hjfX7.27UuXK7EpvOzte4NZ9708JXKLEjXXhlNKnq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:43',	'2023-02-15 12:24:04',	''),
(128,	5,	'VC090',	NULL,	'$2y$10$3aWw32Rxaci29ztM4NmV4Oa3A7BmRfCGqtpkKXyAavQ/R7F75SZ56',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(129,	6,	'VC107',	NULL,	'$2y$10$UKl3INLxSYsKBkNl1.4H..8x0C2dNcEulaBn4rGGcAkN50vJlcc6K',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-06 13:27:36',	''),
(130,	5,	'MC027',	NULL,	'$2y$10$tsWXrY7LgPRgZwSD.fI4eejovCyUjp9jpXIv8wMFDaibdb2T0BsY2',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(131,	5,	'MC028',	NULL,	'$2y$10$NT/Qp8YZdHvdLrDfGDWFXOpZRxcn1ho0CgsT1cOKQIOGQsKp8a7eS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(132,	5,	'VC079',	NULL,	'$2y$10$uXjh9jkT70EyNjceyDTJBOnLqcNjCnG6/0/qxXGpiYknlNvKOWNU6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(133,	6,	'VC081',	NULL,	'$2y$10$5IZ/1aIfAkNLp6ku1V5vZu5Wk5scG7dn6uItIUcRnoniaBTQGnJI6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-06 13:27:37',	''),
(134,	5,	'VC123',	NULL,	'$2y$10$.0kn1PIXqtejRduW1JXPKe8hu1poxjxeZFoB.p4ds9utCat3pjVEa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(135,	5,	'VC088',	NULL,	'$2y$10$Mx4H2ohs36E30si3M7DcAuWumq8Gk26dIU739qmhLtG6sUm5AQaG6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(136,	6,	'VC069',	NULL,	'$2y$10$qAnW3dRomJQGUIZPMqWsUOqeXZNlCcpYLLNU06XImKGZCSiOiSaui',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-06 13:27:37',	''),
(137,	5,	'VC091',	NULL,	'$2y$10$NTBTKt6sExI/a9zdz65zC.un3.sntinSctJx6atNsIjOzF.esAfaO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(138,	5,	'VC119',	NULL,	'$2y$10$ngVtuSdx1I.WrbN/LqN0ke9GfjbHLQJLUl9Kt6LuNC3fgCApNWstK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(139,	5,	'VC108',	NULL,	'$2y$10$jt1kENJrEbBlXRDdW6BkC.ULnwCXXg7dfRf/jjgStbfGn0rKo8GVG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(140,	5,	'VC082',	NULL,	'$2y$10$1UmotoHzPs01KNXH/w86POICdo.OO.JnkyAPZucHZELx4SK/xjjJ6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(141,	5,	'VC034',	NULL,	'$2y$10$uMBdQUvLFs1vby/uUC4UseFaAfWQW2wqhbhe//hnhtC4U7GUS9SYa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(142,	5,	'VC126',	NULL,	'$2y$10$w48GAhz5qZGzFqtrjDrUi.P20LBwCovOfYeoJW8qAqpdDfF1bTQkG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:44',	'2023-02-15 12:24:04',	''),
(143,	6,	'LS014',	NULL,	'$2y$10$hFKsukU3OUEtGNe.LEX64OkNNPMcJ4jhOkwN4Mhi.wsORej4SYfNC',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-06 13:27:37',	''),
(144,	5,	'VC073',	NULL,	'$2y$10$D5fF.OhmcIL/YSmn3gTXyeOMi3xIB3jCNdN4qmRYxIC/Gd.p4CFDy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(145,	5,	'VC106',	NULL,	'$2y$10$dUNnYUJBWUhxrcKLUhgH2.brtPb2ogQlv6.E24xr4C3dI6IUAI9VG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(146,	5,	'VC122',	NULL,	'$2y$10$Lx3yurlrpyWSlNeLPoSanO64lAWWwPOSldm7cez.8sxuqfyrDShO6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(147,	5,	'VC096',	NULL,	'$2y$10$aDNq7y/Llk.DEzKemxtcH.eZu.ATW8.zkpTE0zOEYwJ7pCMbmHTt.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(148,	5,	'VC116',	NULL,	'$2y$10$B5JAC9wDrM03zMd9u2j/o.HEUyH/9/hSBRyA1.P739ABSy8cVlZCS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(149,	5,	'VC077',	NULL,	'$2y$10$rb/B.BCH2mwml6tstMn9wuVm4yXbaADVWSY8RmFhef2kKvjsRpjKS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(150,	5,	'VC070',	NULL,	'$2y$10$wD6Ccoiwemrmul18VnhL6uDbD/XzKXvmHjKzv.zYPoea20Trc8ONy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(151,	5,	'VC103',	NULL,	'$2y$10$d4V9Q9EthQSKzUi5UCxtg.utOpW/XJmO8k3HuFvcy/74JUB7DYvze',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(152,	5,	'VC098',	NULL,	'$2y$10$93oHzylo16vwrTEGy3aIQu6jsBk8mv25kuQzAqp9BCWsrpAmLXVT6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(153,	5,	'VC086',	NULL,	'$2y$10$A61HtW2xM2ldsozi7qvQSu1aSPWrlJcAwD0IlCw2KyHCypRBD74gW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(154,	6,	'VC085',	NULL,	'$2y$10$7RTtE1mohx1Wy.vhZDHaVuH/A/FTIDwqCeQrrRzjsHs8g.zgV8D7W',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-06 13:27:37',	''),
(155,	5,	'VC105',	NULL,	'$2y$10$XqZKZaTY54ADmo3cAaGhXeecuyX5A1ULLLtbsfkbkUyERts2jXE5C',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(156,	6,	'VC062',	NULL,	'$2y$10$ZDOHAB1hBwNzqwAGIyTdtOu7csE0Yvm1F3Fjikr/g2peT9/wcYYkW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-06 13:27:37',	''),
(157,	5,	'MC032',	NULL,	'$2y$10$hNemWej8FU0GeURvudoYXethltsKQJ9tn0d82M1r/IVhsx.9gmuOS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(158,	5,	'MC023',	NULL,	'$2y$10$1zbo13JXF6OF/UZsBnveW.MDYd/ycmtjHFQA8m8WS6UB2yI.xirUi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(159,	5,	'VC132',	NULL,	'$2y$10$d1LeCXenWcqVcIisF35OcOCJ7m7BL92J1CCwbPKm2/LzLlzmA2pDa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:45',	'2023-02-15 12:24:04',	''),
(160,	5,	'VC139',	NULL,	'$2y$10$rciTWWaQ5rm0M6VkLJTOje7VNOKGWOMfOdOWv/ktRQwvx4KN/TAOS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(161,	5,	'VC145',	NULL,	'$2y$10$JD38yfTvp1y6f5EKRS5nru/eGSHbSCd6ERL6.myOmigLsNI1jqnN.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(162,	5,	'VC143',	NULL,	'$2y$10$MZYpWkkXGRLLs.oOFpM0p.0a5iwUf0QhUeZamEAZLRuPMlhLhmcOO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(163,	5,	'VC144',	NULL,	'$2y$10$NfmDqMDKXYFBQqABJz0.IOAbgH4i6HWLEdiz5ImNk3C4YHG0eOy7O',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(164,	5,	'VC154',	NULL,	'$2y$10$U763kVTvYaXZlCGiqtCyZe.gYgpz2S/5mRBPbhStBKxu470F4P.nq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(165,	5,	'MC033',	NULL,	'$2y$10$lTKTTz2vpHZPcQEmyynzQ.q5wcNe3Mdl.GYkQvwt5VVeV9wJzkJfa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(166,	5,	'VC161',	NULL,	'$2y$10$TCNfQNLqJRspgcOiSBl7Yuiaf6lNomKcJhRLXKc.ddLviuoDM5.6q',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(167,	5,	'VC140',	NULL,	'$2y$10$VN9DTu07tDhykyDtzWv3C.PYS.eXCbFaRR/qn98EGevKkpbja4nqO',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(168,	6,	'VC141',	NULL,	'$2y$10$hL9OdbWMJ5w6.e1Clh1c/uGmh5Vch4vAkc0iK5exXiREpPF0udrPS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-06 13:27:37',	''),
(169,	5,	'VC149',	NULL,	'$2y$10$ZxQAm/03pFowub5xp5YrneVhEIlpc/ns4yCKhadEqIyLexKXbMALK',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(170,	5,	'VC131',	NULL,	'$2y$10$k1XvvyQLhpH6exGa7LPrbO49mg8zMa/r/lgE8DKg7S4n3adp.aMSa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(171,	5,	'VC152',	NULL,	'$2y$10$zxLAdCprOZZkBBiO/kfUbuEdO4lNuRwttEqytcBuQdFHZDmIQpilW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(172,	5,	'VC151',	NULL,	'$2y$10$KyBVWz5FnUJSbTn4HGm/Xu8RJZ/kuVwDMo.etkropqvvxkwcbpN/y',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(173,	5,	'VC146',	NULL,	'$2y$10$AoBJZtX0uQWL2EPqBQS5veR64g9CcVlLBrk9BkxT2nUKhvyyh5MSy',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(174,	5,	'VC147',	NULL,	'$2y$10$Pd56QBoYYWQCAdhlLXs5f.N4S2TeRrL6EZTFqTYj2kQPQYNyk8L4a',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(175,	5,	'VC142',	NULL,	'$2y$10$/AuujoMvyOEDvSl50/875.E84FASxJsSn0i0vaT6B8PLf8xkJo6tq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(176,	5,	'VC127',	NULL,	'$2y$10$8nY8DZ9Ic./pOJKsx3cSAOzV7j4o70RVAj.Hgfm3iJu71LrZt0Bx2',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:46',	'2023-02-15 12:24:04',	''),
(177,	5,	'VC133',	NULL,	'$2y$10$HtVRLliC8v.k4GdjGDESl.VfJoD2g8W4a9k3tBbkvpIDSpOf2Ixe6',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	''),
(178,	5,	'VC155',	NULL,	'$2y$10$/WPWrlAlGdIZnNxd8m0g7OzvyBwGh9KPyzMDX9A5m27UA9.bX9/rq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	''),
(179,	5,	'MC018',	NULL,	'$2y$10$Efp2Hvzpr71nPl67uG9fYe.aX98wfY994DKuaBGfTAV7BA9qlS2Uq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	''),
(180,	5,	'MC019',	NULL,	'$2y$10$FRjuO3LEJDMmPgtg11k6CO8uauu0MRIDga0WuOyKqJRYY9b5Vn4RW',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	''),
(181,	5,	'MC020',	NULL,	'$2y$10$.gXL6iHr9AaBZFN0PkPyFuE3Ybs59oqAN/OmSvkWvQBsawcQoHusq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	''),
(182,	5,	'MC021',	NULL,	'$2y$10$g1u1waOl0yaTdNe8YoXITuAvqeVS9PjaMCl9gfmHsZpLzt1dL0/HS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-15 12:24:04',	''),
(183,	6,	'VC115',	NULL,	'$2y$10$M0m6D6TJT3QDXqtpcgJPTOhIt9wU/97vWkwOp4bNj.kDfbXDhQ66q',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(184,	6,	'VC114',	NULL,	'$2y$10$rTZQv2qxzTPePssoOe5tFuvvNcw3SwrYNodpYm0YNKs1dp7HgA7mq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(185,	6,	'VC112',	NULL,	'$2y$10$41glyP4QCC0Iypk2b3BMpuSxLNubXB/iHP2Vp4st59ruOZb8bph9K',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(186,	6,	'VC111',	NULL,	'$2y$10$8PvCNCLMxR7m.BoKhVwobOSPBjxDKyafL.KtuyAW6iLcoxP45FtPq',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(187,	6,	'VC075',	NULL,	'$2y$10$frZxgPDKEaPmtx/4Z9uYveQUJ3gLOuZDGq8h8QDGUq/dOSPrs4dqS',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(188,	6,	'VC0125',	NULL,	'$2y$10$ywf7Jjk2u5LA9B1QqwNzke3nicBZV23R5Gm8ogFccbbcFVwYQedK.',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(189,	6,	'VC113',	NULL,	'$2y$10$lO35Iv7wG5qQzfif5v9F8uWVIZN723ZVBBRz9Y4/4DOJTQXx2yA3e',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(190,	6,	'VC080',	NULL,	'$2y$10$X/2pW3ahYki658J9ApybiOfPbtzL83FKfoQtf4cbs.tC1Ac.CnUQa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(191,	6,	'VC135',	NULL,	'$2y$10$30e2B3QYKXjh/RlNXlWOz.josjM4sAsYnRMr0g1DMZVyADxSu1h9C',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(192,	6,	'VC110',	NULL,	'$2y$10$sXa0RN.m6a6S8VxJbMZHBO5ijUGWvEh7OgXTQNi8e.WhRWybzl0fa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(193,	6,	'VC130',	NULL,	'$2y$10$wtc2J7Dx/o0ONL8zm9ijjOTSy4BAhRnz31VxHh3JvPNG.LQHZ3vny',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:47',	'2023-02-06 13:27:37',	''),
(194,	6,	'VC128',	NULL,	'$2y$10$aiPu6hrZ67QEJ7.Z9YHJFeW2Yc1r229UQHsPGnc0dobyl34Yp2DHi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:48',	'2023-02-06 13:27:37',	''),
(195,	6,	'VC137',	NULL,	'$2y$10$8vO.rK0fRpDjHLmgYbVs6uuuMAH/8qtWov1Y2fT2kD4okyP9xMu2q',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:48',	'2023-02-06 13:27:37',	''),
(196,	6,	'VC117',	NULL,	'$2y$10$8AXXhU3vezwzNExvTEYylOoJgi3BERWUHpNKz4z/q1IyxHmKUFCRa',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:48',	'2023-02-06 13:27:37',	''),
(197,	6,	'VC136',	NULL,	'$2y$10$UU8eGHJiMQCaP8JdnMiiiO.GO.DoQLDekezI9uGMLshW0R78BFDpi',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:48',	'2023-02-06 13:27:37',	''),
(198,	6,	'VC156',	NULL,	'$2y$10$eVBbEEEbRqkg/Esc/FkuM.OWMrf1NuE1zEidolluskJ9gsSRVrxZG',	1,	NULL,	1,	1,	NULL,	'2023-01-12 07:01:48',	'2023-02-06 13:27:37',	''),
(199,	5,	'FRNFTE01',	NULL,	'$2y$10$bepjDAWgg9GL4QzW1b99aOppOdtpl0IPTZRQmX7SXAu3qAGcNCZ/2',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	''),
(200,	5,	'FRNFTE02',	NULL,	'$2y$10$HilD9.i0CjlNz5rpNQBk6O/p2T.IGzqNF7dZye10Qev7u16qi/dQK',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	''),
(201,	5,	'FRNFTE03',	NULL,	'$2y$10$.YJcRhY0hiA7pa7naSBw9.F6CGeaTQhv.LsMro4XAcnpVnHVdssRq',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	''),
(202,	5,	'FRNFTE04',	NULL,	'$2y$10$tqfPW6jv0QVjVGDtf1IXjeNA7CYhR85poWtzFcuviD2rP2/.lUs..',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	''),
(203,	5,	'FRNFTE05',	NULL,	'$2y$10$BDkS4vOIimwj2NBsDH0rguY9j1HAGOUEjg5pr.mi1Ps/N6bIDGcDi',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	''),
(204,	5,	'FRNFTE06',	NULL,	'$2y$10$eZ6FMu1s5kdmT8ZncpNhZOo.r23mAAAn41zQYJ33/efHoGRnteuqe',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:50',	'2023-02-24 11:58:50',	''),
(205,	5,	'FRNFTE07',	NULL,	'$2y$10$LerJrxdlK2sFFcY8BooPV.QDRIkNw6QTy7B5o38Ll.gH7XK.P3KD.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(206,	5,	'FRNFTE08',	NULL,	'$2y$10$cK0nAIqzghq48WztfirwlOMcNpi0LZ/yODIKHh6NXIaH7wQkDKn8K',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(207,	5,	'FRNFTE09',	NULL,	'$2y$10$MOcRuf5xFaqP.s0Y9CxIgOtmeuyZtsOwCfgeeSKkCmDq6fPBOfIm.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(208,	5,	'FRNFTE10',	NULL,	'$2y$10$ZjCKOdh0K7V4md5SJOQN4u4fa3PkoqQJOc58.0Eo9HGNPvo7O8GiO',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(209,	5,	'FRNFTE11',	NULL,	'$2y$10$9jLMT/rPN1bZJKZGtOoNP.tjcWJjzwWy2MBLZ0CZGhYXba6vPx4fy',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(210,	5,	'FRNFTE12',	NULL,	'$2y$10$GVM4V/etHfsz2PEWgZKRYOaUa0LIH/hNZJuBoDpr6xRqsazfH2Hy6',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(211,	5,	'FRNFTE13',	NULL,	'$2y$10$90Z0SWrFsHFqHmsi0WCwZ.zkx1bqvXQH2Ds26NCefeOkHCNHpCZcu',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(212,	5,	'FRNFTE14',	NULL,	'$2y$10$soS76UDvGvRSztQUQjXL0esyKt4xwHXn9r8oCd1e0HVgwJJHXag4G',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(213,	5,	'FRNFTE15',	NULL,	'$2y$10$ip2XocZJmjOJKCXTw2z9UOk8BlGmcmpikMWK2e5qNamTmtUyEnPBa',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(214,	5,	'FRNFTE16',	NULL,	'$2y$10$38.UDMKdVHVMmWDOMg49ouK8qPKqdc6xvaxXqgbfGzENbUru1gfUG',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(215,	5,	'FRNFTE17',	NULL,	'$2y$10$P/CRQUBaj1Q.aXH60Fx6vO31Rrewl5s0DnzxQ3EFozlGknyzsLjj.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(216,	5,	'FRNFTE18',	NULL,	'$2y$10$G5bNKLBp3aj0Oxhmw8J1xuDuFjCvEIT4nw.otbOrhNQZCUrvgr85G',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(217,	5,	'FRNFTE19',	NULL,	'$2y$10$MJYPHPndfhHraPudOWjSseV2olYVVGtXze/oFsBUXK8Lzi3xhKCi.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(218,	5,	'FRNFTE20',	NULL,	'$2y$10$6i0k8Rng4BtAs5PTck.XtOx2DeKf/zBb8KhgAmkvzWHebq1SRPb.u',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(219,	5,	'FRNFTE21',	NULL,	'$2y$10$3BGOT.X2iqPYeqKTm3Kgse9iOfC6zlrI6ud.ya8vTGWINe/uz0EkW',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(220,	5,	'FRNS10',	NULL,	'$2y$10$Ez/oFh0ssW8LAna4wrlwROF03xnUtoyElVGKdKacMTwpRzYXMxTNa',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(221,	5,	'FRNS113',	NULL,	'$2y$10$lfanU.hLGy8eDlZIeQ6nb.v5YUU4NxVqUdn.6PYAzCH4SfjESbHla',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:51',	'2023-02-24 11:58:51',	''),
(222,	5,	'FRNS126',	NULL,	'$2y$10$elIOJPSN5twN4KvfJtBaueQGkaEOmnaz6gBGmHyd0DdDhChGrX0Iu',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(223,	5,	'FRNS13',	NULL,	'$2y$10$t3hYaiOQXWiEcX2IukeDUO7woS3CX0pRaVrfIYkcIdE8QVT6WREw6',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(224,	5,	'FRNS130',	NULL,	'$2y$10$yErYX78uEkHdYRIgvfarvO7RZI0hmgnuMxdaiViz90.DJfVsToxYS',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(225,	5,	'FRNS132',	NULL,	'$2y$10$6nQIUOZ.nu8pHIB3koJAt.JAEl82qeqIQuhVldkGhRY5D2uYfQsnK',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(226,	5,	'FRNS133',	NULL,	'$2y$10$yvRbQAqZubB/8uR8fGBxGea1zdQ7tEtwYpSH4fKWvFnbjxL4GyruC',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(227,	5,	'FRNS134',	NULL,	'$2y$10$3LQG414dZSlYtDpCV5mzI.9U9ojDGgwUiysX6QlnMU356cdINm1cW',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(228,	5,	'FRNS135',	NULL,	'$2y$10$itDd6t2YMlJA1pWhMuLrc.YTGYeVG8dWyneievPxItAbfx4xvpAbS',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(229,	5,	'FRNS142',	NULL,	'$2y$10$XBs0gktX9dCTINXQQo/LnOjoZdLlc8s5AKHHqJ8z01.rK8xJV1EG2',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(230,	5,	'FRNS143',	NULL,	'$2y$10$GJBEPfG8O2ctcrYvgXNlSOFRf7L5gp7Ow1bZDW4OvFFwzJrfWBJpu',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(231,	5,	'FRNS146',	NULL,	'$2y$10$aXXyokzSvoxCdxFYlK8v1eYCpeHjFPMdpYtlPkBnUcRWdR25AELgu',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(232,	5,	'FRNS148',	NULL,	'$2y$10$NI0CLlEkBXLzI/8f5sCLHeB.wlqTz/uNa3w2VSK.Tfgcr0ej8glYS',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(233,	5,	'FRNS149',	NULL,	'$2y$10$PXkhjx.egzppJI5FTYJFJ.oP7irjDIvXdUTvyepbzIERC7zdnClq.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(234,	5,	'FRNS153',	NULL,	'$2y$10$fwzh1SX/uSQkNOLlm2XrQO1S9IHS8unJAKHAHqQNVVYjDTJ46V8R2',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(235,	5,	'FRNS161',	NULL,	'$2y$10$2reOaWqNPEBifkvrD7VwHucod0H3R6Pe1Mo7o1lqXnRLLpjGOs2AW',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(236,	5,	'FRNS162',	NULL,	'$2y$10$TZG4cQ6EGwOCyoVsXYKUgOtg3EkG39.3S6QAwbR9FetlMByPFgyAy',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(237,	5,	'FRNS163',	NULL,	'$2y$10$HaW5LKG1oN3mXT5FfPc15ufH46CsBTqupJw6GQ./Xg3BYJvT/kttm',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(238,	5,	'FRNS167',	NULL,	'$2y$10$ZZ6JEGnKZISe6yKAaANtyedTXhmyKDwrO3U2wHNMr3KMy.WkLOhNK',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:52',	'2023-02-24 11:58:52',	''),
(239,	5,	'FRNS172',	NULL,	'$2y$10$nyiZj.R.jo6VhEbXlOwrlOZ7fEuBIz8Q0wNN2AUE.KZKAQ6rxLRPG',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(240,	5,	'FRNS175',	NULL,	'$2y$10$VX5N8i6wiO2HiGl5OTmn7.dxtxkWYBNsOVzOlMXzRKz1hl84O29Z2',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(241,	5,	'FRNS176',	NULL,	'$2y$10$B41zrevaI49UHshw4ZtI.uRtN3phjYaek9yEM5eZEJ1g8P6fL1Wau',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(242,	5,	'FRNS182',	NULL,	'$2y$10$/Vhd5JB/lObuWM8pnc0AA.bD2d53DiwMuW9HxzoOVtXANxJ30uhwi',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(243,	5,	'FRNS183',	NULL,	'$2y$10$OXh6W29HK27PWPTJsmxwpOuHOuoEMtPGEHDHKvCbsgn8rbrZUzrR6',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(244,	5,	'FRNS184',	NULL,	'$2y$10$/N3NcStveBtqaWC6SlhWPOqSig6ohs86H4QqdDg6PZ/Zeh/ivBFbe',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(245,	5,	'FRNS186',	NULL,	'$2y$10$OOpYTvCg8sUuy9/h7S6PoeA3IaE7RLS9CRZXI2wYUCeKEFEKC4uFu',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(246,	5,	'FRNS187',	NULL,	'$2y$10$6irU6uaNGsQ5AzBZCnXzOeYVlkgk.a1hW.fTArQjTEqEiM296rf7.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(247,	5,	'FRNW05',	NULL,	'$2y$10$KnP4nz5185ciAS3qSiQ4dexnEWrVxom6r2d8C8lApHT9RpwI3vM7y',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(248,	5,	'FRNW06',	NULL,	'$2y$10$QbWsgEfM8LIJQmNimPfC8urHVIinxk0Lj1GzmXLi/C4.7XIWYzala',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(249,	5,	'FRNW08',	NULL,	'$2y$10$n.mcpMByzpiHV2p79.y/6eqVdG8k8VWAjeoesOA9C6tM2/UotPcuq',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(250,	5,	'FRNW17',	NULL,	'$2y$10$SG0/WDnhESuL6fvjR0KdxuC/V6X23/5oWkOULIgOBETmj4k8W5Fa6',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(251,	5,	'FRNW18',	NULL,	'$2y$10$pdFpWg2amxNkgV1rPDpQHO7Uha96.MAg.1rOsiEbR.L99C82MgKfq',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(252,	5,	'FRNW26',	NULL,	'$2y$10$akoaGNqRTWhFVJS/xrODU.Vno5i/LDovgI2Gqr19vbsqSQ7Llyitq',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(253,	5,	'FRNW32',	NULL,	'$2y$10$6PULGqc4XMVyG.aSPNTxDOktVatVI6.0aoea.JWrbxEk6kjK/LRHm',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(254,	5,	'FRNW33',	NULL,	'$2y$10$DGzuD8/2KbMY8mw8abxh0uc34pZ8c3xfNZbwAmaKMtE85h4p/hjJq',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:53',	'2023-02-24 11:58:53',	''),
(255,	5,	'FRNW34',	NULL,	'$2y$10$XIEGBR5UwEXSbvFCp9IgK.zc58RzNt1p6jZHxLCOvcuvyv6o1o7g2',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(256,	5,	'FRNW38',	NULL,	'$2y$10$5YspXaJipNK3jj7Si2EXlueZPA4/llipZoNN5i4vHpNK7Jx5rAOTK',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(257,	5,	'FRNW39',	NULL,	'$2y$10$RZUpkIj.eVm17KOCM5oll..1f6/ryyrXj.3s4XLTIXx0fTPvBXZy2',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(258,	5,	'FRNW48',	NULL,	'$2y$10$VDrhl6W2xhlW9HROXXCTWO41qKeQaAZ3cEJjkdplj4JHoEU/F/2uG',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(259,	5,	'FRNW49',	NULL,	'$2y$10$qImXDGkKfcQdW9OrIrL6KedcKbOF3gE7o3pGqFH2gznbtAqAmRyEG',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(260,	5,	'FRNW50',	NULL,	'$2y$10$8VgJeoc9OEn21CyROzVsguxhc3jCtv3WvrAokVG4Iy6rOUJJLmqJW',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(261,	5,	'FRNW51',	NULL,	'$2y$10$GjAZP.Majc/GfsLkE.yIFe6i/M1UCyqIcRCzpedUiXqYoyBhEZZDq',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(262,	5,	'FRNW52',	NULL,	'$2y$10$kiJUqzU1FZVxS.U0g1HHDuvtmFolVGW79HaMl6.EI40faVEbJsJVG',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(263,	5,	'FRNW53',	NULL,	'$2y$10$Wad67FSnzwoqUL58NEWPZ.fh7QSZUmOLECqU7/y51sj5NbzdLYNbC',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(264,	5,	'FRNW54',	NULL,	'$2y$10$1EN/5WUIHukCTcvV0o26OeHZjJfCnjEeDGOQzNGtAdaSnaTPBMNTi',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(265,	5,	'FRNW57',	NULL,	'$2y$10$Em3YaQzBcX2oAG9nbFK13.1k5rBOkKRIXOKoBKvqZV9xye6KEfBxe',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(266,	5,	'FRNW59',	NULL,	'$2y$10$iLqvwdHcXrJ7PpDzEr5oEODNsXzktkde2fkMMTYuvvTleLJqTjNBS',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(267,	5,	'FRNW62',	NULL,	'$2y$10$6mdu8/3W50jBzMcAQeHfTucteU/MRQMoBsQBUQNAYKFcZWaBTNjhO',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(268,	5,	'FRNW65',	NULL,	'$2y$10$z5QXplvqtqN2k94r8rJqTe.qb1OUZEPWc60T8WuiqAHq1GzBOV1GK',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	''),
(269,	5,	'FRNW66',	NULL,	'$2y$10$LR98s05uvjk8HeqEgCf7xurQrDGtsQIInzf/kFgLLpv6OTPWi4lB.',	1,	NULL,	1,	1,	NULL,	'2023-02-24 11:58:54',	'2023-02-24 11:58:54',	'');

DROP TABLE IF EXISTS `view_employee_in_out_data`;
CREATE TABLE `view_employee_in_out_data` (
  `employee_attendance_id` int NOT NULL AUTO_INCREMENT,
  `finger_print_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date DEFAULT NULL,
  `in_time_from` datetime DEFAULT NULL,
  `in_time` datetime DEFAULT NULL,
  `out_time` datetime DEFAULT NULL,
  `out_time_upto` datetime DEFAULT NULL,
  `working_time` time DEFAULT NULL,
  `working_hour` time DEFAULT NULL,
  `in_out_time` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` tinyint DEFAULT '1',
  `over_time_status` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `over_time` time DEFAULT NULL,
  `early_by` time DEFAULT NULL,
  `late_by` time DEFAULT NULL,
  `shift_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `live_status` tinyint DEFAULT '0',
  `attendance_status` tinyint DEFAULT NULL,
  `work_shift_id` int DEFAULT NULL,
  `mandays` float(10,2) DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_by` int DEFAULT NULL,
  PRIMARY KEY (`employee_attendance_id`),
  KEY `finger_print_id` (`finger_print_id`),
  KEY `date` (`date`),
  KEY `in_time` (`in_time`),
  KEY `out_time` (`out_time`),
  KEY `working_time` (`working_time`),
  KEY `over_time` (`over_time`),
  KEY `early_by` (`early_by`),
  KEY `late_by` (`late_by`),
  KEY `shift_name` (`shift_name`),
  KEY `attendance_status` (`attendance_status`),
  KEY `employee_attendance_id` (`employee_attendance_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16866 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `warning`;
CREATE TABLE `warning` (
  `warning_id` int unsigned NOT NULL AUTO_INCREMENT,
  `warning_to` int unsigned NOT NULL,
  `warning_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `warning_by` int unsigned NOT NULL,
  `warning_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`warning_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `warning` (`warning_id`, `warning_to`, `warning_type`, `subject`, `warning_by`, `warning_date`, `description`, `created_at`, `updated_at`) VALUES
(1,	254,	'test',	'test',	1,	'2022-12-23',	'test',	'2022-12-23 09:23:15',	'2022-12-23 09:23:15'),
(2,	258,	'test',	'test',	1,	'2022-12-23',	'test',	'2022-12-23 09:26:06',	'2022-12-23 09:26:06');

DROP TABLE IF EXISTS `weekly_holiday`;
CREATE TABLE `weekly_holiday` (
  `week_holiday_id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int DEFAULT NULL,
  `month` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `day_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `weekoff_days` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint NOT NULL DEFAULT '1',
  `created_by` tinyint DEFAULT NULL,
  `updated_by` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`week_holiday_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `white_listed_ips`;
CREATE TABLE `white_listed_ips` (
  `id` int unsigned NOT NULL,
  `ip_setting_id` int DEFAULT '0',
  `white_listed_ip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `work_shift`;
CREATE TABLE `work_shift` (
  `work_shift_id` int unsigned NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `late_count_time` time DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`work_shift_id`),
  KEY `work_shift_id` (`work_shift_id`),
  KEY `shift_name` (`shift_name`),
  KEY `end_time` (`end_time`),
  KEY `late_count_time` (`late_count_time`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `work_shift` (`work_shift_id`, `shift_name`, `start_time`, `end_time`, `late_count_time`, `created_at`, `updated_at`) VALUES
(1,	'A',	'07:30:00',	'16:00:00',	'07:35:00',	'2022-06-11 14:10:10',	'2022-12-02 15:13:31'),
(2,	'G',	'09:00:00',	'17:30:00',	'09:00:00',	'2022-11-17 14:24:16',	'2022-11-17 14:24:16'),
(3,	'B',	'16:00:00',	'00:30:00',	'16:00:00',	'2022-11-17 14:24:49',	'2022-11-17 14:24:49'),
(4,	'RS1',	'07:30:00',	'15:30:00',	'07:30:00',	'2022-11-17 14:25:23',	'2022-11-17 14:25:23'),
(5,	'RS2',	'15:30:00',	'22:30:00',	'15:30:00',	'2022-06-11 14:10:10',	'2022-11-28 16:00:15'),
(6,	'RS3',	'22:30:00',	'07:30:00',	'22:30:00',	'2022-11-17 14:24:16',	'2022-11-17 14:24:16'),
(7,	'SECS1',	'08:00:00',	'20:00:00',	'08:00:00',	'2022-11-17 14:24:49',	'2022-11-17 14:24:49'),
(8,	'SECS2',	'20:00:00',	'08:00:00',	'20:00:00',	'2022-11-17 14:25:23',	'2022-11-17 14:25:23');

-- 2023-03-03 04:56:50
