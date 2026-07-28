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
        <img src="{{ asset('images/logo.png') }}" alt="" srcset="">
        <h4>ঢাকা ক্যান্টনমেন্ট বোর্ড</h4>
        <h6>যানবাহন মামলার জরিমানা আদায়</h6>
        <h6>বার্ষিক রিপোর্ট</h6>
        @if (isset($office) && $office)
           <h6 class=""><strong>ইউনিটঃ {{ $office }}</strong></h6>

           @else
           <h6 class=""><strong> সকল ইউনিট</strong></h6>
            
        @endif
        <h6 class="text-decoration-underline" >বছরঃ {{ en2bn($year) }} ইং</h6> 
      
    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="p-2 bd-highlight">মোট আদায়: {{ $numto->bnCommaLakh($total) }} /= </h6>
    </div>
    <table class="table table-bordered table-striped" id="sales-table">
    <thead>
        <tr class="text-center" style="font-size: 14px">
            <th>মাস</th> 
             <th>বছর</th>
            {{-- <th>মোট পরিমাণ</th>
            <th>ছাড় (%)</th>
            <th>ছাড় পরিমাণ</th> --}}
            <th>ইউনিট</th>
            <th>মোট আদায়</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($records as $record)
            <tr class="text-center" style="font-size: 14px">
                <td>{{ en2bn($record->month_name) }}</td>
                <td>{{ $numto->bnNum($record->year) }}</td>
                {{-- <td>{{ $numto->bnNum($record->discount) }}</td>
                <td>{{ $numto->bnNum($record->discount_amount) }}</td> --}}
                <td>{{ $record->office->name }}</td>
                <td>{{ $numto->bnCommaLakh($record->total_amount) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
            </tr>
        @endforelse
            <tr>
                <td colspan="3" class="text-end">মোট আদায়</td>
                <td colspan="" class="text-center">{{$numto->bnCommaLakh($total)}}</td>
            </tr>
            <tr>
                <td colspan="2" class="text-end">কথায়</td>
                <td colspan="2" class="text-end"> {{ $numto->bnWord($total) }} টাকা মাত্র </td>
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
