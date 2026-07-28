<!DOCTYPE html>
<html>
<head>
    <title>যানবাহন মামলার জরিমানা আদায়েরর রিপোর্ট</title>

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        th {
            background: #eee;
        }

         .header {
            position: relative;
            text-align: center;
        }

        .header-left {
            position: absolute;
            left: 0;
            top: 0;
        }
        .logo {
            height: 60px;
        }


        .title h2 {
            margin: 0;
            font-size: 18px;
        }

        .title p {
            margin: 2px 0;
            font-size: 14px;
        }
        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        
        /* .summary-box {*/
        /*    left: 10px;*/
        /*    top: 10px;*/
        /*    border: 1px solid #000;*/
        /*    padding: 3px 5px;*/
        /*    font-size: 14px;*/
        /*    text-align: left;*/
        /*    min-width: 100px;*/
        /*    margin-bottom: 10px;*/
        /*}*/
         .summary-box {
            display: flex;
            margin: 10px auto;
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 11px;
            text-align: center;
        }

        .summary-box p {
            margin: 2px 0;
            margin-bottom: 4px;
            /* border-bottom: 1px solid #000; */
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
</head>
<body>
<div class="header">
    <img src="{{ asset('images/logo.png') }}" class="logo">

    <div class="title">
         <h2>ঢাকা ক্যান্টনমেন্ট বোর্ড</h2>
        <p><b>যানবাহন মামলা</b></p>
        <p>
            <b>{{ en2bn(date('F', mktime(0, 0, 0, $month, 1))) .' - '. en2bn($year) }} ইং </b>
        </p>
    </div>
    <div class="summary-box" style="max-width: 60%; justify-content: center;">
        <table>
            <tr>
                <td class="text-right"><b> মোট নিস্পত্তিকৃত মামলার সংখ্যা  </b></td>
                <td><b>{{ $numto->bnCommaLakh($count) }}</b></td>
                <td class="text-left"><b>টি</b></td>
            </tr>
            @if($type == 'yes')
            <tr>
                <td class="text-right"><b>মওকুফ =</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->sum('discount_amount') + $temp->where('status',  'Released')->sum('total_amount')) }}</b></td>
                <td class="text-left"><b>টাকা</b></td>
            </tr>
            @endif
            <tr>
                <td class="text-right"><b>সর্বমোট জরিমানা আদায় =</b></td>
                <td><b>{{ $numto->bnCommaLakh($total) }}</b></td>
                <td class="text-left"><b>টাকা</b></td>
            </tr>
            <tr>
                <td class="text-right"><b>মোট জরিমানা আদায়ের ২৫ % =</b></td>
                <td><b>{{ $numto->bnCommaLakh($total * 0.25) }}</b></td>
                <td class="text-left"><b>টাকা</b></td>
            </tr>
             
        </table>
       
    </div>
</div>

{{-- <h3>Monthly Attendance Report</h3> --}}

<table>
    <thead>
        <tr>
            <th>ক্রম</th>
            <th>নিস্পত্তির তারিখ</th>
            <th>মামলার সংখ্যা</th>
            <th>জরিমানা আদায়</th>
            <th>২৫% হার</th>   
        </tr>
    </thead>

    <tbody>
       @forelse($records as $index => $record)
        

        <tr>
            <td><b>{{ en2bn($index + 1) }}</b></td>
            <td><b>{{ en2bn($record->invoice_date)}}</b></td>
            <td><b>{{ en2bn($record->total_invoices) }}</b></td>
            <td><b>{{ $numto->bnCommaLakh($record->total_amount) }}</b></td>
            <td><b>{{ $numto->bnCommaLakh($record->total_amount * 0.25) }}</b></td>
        </tr>
         @empty
            <tr>
                <td colspan="5" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
            </tr>
        @endforelse
         <tr>
            <td colspan="2" class="text-end"><b>মোট </b></td>
            <td><b> {{ $numto->bnCommaLakh($records->sum('total_invoices')) }}  </b></td>
            <td><b> {{ $numto->bnCommaLakh($records->sum('total_amount')) }}  </b></td>
            <td><b> {{ $numto->bnCommaLakh($records->sum('total_amount') * 0.25) }}  </b></td>
        </tr>
        {{-- <tr>
            <td colspan="2" class="text-end">কথায়</td>
            <td colspan="3" class="text-end"> {{ $numto->bnWord($total) }} টাকা মাত্র </td>
        </tr> --}}
    </tbody>
</table>
<div class="summary-box" style="margin-top: 20px;">
    <table>
        <thead>
            <tr>
                <th>ইউনিটের নাম</th>
                <th>মামলার সংখ্যা</th>
                <th>জরিমানা আদায়</th>
                <th>২৫% হার</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><b>আর্মি এমপি ও লজিস্টিক এমপি</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->whereIn('office_id', [1,3, 4])->count()) }} টি</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->whereIn('office_id', [1,3, 4])->sum('pay_amount')) }} /=</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->whereIn('office_id', [1,3, 4])->sum('pay_amount') * 0.25) }} /=</b></td>
            </tr>
            <tr>
                <td><b>প্রভোস্ট ও নিরাপত্তা ইউনিট, বিমান বাহিনী</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->whereIn('office_id', [6])->count()) }} টি</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->whereIn('office_id', [6])->sum('pay_amount')) }} /=</b></td>
                <td><b>{{ $numto->bnCommaLakh($temp->whereIn('office_id', [6])->sum('pay_amount') * 0.25) }} /=</b></td>
            </tr>
        </tbody>
    </table>
</div>
<script>
    window.onload = function () {
        window.print();
        window.onafterprint = function () {
            window.close();
        };

    }
</script>

</body>

</html>








{{-- <!DOCTYPE html>
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
        <h6>মাসিক রিপোর্ট</h6>
        @if (isset($office) && $office)
           <h6 class=""><strong>ইউনিটঃ {{ $office }}</strong></h6>
            @else
           <h6 class=""><strong> সকল ইউনিট</strong></h6>
        @endif
        <h6 class="text-decoration-underline" > {{ en2bn(date('F', mktime(0, 0, 0, $month, 1))) .' '. en2bn($year) }} ইং</h6>
      
    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="p-2 bd-highlight">মোট মামলা: {{ $numto->bnCommaLakh($count) }}  টি </h6>
        
    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="bd-highlight">মোট আদায়: {{ $numto->bnCommaLakh($total) }} /= </h6>
    </div>
    <table class="table table-bordered table-striped" id="sales-table">
    <thead>
        <tr class="text-center" style="font-size: 14px">
            <th>তারিখ</th>
            <th>মাস</th> 
             <th>বছর</th>
            
            <th>মোট আদায়</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($records as $record)
            <tr class="text-center" style="font-size: 14px">
                <td>{{ en2bn($record->invoice_date)}}{{ ' ইং' }}</td>
                <td>{{ en2bn($record->month_name) }}</td>
                <td>{{ $numto->bnNum($record->year) }}</td>
                

                <td>{{ $numto->bnCommaLakh($record->total_amount) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
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
</html> --}}
