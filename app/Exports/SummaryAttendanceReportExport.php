<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class SummaryAttendanceReportExport implements FromView, WithProperties, WithEvents
{
    use RegistersEventListeners;

    public $data;
    public $view;

    public function __construct($view, $data)
    {
        $this->data = $data;
        $this->view = $view;
        // dd($data['results']);
    }

    public function view(): View
    {
        \set_time_limit(0);
        return view($this->view, $this->data);
    }

    public function registerEvents(): array
    {

        //border style
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    //'color' => ['argb' => 'FFFF0000'],
                ],
            ],
        ];

        //font style
        $styleArray1 = [
            'font' => [
                'bold' => true,
            ],
        ];

        //column  text alignment
        $styleArray2 = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ),
        );

        //$styleArray3 used for vertical alignment
        $styleArray3 = array(
            'alignment' => array(
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ),
        );

        $styleArray4 = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'D3D3D3'),
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'E0EFFF'),
            ),
        );

        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $styleArray,
                $styleArray1,
                $styleArray2,
                $styleArray3,
                $styleArray4
            ) {
                $cellRangeArr = [];

                // get layout counts (add 1 to rows for heading row)
                $row_count = count($this->data['results']);
                $column_count = count($this->data['monthToDate']) + 5;

                // // set columns to autosize
                for ($i = 1; $i <= 4; $i++) {
                    for ($i = 1; $i <= $column_count; $i++) {
                        $column = Coordinate::stringFromColumnIndex($i);
                        array_push($cellRangeArr, $column);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }

                for ($i = 1; $i <= 4; $i++) {
                    $cellRange = $cellRangeArr[0] . $i . ':' . $cellRangeArr[count($cellRangeArr) - 1] . $i; // All headers
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray1);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray2);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray3);
                }
            },
        ];
    }

    public function properties(): array
    {
        return [

            'creator' => 'DUROFLEX ' . config('services.var.org') . ' LOCATION',
            'lastModifiedBy' => 'DUROFLEX ' . config('services.var.org') . ' LOCATION',
            'title' => 'Attendance Report',
            'description' => 'DUROFLEX ' . config('services.var.org') . ' LOCATION - Attendance Report',
            'subject' => 'DUROFLEX ' . config('services.var.org') . ' LOCATION - Attendance Report',
            'keywords' => 'attendance,export,spreadsheet',
            'category' => 'attendance',
            'manager' => 'DUROFLEX ' . config('services.var.org') . ' LOCATION',
            'company' => 'DUROFLEX ' . config('services.var.org') . ' LOCATION',
        ];
    }
}
