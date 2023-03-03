<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\AccessControl;
use App\Model\Device;
use App\Model\MsSql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{

    public function add(Request $request)
    {

        DB::beginTransaction();
        $device = Device::create($request->all());
        Device::where('id', $device->id)->update(['id' => $request->id]);
        DB::commit();

        return json_encode(['status' => 'success', 'message' => 'Device created Successfully !'], 200);
    }

    public function update(Request $request)
    {

        DB::beginTransaction();
        $device = Device::findOrFail($request->id);
        $device->update($request->all());
        DB::commit();

        return json_encode(['status' => 'success', 'message' => 'Device Successfully updated !'], 200);
    }

    public function importlogs(Request $request)
    {
        try {

            Log::info('import done at :' . date('Y-m-d H:i:s'));
            DB::beginTransaction();

            $device = new MsSql();
            $device->local_primary_id = $request->primary_id;
            $device->evtlguid = $request->evtlguid;
            $device->ID = $request->ID;
            $device->datetime = $request->datetime;
            $device->devdt = $request->devdt;
            $device->devuid = $request->devuid;
            $device->punching_time = $request->punching_time;
            $device->created_at = date('Y-m-d H:i:s');
            $device->updated_at = date('Y-m-d H:i:s');

            $device->save();
            DB::commit();

        } catch (\Throwable$th) {
            //throw $th;
            info($th->getMessage());
        }

        return response()->json(['status' => 'success', 'message' => 'Device Log Successfully updated !'], 200);
    }

    public function destroy(Request $request)
    {

        $devices = Device::FindOrFail($request->id);
        $devices->status = 2;
        $devices->save();

        AccessControl::where('device', $request->id)->delete();

        return json_encode(['status' => 'success', 'message' => 'Device Log Successfully updated !'], 200);
    }
}
