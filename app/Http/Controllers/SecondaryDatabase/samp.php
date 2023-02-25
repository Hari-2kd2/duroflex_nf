<?php

namespace App\Http\Controllers\SecondaryDatabase;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use PhpParser\Node\Expr\Empty_;
use Razorpay\Api\Card;

class LogController extends Controller
{

    // public function log()
    // {
    //     $log = DB::connection('sqlsrv')->table('atteninfo')->orderByDesc('ID')->limit(50)->get();
    //     return $log;
    // }

    // protected function getNamesTablesDB()
    // {

    //     //  $database = Config::get('database.connections.sqlsrv.database');

    //     $tables = DB::select('SHOW TABLES');

    //     $combine = "Tables_in_" . $database;

    //     $collection = new \Illuminate\Database\Eloquent\Collection;

    //     foreach ($tables as $table) {
    //         $collection->put($table->$combine, $table->$combine);
    //     }

    //     return $collection; //or compact('collection'); //for combo select
    // }

    // public function findTable()
    // {
    //     $tables  = DB::connection('sqlsrv')->select('SHOW TABLES');
    //     $results = [];
    //     foreach ($tables as $table) {
    //         $data[] = $table->Tables_in_biostar2_ac;
    //     }
    //     return $results = $data;
    // }

    // public function sample(Request $request)
    // {

    //     $customdate   = '2022-04-01';
    //     $date2        = Carbon::today();
    //     $carbon_parse = Carbon::parse($customdate)->format("Ym");
    //     $table_name   = 't_lg' . $carbon_parse;
    //     $tb_head      = $this->findTable();
    //     $bool         = in_array($table_name, $tb_head, true);
    //     // dd($bool);
    //     if ($bool == true) {
    //         $results = DB::connection('sqlsrv')->table($table_name)->get();
    //         $columns = Schema::Connection('sqlsrv')->getColumnListing($table_name);
    //         // return $data;
    //         if (request()->ajax()) {
    //             $dateField = $request->dateField;
    //             // dd($date);
    //             $date1         = $request->dateField;
    //             $carbon_parse1 = Carbon::parse($dateField)->format("Ym");
    //             $table_name1   = 't_lg' . $carbon_parse1;
    //             $results       = DB::connection('sqlsrv')->table($table_name1)->get();
    //             return \view('admin.secondDb.table', compact('results'))->render();
    //         }

    //         return \view('admin.secondDb.sample', ['results' => $results, 'columns' => $columns, 'dateField' => $request->dateField]);
    //     } else {
    //         return redirect('sample')->with('error', 'Table not found');
    //     }
    // }

    // public function getTableColumns()
    // {
    //     $date = '2022-06-01';
    //     // {   $date = $request->date;
    //     // $date  = Carbon::today()->format("Ym");
    //     $carbon_parse   = Carbon::parse($date)->format("Ym");
    //     $table_name     = 't_lg' . $carbon_parse;
    //     return $columns = Schema::Connection('sqlsrv')->getColumnListing($table_name);
    // }

    // public function demo()
    // {
    //     Log::info("Controller is working fine!");
    //     return DB::Connection('sqlsrv')->table('dbo.sample')->get();
    // }

    // public function insertLog()
    // {
    //     Log::info("Controller is working fine!");
    //     $date = Carbon::today()->subdays(2)->format('Y-m-d');
    //     $inPunch = [];
    //     $outPunch = [];
    //     $inTime = '';
    //     $outTime = '';

    //     $results = DB::connection('sqlsrv')->table('atteninfo')->select(
    //         'atteninfo.ID',
    //         'atteninfo.Empname',
    //         DB::raw("(SELECT MIN(atteninfo.datetime)  FROM atteninfo
    //         WHERE atteninfo.status = 'I' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS inTime"),
    //         DB::raw("(SELECT MAX(atteninfo.datetime)  FROM atteninfo
    //         WHERE atteninfo.status = 'O' AND CAST(atteninfo.datetime AS DATE) = '" . $date . "'  ) AS outTime"),
    //     )
    //         ->where('atteninfo.date', $date)
    //         //->whereBetween('atteninfo.time',[Carbon::now(), Carbon::now()->subHours(1)])
    //         ->groupBy('atteninfo.ID', 'atteninfo.Empname',)
    //         ->get();

    //     // dd($results);
    //     if (!empty($results)) {
    //         foreach ($results as $result) {
    //             $inTime = date('Y-m-d H:i:s', strtotime($result->inTime));
    //             $outTime = date('Y-m-d H:i:s', strtotime($result->outTime));
    //             DB::beginTransaction();
    //             $inPunch = DB::table('employee_attendance')->insert([

    //                 'finger_print_id' => $result->ID,
    //                 'in_out_time' => $inTime,
    //             ]);
    //             $outPunch = DB::table('employee_attendance')->insert([

