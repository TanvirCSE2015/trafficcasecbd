<?php

namespace App\Http\Controllers;

use App\Models\CaseInvoice;
use App\Models\Lawsuit;
use App\Models\Office;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrintReportController extends Controller
{


    public function PrintDailyTaxCollectorReport(Request $request)
    {
        // Convert the date to a format suitable for querying
        $date =  $request->query('date');
        $office =  $request->query('office');

        // Fetch the data for the report based on the date
        $records = CaseInvoice::where('invoice_date', $date)
            ->when($date ?? null, fn ($query) => $query->where('invoice_date', $date))
            ->when($office ?? null, fn ($query) => $query->where('office_id', $office))
            ->with('lawsuit.lawsuitSections.section')
            ->get();
        $total = $records->sum('pay_amount');
        $office = Office::find($office)->name ?? null;

        // Return a view or a PDF with the data
        return view('reports.daily_tax_collector_report', compact('records', 'date', 'total','office'));
    }
    
    public function PrintMonthlyTaxReport(Request $request)
    {
        // Fetch the month and year from the request
        $month = $request->query('month');
        $year = $request->query('year');
        $office_id = $request->query('office_id');
        $type = $request->query('type');

        // Fetch the data for the report based on the month and year
        $records = CaseInvoice::query()
        ->selectRaw('
            ROW_NUMBER() OVER (ORDER BY invoice_date) as id,
            invoice_date,
            month,
            month_name,
            year,
            COUNT(*) as total_invoices,
            SUM(pay_amount) as total_amount
        ')
        ->when($month ?? null, fn ($query) => $query->where('month', $month))
        ->when($year ?? null, fn ($query) => $query->where('year', $year))
        ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
        ->groupBy('invoice_date', 'month','month_name', 'year')
        ->orderBy('invoice_date', 'asc')->get();
        $temp = CaseInvoice::query()
        ->when($month ?? null, fn ($query) => $query->where('month', $month))
        ->when($year ?? null, fn ($query) => $query->where('year', $year))
        ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))->get();
        
        $total=$temp->sum('pay_amount');
        $count=$temp->count();
        $office = Office::find($office_id)->name ?? null;

        // Return a view or a PDF with the data
        return view('reports.monthly_tax_report', compact('records', 'month', 'year', 'total','count','office','temp', 'type'));
    }

    // public function PrintMonthlyTaxReport(Request $request)
    // {
    //     // Fetch the month and year from the request
    //     $month = $request->query('month');
    //     $year = $request->query('year');
    //     $office_id = $request->query('office_id');

    //     // Fetch the data for the report based on the month and year
    //     $records = CaseInvoice::query()
    //     ->selectRaw('
    //         ROW_NUMBER() OVER (ORDER BY invoice_date) as id,
    //         invoice_date,
    //         month,
    //         month_name,
    //         year,
    //         SUM(pay_amount) as total_amount
    //     ')
    //     ->when($month ?? null, fn ($query) => $query->where('month', $month))
    //     ->when($year ?? null, fn ($query) => $query->where('year', $year))
    //     ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
    //     ->groupBy('invoice_date', 'month','month_name', 'year')
    //     ->orderBy('invoice_date', 'asc')->get();
    //     $temp = CaseInvoice::query()
    //     ->when($month ?? null, fn ($query) => $query->where('month', $month))
    //     ->when($year ?? null, fn ($query) => $query->where('year', $year))
    //     ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id));
        
    //     $total=$temp->sum('pay_amount');
    //     $count=$temp->count();
    //     $office = Office::find($office_id)->name ?? null;

    //     // Return a view or a PDF with the data
    //     return view('reports.monthly_tax_report', compact('records', 'month', 'year', 'total','count','office'));
    // }

    public function PrintYearlyTaxReport(Request $request)
    {
        // Fetch the year from the request
        $year = $request->query('year');
        $office_id = $request->query('office_id');

        // Fetch the data for the report based on the year
        $records = CaseInvoice::query()
            ->selectRaw('
                ROW_NUMBER() OVER (ORDER BY month) as id,
                month,
                month_name,
                year,
                office_id,
                SUM(pay_amount) as total_amount
            ')
            ->when($year, fn ($query) => $query->where('year', $year))
            ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
            ->groupBy('month', 'month_name', 'year', 'office_id')
            ->orderBy('month', 'asc')
            ->get();
        $total = CaseInvoice::query()
            ->when($year ?? null, fn ($query) => $query->where('year', $year))
            ->when($office_id ?? null, fn($query)=> $query->where('office_id', $office_id))
            ->sum('pay_amount');

        $office= Office::find($office_id)->name ?? null;

        // Return a view or a PDF with the data
        return view('reports.yearly_tax_report', compact('records', 'year', 'total', 'office'));
    }

    public function PrintYearlyFinancialReport(Request $request){
        // Fetch the month and year from the request
        $month = $request->query('month');
        $year = $request->query('year');
        $office_id = $request->query('office_id');

        // Fetch the data for the report based on the month and year
        $records = CaseInvoice::query()
        ->selectRaw('
            ROW_NUMBER() OVER (ORDER BY invoice_date) as id,
            month,
            month_name,
            mp_percentage,
            year,
            office_id,
            SUM(pay_amount) as total_amount,
            SUM(mp_amount) as total_mp_amount,
            SUM(board_amount) as total_board_amount'

            )
        ->when($month ?? null, fn ($query) => $query->where('month', $month))
        ->when($year ?? null, fn ($query) => $query->where('year', $year))
        ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
        ->groupBy( 'month','month_name', 'year','mp_percentage', 'office_id')
        ->orderBy('month', 'asc')->get();

        $total = CaseInvoice::query()
        ->when($month ?? null, fn ($query) => $query->where('month', $month))
        ->when($year ?? null, fn ($query) => $query->where('year', $year))
        ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
        ->sum('pay_amount');

        $office = Office::find($office_id)->name ?? null;

        // Return a view or a PDF with the data
        return view('reports.yearly_financial_report', compact('records', 'month', 'year', 'total','office'));
    }

    public function PrintDailySentCaseReport(Request $request)
    {
        // Convert the date to a format suitable for querying
        $date =  $request->query('date');
        $end_date = $request->query('end_date');
        $userId = $request->query('user_id');
        $officeId = $request->query('office_id');
        $status = $request->query('status');
        
         $dateColumn = $status === 'pending'
        ? 'lawsuit_date'
        : 'approval_date';
        // Fetch the data for the report based on the date
        $records = Lawsuit::query()
        ->where('case_status', $status)

        ->when($date && $end_date, function ($query) use($date,$end_date,$dateColumn) {
            $query->whereRaw(
                "STR_TO_DATE($dateColumn, '%d-%m-%Y') BETWEEN ? AND ?",
                [
                    $date,
                    $end_date,
                ]
            );
        })

        ->when($date && !$end_date, function ($query) use ($date,$dateColumn) {
            $query->where($dateColumn, Carbon::parse($date)->format('d-m-Y'));
        })
        ->when($officeId ?? null, fn ($query) => $query->where('office_id', $officeId))
        ->when($userId ?? null, fn ($query) => $query->where('entry_user_id', $userId))
        ->with('lawsuitSections.section', 'lawsuitDocuments.document','vechicleCategory')->get();
        $total= $records->count();
        $office = Office::find($officeId)->name ?? null;
        // Return a view or a PDF with the data
        return view('reports.daily_sent_case_report', compact('records', 'date', 'end_date', 'total','office'));
    }

    public function PrintUnpaidCaseReport(Request $request)
    {
        $date=$request->query('date');
        $month = $request->query('month');
        $year = $request->query('year');
        $office_id = $request->query('office_id');

        $records= Lawsuit::query()
        ->when($date ?? null, fn ($query) => $query->where('lawsuit_date', date('d-m-Y',strtotime($date))))
        ->when($month ?? null, fn ($query) => $query->where('month', $month))
        ->when($year ?? null, fn ($query) => $query->where('year', $year))
        ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
        ->where(['status'=>'Unpaid','case_status'=>'approved'])->get();

         $total= $records->count();
         $office = Office::find($office_id)->name ?? null;
        // Return a view or a PDF with the data
        return view('reports.unpaid_case_report', compact('records', 'date','month','year', 'total','office'));

    }
    
    public function PrintRemissionReport(Request $request)
    {
        $from = $request->query('from');
        $to = $request->query('to');
        $office_id = $request->query('office_id');
        $status = $request->query('status');

        $records = CaseInvoice::query()
            ->when($from && $to, function ($query) use ($from, $to) {
                $query->whereBetween(
                    DB::raw("STR_TO_DATE(invoice_date, '%d-%m-%Y')"),
                    [
                        Carbon::parse($from)->format('Y-m-d'),
                        Carbon::parse($to)->format('Y-m-d'),
                    ]
                );
            })
            ->when($office_id ?? null, fn ($query) => $query->where('office_id', $office_id))
            ->when(
                $status,
                function ($query) use ($status) {
                    if ($status === 'Released') {
                        $query->where('status', 'Released');
                    } elseif ($status === 'remission') {
                        $query->where('discount_amount', '>', 0);
                    }
                },
                function ($query) {
                    // No status selected
                    $query->where(function ($q) {
                        $q->where('status', 'Released')
                            ->orWhere('discount_amount', '>', 0);
                    });
                }
            )
            ->with(['lawsuit.lawsuitSections.section'])
            ->orderBy('invoice_date', 'asc')
            ->get();

        $total = $records->sum('pay_amount');
        $count = $records->count();
        $office = Office::find($office_id)->name ?? null;

        return view('reports.remission_report', compact('records', 'from', 'to', 'total', 'count', 'office'));
    }
}
