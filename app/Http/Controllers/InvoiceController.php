<?php

namespace App\Http\Controllers;

use App\Models\Lawsuit;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class InvoiceController extends Controller
{
    public function PrintInvoice(Request $request)
    {
        $id=$request->query('lawsuit'); // Assuming you pass the record ID in the query string
        $lawsuit = Lawsuit::with(['lawsuitSections.section', 'lawsuitDocuments.document'])
            ->findOrFail($id);
        return view('invoice.traffic-case-invoice',compact('lawsuit')); // Example view for printing invoices
    }


}
