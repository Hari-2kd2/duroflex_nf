<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithProperties;

class MonthlyAttendanceReportExport implements FromView , WithProperties
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
        // dd($this->view);
        return view($this->view, $this->data);
    }

    public function properties(): array
    {
        return [
                                            
            'creator' => 'DUROFLEX FRN LOCATION',
            'lastModifiedBy' => 'DUROFLEX FRN LOCATION',
            'title' => 'Attendance Report',
            'description' => 'DUROFLEX FRN LOCATION - Attendance Report',
            'subject' => 'DUROFLEX FRN LOCATION - Attendance Report',
            'keywords' => 'attendance,export,spreadsheet',
            'category' => 'attendance',
            'manager' => 'DUROFLEX FRN LOCATION',
            'company' => 'DUROFLEX FRN LOCATION',
        ];
    }
}
