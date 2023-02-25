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

class EarnedLeaveReport implements FromCollection, WithHeadings, WithProperties, WithEvents
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
        // dd($this->data);
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
                $cellRange = 'A1:F1';
                $event->sheet->getDelegate()->setMergeCells(['A1:F1']);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(12);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $cellRange1 = 'A2:F2';
                $event->sheet->setAutoFilter($cellRange1);
                $event->sheet->getStyle($cellRange1)->ApplyFromArray($styleArray);
                $event->sheet->getDelegate()->getStyle($cellRange1)->getFont()->setSize(11);
                $event->sheet->getDelegate()->getStyle($cellRange1)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                for ($i = 1; $i <= 6; $i++) {
                    $column = Coordinate::stringFromColumnIndex($i);
                    $event->sheet->getColumnDimension($column)->setAutoSize(true);
                }
            },

        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'DUROFLEX PVT LTD, FRN.',
            'lastModifiedBy' => 'DUROFLEX PVT LTD, FRN.',
            'title' => 'Earned Leave',
            'description' => 'DUROFLEX PVT LTD, FRN. - Earned Leave',
            'subject' => 'DUROFLEX PVT LTD, FRN. -Earned Leave',
            'keywords' => 'earned leave,export,spreadsheet',
            'category' => 'earned leave report',
            'manager' => 'DUROFLEX PVT LTD',
            'company' => 'DUROFLEX PVT LTD , FRN.',
        ];
    }
}
