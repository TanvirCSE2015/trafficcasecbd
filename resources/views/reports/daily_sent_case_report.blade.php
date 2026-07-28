<!DOCTYPE html>
<html>
<head>
    <title>Print Report</title>
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>
<body onload="printReport()">
 <style>
@media print {
    @page {
    margin-top: 0px;
    margin-bottom: 10px;
    padding-top: 40px
    }
    body {
        padding-top: 72px;
        padding-bottom: 72px ;
    }
}
</style>

@php
use Rakibhstu\Banglanumber\NumberToBangla;
$numto = new NumberToBangla();

 function en2bn($number): string
{
    $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    return str_replace($en, $bn, $number);
}
@endphp

 <div class="p-4">
    <div class="text-center">
        <!--<img src="{{ asset('images/logo.png') }}" alt="" srcset="">-->
        @if (isset($office) && $office)
           <h4 class=""><strong>{{ $office }}</strong></h4>

           @else
           <h4 class=""><strong> সকল ইউনিট</strong></h4>
            
        @endif
        <h6>যানবাহন মামলার তালিকা</h6>
        <h6>দৈনিক রিপোর্ট</h6>
        <h6 class="text-decoration-underline" >তারিখঃ {{ en2bn($date) }} ইং {{ $end_date ? ' হতে ' .  en2bn($end_date) .' ইং পর্যন্ত' : '' }}</h6>

    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="p-2 bd-highlight">মোট মামলা: {{ $numto->bnCommaLakh($total) }} টি</h6>
    </div>
    <table class="table table-bordered table-striped" id="sales-table">
    <thead>
        <tr class="text-center" style="font-size: 11px">
            <th>ক্রমিক নং</th>
            <th>প্রেরণের তারিখ</th>
            <th>অনিয়মের তারিখ</th>
            <th>গাড়ির নম্বর</th>
            <th>যানবাহনের প্রকার</th>
            <th>অনিয়মের স্থান</th>
            <th>অনিয়মের ধরণ</th>
            <th>আটকৃত নথি</th>
            <th>জরিমানা</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($records as $index => $record)
            <tr class="text-center" style="font-size: 11px">
                <td>{{ en2bn($index + 1) }}</td>
                <td>{{ en2bn($record->approval_date)}}</td>
                <td>{{ en2bn($record->lawsuit_date)}}</td>
                <td>{{ en2bn($record->vechicle_number) }}</td>
                <td>{{ $record->vechicleCategory->title }}</td>
                <td>{{ $record->location }}</td>
                <td style="min-width: 350px;">
                @foreach ($record->lawsuitSections as $section)
                   {{ $section->section->title. ', ' }}<br>
                @endforeach
                </td>
                <td style="min-width: 200px;">
                @foreach ($record->lawsuitDocuments as $document)
                   {{ $document->document->title. ', ' }}<br>
                @endforeach</td>
                <td>{{ $numto->bnCommaLakh($record->total_amount)}}/=</td>

            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
            </tr>
        @endforelse
            <tr>
                <td colspan="6" class="text-end">মোট</td>
                <td colspan="3" class="text-end"> {{ $numto->bnCommaLakh($records->sum('total_amount')) }} /= </td>
            </tr>
            <tr>
                <td colspan="6" class="text-end">কথায়</td>
                <td colspan="3" class="text-end"> {{ $numto->bnWord($records->sum('total_amount')) }} টাকা মাত্র </td>
            </tr>
        
    </tbody>
</table>
 </div>

 <script>
    function printReport() {
         window.print();
            window.onafterprint = function () {
                window.close();
            };
    }
 </script>
</body>
</html>
