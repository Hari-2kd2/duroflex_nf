<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithProperties;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class EmployeeDetailsExport implements WithHeadings, FromCollection, WithProperties, WithEvents, WithColumnFormatting
{

    public $data;
    public $extraData;

    public function __construct($data, $extraData)
    {
        $this->data = $data;
        $this->extraData = $extraData;
    }

    public function headings(): array
    {
        return $this->extraData['heading'];
    }

    public function collection()
    {
        return collect($this->data);
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
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                'startColor' => [
                    'argb' => 'FFA0A0A0',
                ],
                'endColor' => [
                    'argb' => 'FFFFFFFF',
                ],
            ],
        );

        $styleArray5 = array(
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,

                'startColor' => [
                    'argb' => 'E0E0E0',
                ],
            ],
        );

        $styleArray6 = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'BFBFBF'),
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'E2EFDA'),
            ),
        );

        $styleArray7 = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'BFBFBF'),
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => 'DDEBF7'),
            ),
        );

        $styleArray8 = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => array('argb' => '000000'),
                ),
            ),
            'alignment' => array(
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ),
        );

        $styleArray9 = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'BFBFBF'),
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '116530'),
            ),
        );

        $styleArray10 = array(
            'borders' => array(
                'allBorders' => array(
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => array('argb' => 'BFBFBF'),
                ),
            ),
            'fill' => array(
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => array('argb' => '05445e'),
            ),
        );

        $styleArray11 = [
            'font' => [
                'bold' => true,
                'color' => array('argb' => 'FFFFFF'),
            ],
        ];

        return [
            AfterSheet::class => function (AfterSheet $event) use (
                $styleArray,
                $styleArray1,
                $styleArray2,
                $styleArray3,
                $styleArray4,
                $styleArray5,
                $styleArray6,
                $styleArray7,
                $styleArray8,
                $styleArray9,
                $styleArray10,
                $styleArray11
            ) {
                $cellRange = 'A1:AN1'; // All headers
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
                // $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray1);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray2);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray3);
                // $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray4);
                // $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray5);
                $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray8);
                $event->sheet->setAutoFilter($cellRange);
                // $event->sheet->getStyle($cellRange)->ApplyFromArray($styleArray11);

                $NonMandCell = [
                    'A', 'J', 'K', 'M', 'N', 'P', 'U', 'W', 'X',
                ];

                $ColumnLength = count($this->data[0]);
                $RowLength = count($this->data);

                $l = 0;
                $cellLength = [];

                for ($x = 'A'; $x < 'ZZ'; $x++) {
                    array_push($cellLength, $x);
                    if ($x == 'AN') {
                        break;
                    }

                }

                // foreach ($cellLength as $key => $value) {
                //     for ($i = 0; $i <= $RowLength; $i++) {
                //         $j = $i + 1;
                //         // if ($j % 2 != 0) {
                //         if (in_array($value, $NonMandCell)) {
                //             $event->sheet->getStyle("{$value}$j")->ApplyFromArray($styleArray7);
                //         } else {
                //             $event->sheet->getStyle("{$value}$j")->ApplyFromArray($styleArray6);
                //         }
                //         // }
                //     }
                // }

                foreach ($cellLength as $key => $value) {
                    if (in_array($value, $NonMandCell)) {
                        $event->sheet->getStyle("{$value}1")->ApplyFromArray($styleArray7);
                    } else {
                        $event->sheet->getStyle("{$value}1")->ApplyFromArray($styleArray6);
                    }
                }

                for ($i = 1; $i <= $RowLength; $i++) {
                    for ($j = 1; $j <= $ColumnLength; $j++) {
                        $column = Coordinate::stringFromColumnIndex($i);
                        $event->sheet->getColumnDimension($column)->setAutoSize(true);
                    }
                }

                $options1 = ['Yes', 'No'];
                $options2 = ['Enabled', 'Disabled'];
                $options3 = ['Male', 'Female', 'NoDisclosure'];
                $options4 = [null, 'Married', 'Unmarried', 'NoDisclosure'];
                $drop_column = [['cell' => 'T', 'options' => $options3], ['cell' => 'V', 'options' => $options4], ['cell' => 'AM', 'options' => $options1], ['cell' => 'AN', 'options' => $options2]];
                for ($i = 2; $i <= $RowLength; $i++) {
                    for ($j = 1; $j <= $ColumnLength; $j++) {
                        foreach ($drop_column as $data) {
                            $validation1 = $event->sheet->getCell("{$data["cell"]}$j")->getDataValidation();
                            $validation1->setType(DataValidation::TYPE_LIST);
                            $validation1->setErrorStyle(DataValidation::STYLE_INFORMATION);
                            $validation1->setAllowBlank(true);
                            $validation1->setShowInputMessage(true);
                            $validation1->setShowErrorMessage(true);
                            $validation1->setShowDropDown(true);
                            $validation1->setErrorTitle('Input error');
                            $validation1->setError('Value is not in list.');
                            $validation1->setPromptTitle('Pick from list');
                            $validation1->setPrompt('Please pick a value from the drop-down list.');
                            $validation1->setFormula1(sprintf('"%s"', implode(',', $data['options'])));
                        }
                    }
                }
            },
        ];
    }

    public function columnFormats(): array
    {
        return [
            'L' => NumberFormat::FORMAT_GENERAL,
            'R' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'S' => NumberFormat::FORMAT_DATE_DDMMYYYY,
        ];
    }

    public function properties(): array
    {
        return [
            'creator' => 'DUROFLEX PVT LTD, FRN.',
            'lastModifiedBy' => 'DUROFLEX PVT LTD, FRN.',
            'title' => 'EmployeeInfo',
            'description' => 'DUROFLEX PVT LTD, FRN. - EmployeeInfo',
            'subject' => 'DUROFLEX PVT LTD, FRN. - EmployeeInfo',
            'keywords' => 'EmployeeInfo,export,spreadsheet',
            'category' => 'EmployeeInfo',
            'manager' => 'DUROFLEX PVT LTD',
            'company' => 'DUROFLEX PVT LTD , FRN.',
        ];
    }
}
