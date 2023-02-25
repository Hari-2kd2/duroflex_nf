<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceReportExport implements FromCollection, WithHeadings, WithProperties, WithEvents
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

        return [
            AfterSheet::class => function (AfterSheet $event) {
                $cellRange = 'A1:O1';
                $event->sheet->getDelegate()->setMergeCells([
                    'A1:O1',
                    'A2:O2',
                ]);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(18);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A2:O2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getDelegate()->getStyle('A3:O3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            },
        ];

    }

    public function properties(): array
    {
        return [
            'creator' => 'DUROFLEX PVT LTD, FRN.',
            'lastModifiedBy' => 'DUROFLEX PVT LTD, FRN.',
            'title' => 'Wage Sheet',
            'description' => 'DUROFLEX PVT LTD, FRN. - Wage Sheet',
            'subject' => 'DUROFLEX PVT LTD, FRN. - Wage Sheet',
            'keywords' => 'salary,export,spreadsheet',
            'category' => 'salary report',
            'manager' => 'DUROFLEX PVT LTD',
            'company' => 'DUROFLEX PVT LTD , FRN.',
        ];
    }

}
