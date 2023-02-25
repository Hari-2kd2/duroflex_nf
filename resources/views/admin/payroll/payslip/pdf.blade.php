<style type="text/css">
    table {
        font-family: calibri;
        font-size: 12px;
        border: none
    }

    .width20 {
        width: 20%
    }

    .width21 {
        width: 21%
    }

    .width25 {
        width: 25%
    }

    .width28 {
        width: 28%
    }

    .width11 {
        width: 11%
    }

    .width18 {
        width: 18%
    }

    .height50 {
        width: 50%
    }

    .width100 {
        width: 100%
    }

    .width50 {
        width: 50%
    }

    .al-right {
        text-align: right;
    }

    .al-center {
        text-align: center;
    }

    .text-italic {
        font-style: italic;
    }

    .text-bold {
        font-weight: bold;
    }

    .wages td {
        text-align: right;
    }

    .border1 {
        border: 0.04rem solid #000000;
    }

    .b-top0 {
        border-top: 0px !important;
    }

    .b-collapse {
        border-collapse: collapse;
    }

    .padding25 {
        padding: 25px;
    }

    p {
        font-size: 12px;
    }

    table tr td {
        padding: 2px;
    }

    .top-align {
        vertical-align: top;
        text-align: left;
    }
</style>
<table cellpadding="5" cellspacing="5" class="border1 width100">
    <tr>
        <td class="al-center">FORM NO.XXVIII</td>
    </tr>
</table>
<table cellpadding="5" cellspacing="5" class="border1 width100 b-top0">
    <tr>
        <td class="al-center">(See Rule 78 (1) (b) of Tamil Nadu Contract Labour (Regulation and Abolition) Rules, 1975)
        </td>
    </tr>
</table>
<table cellpadding="5" cellspacing="5" class="border1 width100 b-top0">
    <tr>
        <td class="al-center">WAGE SLIP for the month of
            {{ strtoupper(date('M-y', strtotime($payroll->year . '-' . $payroll->month))) }}</td>
    </tr>
</table>
<table cellpadding="10" cellspacing="10" class="table-bordered border1 width100 b-top0 b-collapse" border="1">
    <tr>
        <td class="width28">Contractor Name </td>
        <td>{{ $employee->branch->branch_name }}</td>
        <td class="width28">Worked Days</td>
        <td class="width18">{{ number_format((float) $payroll->no_day_wages + $payroll->company_holiday, 2, '.', '') }}
        </td>
    </tr>
    <tr>
        <td>Employee ID </td>
        <td>{{ $employee->finger_id }}</td>
        <td class="width28">Paid Holidays</td>
        <td class="width18">{{ number_format((float) $payroll->ph, 2, '.', '') }}</td>
    </tr>
    <tr>
        <td>Name of the Employee</td>
        <td>{{ $employee->first_name . ' ' . $employee->last_name }}</td>
        <td class="width28">Total Payable Days</td>
        <td class="width18">
            {{ number_format((float) $payroll->no_day_wages + $payroll->ph + $payroll->company_holiday, 2, '.', '') }}
        </td>
    </tr>
    <tr>
        <td>Father's / Husband's Name</td>
        <td>{{ $employee->father_name }}</td>
        <td>OT Hours</td>
        <td>{{ $payroll->ot_hours }}</td>
    </tr>
    <tr>
        <td>Date of Joining</td>
        <td>{{ $employee->date_of_joining != '0000-00-00' ? DATE('d-m-Y', strtotime($employee->date_of_joining)) : '' }}
        </td>
        <td>UAN No</td>
        <td>{{ $employee->pf_account_number }}</td>
    </tr>
    <tr>
        <td>Designation</td>
        <td>{{ $employee->designation->designation_name }}</td>
        <td>ESIC No</td>
        <td>{{ $employee->esi_card_number }}</td>
    </tr>
