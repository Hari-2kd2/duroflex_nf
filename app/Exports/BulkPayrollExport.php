<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BulkPayrollExport implements FromCollection, WithHeadings, WithProperties, WithEvents
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

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use ($styleArray) {
                $cellRange = 'A1:AL1';
                $event->sheet->getDelegate()->setMergeCells(['A1:AL1', 'A2:AL2', 'A3:AL3']);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(18);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getStyle('A2:AL2')->ApplyFromArray($styleArray);
                $event->sheet->getStyle('A3:AL3')->ApplyFromArray($styleArray);

                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('A2:AL2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('A3:AL3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle('A4:AL4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                $event->sheet->setAutoFilter('A4:AL4');
                $event->sheet->getStyle('A4:AL4')->ApplyFromArray($styleArray);

                $count = count($this->data) > 0 ? count($this->data[0]) : 0;

                for ($i = 1; $i <= $count; $i++) {
                    $column = Coordinate::stringFromColumnIndex($i);
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },
        ];
    }

    public function properties(): array
    {
        return [

            'creator' => 'DUROFLEX ',
            'lastModifiedBy' => 'DUROFLEX',
            'title' => 'Attendance Report',
            'description' => 'DUROFLEX - Payroll Report',
            'subject' => 'DUROFLEX - Payroll Report',
            'keywords' => 'Payroll,export,spreadsheet',
            'category' => 'Payroll',
            'manager' => 'DUROFLEX',
            'company' => 'DUROFLEX',
        ];
    }
}
