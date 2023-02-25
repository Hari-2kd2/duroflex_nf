<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DailyAttendanceReportExport implements FromView, WithProperties, WithEvents
{
    use RegistersEventListeners;

    public $data;
    public $view;

    public function __construct($view, $data)
    {
        $this->data = $data;
        $this->view = $view;
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

        //column  text alignment
        $styleArray5 = array(
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ),
        );

        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $styleArray,
                $styleArray1,
                $styleArray2,
                $styleArray3,
                $styleArray4,
                $styleArray5
            ) {
                $cellRangeArr = [];

                // get layout counts (add 1 to rows for heading row)
                $row_count = 0;
                $column_count = 15;
                foreach ($this->data['results'] as $value) {
                    $row_count += count($value);
                }

                // // set columns to autosize
                for ($i = 1; $i <= $column_count; $i++) {
                    $column = Coordinate::stringFromColumnIndex($i);
                    array_push($cellRangeArr, $column);
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }

                for ($i = 1; $i <= 2; $i++) {
                    $cellRange = $cellRangeArr[0] . $i . ':' . $cellRangeArr[count($cellRangeArr) - 1] . $i; // All headers
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray1);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray2);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray3);
                    if ($i == 2) {
                        $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray5);
                        $event->sheet->setAutoFilter($cellRange);
                    }
                }

                for ($i = 3; $i <= $row_count - 2; $i++) {
                    $cellRange = $cellRangeArr[0] . $i . ':' . $cellRangeArr[count($cellRangeArr) - 1] . $i; // All headers
                    $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
                    $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray5);
                }
            },
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'DUROFLEX PVT LTD, FRN.',
            'lastModifiedBy' => 'DUROFLEX PVT LTD, FRN.',
            'title' => 'Attendance Report',
            'description' => 'DUROFLEX PVT LTD, FRN. - Attendance Report',
            'subject' => 'DUROFLEX PVT LTD, FRN. - Attendance Report',
            'keywords' => 'attendance,export,spreadsheet',
            'category' => 'attendance report',
            'manager' => 'DUROFLEX PVT LTD',
            'company' => 'DUROFLEX PVT LTD , FRN.',
        ];
    }
}