    //                 'finger_print_id' => $result->ID,
    //                 'in_out_time' => $outTime,
    //             ]);
    //             DB::commit();
    //         }
    //         return [$outPunch, $inPunch];
    //     } else {
    //         DB::rollback();
    //         return;
    //     }
    // }


    // public function employeeDetail()
    // {
    //     $results = DB::connection('sqlsrv')->table('atteninfo')->select('atteninfo.ID', 'atteninfo.Empname')->where('Empname', '!=', "")
    //         ->groupBy('atteninfo.ID', 'atteninfo.Empname')->get();
    //     $user_id  = DB::table('user')->orderBy('user_id', 'desc')->select('user_id')->first();
    //     //dd($results);
    //     foreach ($results as $result) {

    //         $user_list = [
    //             'role_id'    => 5,
    //             'user_name'  => $result->Empname,
    //             'password'   => Hash::make(12345),
    //             'created_by' => 1,
    //             'updated_by' => 1,
    //             'status'     => 1,
    //             'created_at' => Carbon::now(),
    //             'updated_at' => Carbon::now(),
    //         ];

    //         $employee_list = [
    //             'user_id'            => isset($user_id->user_id) ? ($user_id->user_id) + 1 : 1,
    //             'finger_id'          => $result->ID,
    //             'department_id'      => 1,
    //             'designation_id'     => 1,
    //             'first_name'         => $result->Empname,
    //             'gender'             => 1,
    //             'created_by'         => 1,
    //             'updated_by'         => 1,
    //             'status'             => 1,
    //             'created_at'         => Carbon::now(),
    //             'updated_at'         => Carbon::now()
    //         ];

    //         // dd($user_list, $employee_list);

    //         DB::beginTransaction();
    //         DB::table('user')->insert($user_list);
    //         // dd($user_id);
    //         DB::table('employee')->insert([$employee_list]);
    //         DB::commit();
    //     }

        
    // }
}





// $results = DB::connection('sqlsrv')->table('t_usr')->select(
//     't_usr.USRID',
//     't_usr.NM',
//     DB::raw('(SELECT MIN(' . $table_name . '.SRVDT)  FROM ' . $table_name . '
//     WHERE  CAST(' . $table_name . '.SRVDT AS DATE) = "' . $date . '" AND ' . $table_name . '.USRID = t_usr.USRID ) AS inTime'),
//     DB::raw('(SELECT MAX(' . $table_name . '.SRVDT) FROM ' . $table_name . '
//                                                  WHERE CAST(' . $table_name . '.SRVDT AS DATE) =  "' . $date . '" AND ' . $table_name . '.USRID = t_usr.USRID ) AS outTime')
// )
//     ->join($table_name, $table_name . '.USRID', '=', 't_usr.USRID')
//     ->whereDate($table_name . '.SRVDT', $date)
//     ->whereTime($table_name . '.SRVDT', '>=', $time)
//     ->whereBetween($table_name . '.EVT', [4096, 4129])
//     ->orWhereBetween($table_name . '.EVT', [4864, 4872])
//     ->groupBy('t_usr.USRID', 't_usr.NM')
//     ->get();



// $date = Carbon::now()->subDay(3)->format('Y-m-d');
// $time = Carbon::today()->subDay(3)->subHour(6)->format('H:i:s');
// $array = [4864, 4865, 4866, 4868, 4869, 4870, 4871, 4872];
// $carbon_parse = Carbon::parse($date)->format("Ym");
// // $carbon_today = Carbon::parse($date)->format("Y-m-d");
// $table_name = 't_lg' . $carbon_parse;
// $tb_head = $this->findTable();
// $bool = in_array($table_name, $tb_head, true);
// $t_usr = DB::connection('sqlsrv')->table('t_usr')->get()->groupBy('USRID');

//	$results = DB::connection('sqlsrv')->table('t_usr')->select(
//     't_usr.USRID',
//     't_usr.NM',
//     DB::raw('(SELECT MIN(' . $table_name . '.SRVDT)  FROM ' . $table_name . '
//     WHERE  CAST(' . $table_name . '.SRVDT AS DATE) = "' . $date . '" AND ' . $table_name . '.USRID = t_usr.USRID ) AS inTime'),
//     DB::raw('(SELECT MAX(' . $table_name . '.SRVDT) FROM ' . $table_name . '
//                                                  WHERE CAST(' . $table_name . '.SRVDT AS DATE) =  "' . $date . '" AND ' . $table_name . '.USRID = t_usr.USRID ) AS outTime')
// )
//     ->join($table_name, $table_name . '.USRID', '=', 't_usr.USRID')
//     ->whereDate($table_name . '.SRVDT', $date)
//     ->whereTime($table_name . '.SRVDT', '>=', $time)
//     ->whereBetween($table_name . '.EVT', [4096, 4129])
//     ->orWhereBetween($table_name . '.EVT', [4864, 4872])
//     ->groupBy('t_usr.USRID', 't_usr.NM')
//     ->get();