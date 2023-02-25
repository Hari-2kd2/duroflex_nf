<?php

namespace App\Console\Commands\Local;

use App\Components\Common;
use App\Model\DeviceAttendanceLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LogExport extends Command
{

    protected $signature = 'export:local-log';
    protected $name = 'local-log-export';
    protected $description = 'Local Export Log';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {

        DB::beginTransaction();

        $client = new \GuzzleHttp\Client(['verify' => false]);
        $response = $client->request('GET', Common::liveurl() . "loghistory");
        $json = $response->getBody()->getContents();

        $json = json_decode($json);
        if (isset($json->data->primary_id)) {
            $serverID = $json->data->primary_id;
        } else {
            $serverID = 0;
        }

        $local_logID = DeviceAttendanceLog::orderBy('primary_id', 'DESC')->first();

        if ($serverID && $local_logID->primary_id == $serverID) {
            return true;
        }

        DeviceAttendanceLog::where('primary_id', '>', $serverID)->orderBy('primary_id', 'ASC')->chunk(5, function ($device_log) {

            foreach ($device_log as $logs) {

                $client = new \GuzzleHttp\Client(['verify' => false]);
                $response = $client->request('POST', Common::liveurl() . "importlogs", [
                    'form_params' => [
                        'ID' => $logs->ID,
                        'evtlguid' => $logs->evtlguid,
                        'devdt' => $logs->devdt,
                        'devuid' => $logs->devuid,
                        'datetime' => $logs->datetime,
                        'punching_time' => $logs->punching_time,
                        // 'type' => $logs->type,
                        // 'employee' => $logs->employee,
                        // 'device' => $logs->device,
                        // 'device_name' => $logs->device_name,
                        // 'device_employee_id' => $logs->device_employee_id,
                        // 'status' => $logs->status,
                        // 'live_status' => $logs->live_status,
                        // 'sms_log' => $logs->sms_log,
                    ],

                ]);
                $logs->live_status = 1;
                $logs->save();
            }
        });
        DB::commit();
    }
}
