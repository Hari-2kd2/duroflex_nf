<?php

namespace App\Http\Controllers\Payroll;

use Illuminate\Http\Request;
use App\Model\Employee;
use App\Model\Designation;
use \Mpdf\Mpdf as PDF;
use App\Http\Controllers\Controller;
use App\Lib\Enumerations\UserStatus;
use App\Model\PayRoll;
use Carbon\Carbon;

class PayslipController extends Controller
{

    public function index(Request $request)
    {

        $payroll = PayRoll::find($request->id);
        $employee = Employee::find($payroll->employee);
        // Setup a filename 
        $documentFileName = "Payslip"   . "For" . $employee->finger_id . '-' . $payroll->year . sprintf('%02d', $payroll->month) . ".pdf";

        // Create the mPDF document
        $document = new PDF([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_header' => '2',
            'margin_top' => '20',
            'margin_bottom' => '20',
            'margin_footer' => '2',
            // 'format' => 'A4-L',
            // 'format' => [210, 297]
        ]);

        // Set some header informations for output
        $header = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documentFileName . '"'
        ];

        // Write some simple Content
        $document->WriteHTML(view('admin.payroll.payslip.pdf', ['document' => $document, 'payroll' => $payroll, 'employee' => $employee]));


        return $document->Output($documentFileName, "I");

        /*// Save PDF on your public storage 
        Storage::disk('public')->put($documentFileName, $document->Output($documentFileName, "S"));
         
        // Get file back from storage with the give header informations
        return Storage::disk('public')->download($documentFileName, 'Request', $header); //*/
    }


    public function payslipCollection(Request $request)
    {
        ini_set('memory_limit', '1500000M');
        ini_set("pcre.backtrack_limit", "3000000");

        $collections = Payroll::whereRaw('month = ' . date('m', strtotime($request->month . '-01')) . ' and year = ' . date('Y', strtotime($request->month . '-01')) . '')->get();
        // Setup a filename 
        $documentFileName = "PayslipsFor" . date('MY') . '-' . date('dHis') . ".pdf";

        // Create the mPDF document
        $document = new PDF([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_header' => '2',
            'margin_top' => '20',
            'margin_bottom' => '20',
            'margin_footer' => '2',
            'format' => [210, 297]
        ]);

        // Set some header informations for output
        $header = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $documentFileName . '"'
        ];

        // Write some simple Content
        $document->WriteHTML(view('admin.payroll.payslip.pdfs', ['document' => $document, 'collections' => $collections]));

        if (count($collections) > 0) {
            return  $document->Output($documentFileName, "I");
        } else {
            return redirect('salaryInfo')->with('error', 'No payslip records found..');
        }


        /*// Save PDF on your public storage 
        Storage::disk('public')->put($documentFileName, $document->Output($documentFileName, "S"));
         
        // Get file back from storage with the give header informations
        return Storage::disk('public')->download($documentFileName, 'Request', $header); //*/
    }
}
