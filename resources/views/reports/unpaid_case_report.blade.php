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
use Carbon\Carbon;
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
        <h6>অনিষ্পন্ন যানবাহন মামলা </h6>
        
        @if ($date)
            <h6>দৈনিক রিপোর্ট</h6>
            <h6 class="text-decoration-underline" > {{ en2bn(date('d-m-Y',strtotime($date))) }} ইং</h6>
            @elseif ($month && $year)
            <h6>মাসিক রিপোর্ট</h6>
             <h6 class="text-decoration-underline" > {{ en2bn(date('F', mktime(0, 0, 0, $month, 1))) .' '. en2bn($year) }} ইং</h6>
            @elseif ($year)
            <h6>বার্ষিক রিপোর্ট</h6>
            <h6 class="text-decoration-underline" > {{  en2bn($year) }} ইং</h6>
            @else
            <h6> রিপোর্ট</h6>
            <h6 class="text-decoration-underline" > {{ 'অদ্যবদি পর্যন্ত' }}</h6>
            
        @endif
        @if (isset($office) && $office)
           <h6 class=""><strong>ইউনিটঃ {{ $office }}</strong></h6>

           @else
           <h6 class=""><strong> সকল ইউনিট</strong></h6>
            
        @endif
        
      
    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="p-2 bd-highlight">মোট মামলা: {{ $numto->bnCommaLakh($total) }} টি </h6>
       
    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="p-2 bd-highlight">মোট টাকা: {{ $numto->bnCommaLakh($records->sum('pay_amount')) }} /= </h6>
    </div>
     
    <table class="table table-bordered table-striped" id="sales-table">
    <thead>
        <tr class="text-center" style="font-size: 14px">
            <th>গাড়ির নাম্বার</th>
            <th>মামলার তারিখ</th>
            <th>নিষ্পত্তির তারিখ</th>
            {{-- <th>দিন</th> --}}
            
            {{-- <th>মাস</th> 
            <th>বছর</th> --}}
            <th>জরিমানার পরিমান</th> 
            <th>মন্তব্য</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($records as $record)

            @php
                // $probableDate = Carbon::createFromFormat('d-m-Y', $record->probable_date);
                //         $diff = now()->startOfDay()->diffInDays($probableDate->startOfDay(), false);
                //         // Convert number to Bangla
                //         $banglaDiff = en2bn((string) intval($diff));

                //         // Return formatted text
                //         $day = $diff < 0
                //             ? "বিলম্ব {$banglaDiff} দিন" // e.g. "Overdue X days"
                //             : "{$banglaDiff} দিন বাকি";
            @endphp
            <tr class="text-center" style="font-size: 14px">
                <td>{{ en2bn($record->vechicle_number) }}</td>
                <td>{{ en2bn($record->lawsuit_date)}}{{ ' ইং' }}</td>
                <td>{{ en2bn($record->probable_date) }}</td>
                {{-- <td>{{ $day }}</td> --}}
                
                {{-- <td>{{ en2bn($record->month_name) }}</td>
                <td>{{ $numto->bnNum($record->year) }}</td> --}}
                {{-- <td>{{ $numto->bnNum($record->discount) }}</td>
                <td>{{ $numto->bnNum($record->discount_amount) }}</td> --}}

                <td>{{ $numto->bnCommaLakh($record->total_amount) }}</td>
                <td>{{ 'অনিষ্পন্ন' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
            </tr>
        @endforelse
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