</table>
<table cellpadding="10" cellspacing="10" class="border1 width100 b-top0 b-collapse" border="1">
    <tr>
        <td class="width28 al-right">Wages Earned </td>
        <td class="al-right">Rs</td>
        <td class="al-right">P</td>
        <td class="width21 al-right">Deduction</td>
        <td class="al-right">Rs</td>
        <td class="al-right">P</td>
    </tr>
    <tr class="wages">
        <td>Basic </td>
        <td>{{ explode('.', $payroll->basic_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->basic_amount)[1] }}</td>
        <td>EPF</td>
        <td>{{ explode('.', $payroll->employee_pf)[0] }}</td>
        <td>{{ explode('.', $payroll->employee_pf)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>D.A </td>
        <td>{{ explode('.', $payroll->da_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->da_amount)[1] }}</td>
        <td>ESI</td>
        <td>{{ explode('.', $payroll->employee_esic)[0] }}</td>
        <td>{{ explode('.', $payroll->employee_esic)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>HRA </td>
        <td>{{ explode('.', $payroll->hra_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->hra_amount)[1] }}</td>
        <td>Other Deduction</td>
        <td>{{ 0 }}</td>
        <td>{{ 0 }}</td>
    </tr>
    <tr class="wages">
        <td>O.T Wages </td>
        <td>{{ explode('.', $payroll->ot_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->ot_amount)[1] }}</td>
        <td>Canteen</td>
        <td>{{ explode('.', $payroll->canteen)[0] }}</td>
        <td>{{ explode('.', $payroll->canteen)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>Leave Wages </td>
        <td>0</td>
        <td>00</td>
        <td>LWF</td>
        <td>{{ explode('.', $payroll->lwf)[0] }}</td>
        <td>{{ explode('.', $payroll->lwf)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>Other Allowance </td>
        <td>0</td>
        <td>00</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="al-right text-italic text-bold">Gross Wages </td>
        <td class="al-right">{{ explode('.', $payroll->gross_salary)[0] }}</td>
        <td class="al-right">{{ explode('.', $payroll->gross_salary)[1] }}</td>
        <td class="al-right width21 text-italic text-bold">Total Deduction</td>
        <td class="al-right">{{ explode('.', $payroll->employee_total_deduction)[0] }}</td>
        <td class="al-right">{{ explode('.', $payroll->employee_total_deduction)[1] }}</td>
    </tr>
    <tr class="wages">
        <td style="color: white">Other Allowance </td>
        <td style="color: white"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr class="wages">
        <td><b>Net Amount</b></td>
        <td><b>{{ explode('.', $payroll->net_salary)[0] }}</b></td>
        <td><b>{{ explode('.', $payroll->net_salary)[1] }}</b></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

</table>
<p class="al-center" style="padding-bottom: 92px;">This is computer generated payslip. No Signature is required.</p>
{{-- <table cellpadding="5" cellspacing="5" class="border1 width100 b-top0">
    <tr>
        <td class="padding25 al-center"></td>
        <td class="al-center"></td>
    </tr>
    <tr>
        <td class="al-center">Signature of the Employers / manager <br>or any other Authorised person</td>
        <td class="al-center">Signature of <br>Thumb Impression of the labour</td>
    </tr>
</table> --}}


{{-- <pagebreak> --}}


<table cellpadding="5" cellspacing="5" class="border1 width100">
    <tr>
        <td class="al-center">FORM NO.XXVIII</td>
    </tr>
</table>
<table cellpadding="5" cellspacing="5" class="border1 width100 b-top0">
    <tr>
        <td class="al-center">(See Rule 78 (1) (b) of Tamil Nadu Contract Labour (Regulation and Abolition) Rules,
            1975)</td>
    </tr>
</table>
<table cellpadding="5" cellspacing="5" class="border1 width100 b-top0">
    <tr>
        <td class="al-center">WAGE SLIP for the month of
            {{ strtoupper(date('M-y', strtotime($payroll->year . '-' . $payroll->month))) }}</td>
    </tr>
</table>
<table cellpadding="10" cellspacing="10" class="border1 width100 b-top0 b-collapse" border="1">
    <tr>
        <td class="width28">Contractor Name </td>
        <td>{{ $employee->branch->branch_name }}</td>
        <td class="width25">Total Payable Days</td>
        <td class="width21">
            {{ number_format((float) $payroll->no_day_wages + $payroll->ph + $payroll->company_holiday, 2, '.', '') }}
        </td>
    </tr>
    <tr>
        <td>Employee ID </td>
        <td>{{ $employee->finger_id }}</td>
        <td>OT Hours</td>
        <td>{{ $payroll->ot_hours }}</td>
    </tr>
    <tr>
        <td>Name of the Employee</td>
        <td>{{ $employee->first_name . ' ' . $employee->last_name }}</td>
        <td>UAN No</td>
        <td>{{ $employee->pf_account_number }}</td>
    </tr>
    <tr>
        <td>Father's / Husband's Name</td>
        <td>{{ $employee->father_name }}</td>
        <td>ESIC No</td>
        <td>{{ $employee->esi_card_number }}</td>
    </tr>
    <tr>
        <td>Date of Joining</td>
        <td>{{ $employee->date_of_joining != '0000-00-00' ? DATE('d-m-Y', strtotime($employee->date_of_joining)) : '' }}
        </td>
        <td class="width28">Paid Holidays</td>
        <td class="width18">{{ number_format((float) $payroll->ph, 2, '.', '') }}</td>
    </tr>
    <tr>
        <td class="top-align">Designation</td>
        <td class="top-align">{{ $employee->designation->designation_name }}</td>
        <td class="top-align">Wages Period</td>
        <td class="top-align">
            {{ date('d/m/y', strtotime($payroll->fdate)) }}{{ ' - ' }}{{ date('d/m/y', strtotime($payroll->tdate)) }}
        </td>
    </tr>
</table>
<table cellpadding="10" cellspacing="10" class="border1 width100 b-top0 b-collapse" border="1">
    <tr>
        <td class="width28 al-right">Wages Earned </td>
        <td class="al-right">Rs</td>
        <td class="al-right">P</td>
        <td class="width21 al-right">Deduction</td>
        <td class="al-right">Rs</td>
        <td class="al-right">P</td>
    </tr>

    @php
        $gross_salary = number_format((float) $payroll->basic_amount + $payroll->da_amount + $payroll->hra_amount + $payroll->ot_amount, 2, '.', '');
        $total_deduction = number_format((float) $payroll->employer_esic + $payroll->employer_pf + $payroll->canteen + $payroll->lwf, 2, '.', '');
        $net_amount = number_format((float) $gross_salary - $total_deduction, 2, '.', '');
    @endphp
    <tr class="wages">
        <td>Basic </td>
        <td>{{ explode('.', $payroll->basic_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->basic_amount)[1] }}</td>
        <td>EPF</td>
        <td>{{ explode('.', $payroll->employer_pf)[0] }}</td>
        <td>{{ explode('.', $payroll->employer_pf)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>D.A </td>
        <td>{{ explode('.', $payroll->da_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->da_amount)[1] }}</td>
        <td>ESI</td>
        <td>{{ explode('.', $payroll->employer_esic)[0] }}</td>
        <td>{{ explode('.', $payroll->employer_esic)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>HRA </td>
        <td>{{ explode('.', $payroll->hra_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->hra_amount)[1] }}</td>
        <td>Other Deduction</td>
        <td>{{ 0 }}</td>
        <td>{{ 0 }}</td>
    </tr>
    <tr class="wages">
        <td>O.T Wages </td>
        <td>{{ explode('.', $payroll->ot_amount)[0] }}</td>
        <td>{{ explode('.', $payroll->ot_amount)[1] }}</td>
        <td>Canteen</td>
        <td>{{ explode('.', $payroll->canteen)[0] }}</td>
        <td>{{ explode('.', $payroll->canteen)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>Leave Wages </td>
        <td>0</td>
        <td>00</td>
        <td>LWF</td>
        <td>{{ explode('.', $payroll->lwf)[0] }}</td>
        <td>{{ explode('.', $payroll->lwf)[1] }}</td>
    </tr>
    <tr class="wages">
        <td>Other Allowance </td>
        <td>0</td>
        <td>00</td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    <tr>
        <td class="al-right text-italic text-bold">Gross Wages </td>
        <td class="al-right">{{ explode('.', $gross_salary)[0] }}</td>
        <td class="al-right">{{ explode('.', $gross_salary)[1] }}</td>
        <td class="al-right width21 text-italic text-bold">Total Deduction</td>
        <td class="al-right">{{ explode('.', $total_deduction)[0] }}</td>
        <td class="al-right">{{ explode('.', $total_deduction)[1] }}</td>
    </tr>
    <tr class="wages">
        <td style="color: white">Other Allowance </td>
        <td style="color: white"></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    @php
        $net_amt = number_format((float) $gross_salary - $total_deduction, 2, '.', '');
    @endphp
    <tr class="wages">
        <td><b>Net Amount</b></td>
        <td><b>{{ explode('.', $net_amt)[0] }}</b></td>
        <td><b>{{ explode('.', $net_amt)[1] }}</b></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>

</table>
<p class="al-center">This is computer generated payslip. No Signature is required.</p>
{{-- <table cellpadding="5" cellspacing="5" class="border1 width100 b-top0">
        <tr>
            <td class="padding25 al-center"></td>
            <td class="al-center"></td>
        </tr>
        <tr>
            <td class="al-center">Signature of the Employers / manager <br>or any other Authorised person</td>
            <td class="al-center">Signature of <br>Thumb Impression of the labour</td>
        </tr>
    </table> --}}
