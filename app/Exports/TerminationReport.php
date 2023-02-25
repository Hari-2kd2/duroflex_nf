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

class TerminationReport implements FromCollection, WithHeadings, WithProperties, WithEvents
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
                $cellRange = 'A1:H1';
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->setAutoFilter($cellRange);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                $ColumnLength = count($this->data[0]);
                $RowLength = count($this->data) + 1;

                for ($i = 1; $i <= $RowLength; $i++) {
                    for ($j = 1; $j <= $ColumnLength; $j++) {
                        $column = Coordinate::stringFromColumnIndex($j);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                        $event->sheet->getDelegate()->getStyle("{$column}$i")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    }
                }
            },
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'DUROFLEX PVT LTD, FRN.',
            'lastModifiedBy' => 'DUROFLEX PVT LTD, FRN.',
            'title' => 'termination report',
            'description' => 'DUROFLEX PVT LTD, FRN. - termination report',
            'subject' => 'DUROFLEX PVT LTD, FRN. - termination report',
            'keywords' => 'termination,export,spreadsheet',
            'category' => 'termination report',
            'manager' => 'DUROFLEX PVT LTD',
            'company' => 'DUROFLEX PVT LTD , FRN.',
        ];
    }
}
