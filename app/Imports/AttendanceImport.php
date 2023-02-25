<?php

namespace App\Imports;

use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Validators\Failure;

class AttendanceImport implements
WithHeadingRow,
ToModel,
ToCollection,
SkipsOnError,
WithValidation,
SkipsOnFailure,
WithChunkReading,
ShouldQueue,
WithEvents
{
    use Importable, SkipsErrors, SkipsFailures, RegistersEventListeners;
    /**

     * @param array $row

     *

     * @return \Illuminate\Database\Eloquent\Model|null

     */

    // public function model(array $row)
    // {

    //     $data = [
    //         'finger_print_id' => $row['finger_print_id'],
    //         'in_out_time'     => dateConvertFormtoDB($row['in_out_time']) . ' ' . date("H:i:s", strtotime($row['in_out_time'])),
    //     ];

    //     return EmployeeAttendance::create($data);

    // }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $upload = EmployeeAttandance::create([
                'finger_print_id' => $row['finger_print_id'],
                'in_out_time'     => dateConvertFormtoDB($row['in_out_time']) . ' ' . date("H:i:s", strtotime($row['in_out_time'])),
            ]);
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public static function afterImport(AfterImport $event)
    {
    }

    public function onFailure(Failure...$failure)
    {
    }

}
