<?php

namespace App\Exports;


use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithProperties;

class SettlementReport implements FromCollection, WithHeadings, WithProperties, WithEvents
{

    use Exportable;

    public $data;
    public $extraData;

    public function __construct($data, $extraData)
    {
        $this->data = $data;
        $this->extraData = $extraData;
    }

    public function collection()
    {

        return collect($this->data);
    }

    public function headings(): array
    {
        return $this->extraData['heading'];
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

        return [


            AfterSheet::class    => function (AfterSheet $event)  use (
                $styleArray,
                $styleArray1,
                $styleArray2,
                $styleArray3
            ) {
                $cellRange = 'A1:P1';

                $event->sheet->getDelegate()->setMergeCells([
                    'A1:J1',
                    'A2:J2',
                    'A3:J3',
                ]);

                $event->sheet->setAutoFilter('A4:J4');
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(18);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A2:J2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A3:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // $event->sheet->getDelegate()->getStyle('A4:J4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // get layout counts (add 1 to rows for heading row) 
                $row_count =  4;
                $column_count = 10;
                $cellRangeArr = [];

                // // set columns to autosize
                for ($i = 1; $i <= $row_count; $i++) {
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
            'creator'        => 'DUROFLEX PVT LTD, FRN.',
            'lastModifiedBy' => 'DUROFLEX PVT LTD, FRN.',
            'title'          => 'Wage Sheet',
            'description'    => 'DUROFLEX PVT LTD, FRN. - Wage Sheet',
            'subject'        => 'DUROFLEX PVT LTD, FRN. - Wage Sheet',
            'keywords'       => 'salary,export,spreadsheet',
            'category'       => 'salary report',
            'manager'        => 'DUROFLEX PVT LTD',
            'company'        => 'DUROFLEX PVT LTD , FRN.',
        ];
    }
}
