<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>

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
            font-size: 12px;
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
            font-size: 12px;
        }
        .text-left {
            text-align: left;
        }
        
         .summary-box {
            position: absolute;
            right: 0;
            top: 30px;
            border: 1px solid #000;
            padding: 3px 5px;
            font-size: 11px;
            text-align: left;
            min-width: 100px;
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
        <p>{{ $office ?? 'সকল ইউনিট' }}</p>
        <p>
            তারিখ: {{ en2bn(\Carbon\Carbon::parse($date)->format('d-m-Y')) }} ইং 
        </p>
    </div>
    <div class="summary-box">
        <table>
            <tr>
                <td><b> মোট মামলা </b></td>
                <td>{{ $numto->bnCommaLakh($records->count()) }} টি</td>
            </tr>
            <tr>
                <td><b>মওকুফ: </b></td>
                <td>{{ $numto->bnCommaLakh($records->sum('discount_amount') + $records->where('status',  'Released')->sum('total_amount')) }} /=</td>
            </tr>
             <tr>
                <td><b>মোট আদায়:</b></td>
                <td>{{ $numto->bnCommaLakh($total) }} /=</td>
            </tr>
        </table>
       
    </div>
</div>

{{-- <h3>Monthly Attendance Report</h3> --}}

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>তারিখ</th>
            <th>গাড়ী নং</th>
            {{-- <th>অপরাধের ধারা</th> --}}
            {{-- <th>মোট পরিমাণ</th>
            <th>ছাড় (%)</th>
            <th>ছাড় পরিমাণ</th> --}}
            <th>রশিদ নং</th>
            <th>মোট টাকা</th>   
        </tr>
    </thead>

    <tbody>
       @forelse($records as $index => $record)
        

        <tr>
            <td>{{ en2bn($index + 1) }}</td>
            <td>{{ en2bn($record->invoice_date)}}</td>
            <td>{{ en2bn($record->car_no) }}</td>
            {{-- <td>{{ $numto->bnNum($record->total_amount) }}</td>
            <td>{{ $numto->bnNum($record->discount) }}</td>
            <td>{{ $numto->bnNum($record->discount_amount) }}</td> --}}
            <td>{{ en2bn($record->invoice_number) }}</td>
            <td>{{ $numto->bnCommaLakh($record->pay_amount) }}</td>
        </tr>
         @empty
            <tr>
                <td colspan="5" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
            </tr>
        @endforelse
        <tr>
            <td colspan="4" class="text-end">মোট আদায়</td>
            <td> {{ $numto->bnCommaLakh($total) }} /= </td>
        </tr>
        <tr>
            <td colspan="2" class="text-end">কথায়</td>
            <td colspan="3" class="text-end"> {{ $numto->bnWord($total) }} টাকা মাত্র </td>
        </tr>
    </tbody>
</table>

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

 <div class="p-4 pt-0">
    <div class="text-center">
        <img src="{{ asset('images/logo.png') }}" alt="" srcset="">
        <h4>ঢাকা ক্যান্টনমেন্ট বোর্ড</h4>
        <h6>যানবাহন মামলার জরিমানা আদায়</h6>
        
        <h6>দৈনিক রিপোর্ট</h6>
        @if (isset($office) && $office)
           <h6 class=""><strong>ইউনিটঃ {{ $office }}</strong></h6> 
           @else
           <h6 class=""><strong> সকল ইউনিট</strong></h6>
        @endif
        <h6 class="text-decoration-underline" >তারিখঃ {{ en2bn($date) }} ইং</h6>
      
    </div>
    <div class="d-flex flex-row-reverse bd-highlight">
        <h6 class="p-2 bd-highlight">মোট আদায়: {{ $numto->bnCommaLakh($total) }} /= </h6>
    </div>
    <table class="table table-bordered table-striped" id="sales-table">
    <thead>
        <tr class="text-center" style="font-size: 14px">
            <th>ক্রমিক নং</th>
            <th>তারিখ</th>
            <th>গাড়ী নং</th>
            
            <th>রশিদ নং</th>
            <th>মোট টাকা</th>
            
        </tr>
    </thead>
    <tbody>
        @forelse ($records as $index => $record)
            <tr class="text-center" style="font-size: 14px">
                <td>{{ en2bn($index + 1) }}</td>
                <td>{{ en2bn($record->invoice_date)}}</td>
                <td>{{ en2bn($record->car_no) }}</td>
               
                <td>{{ en2bn($record->invoice_number) }}</td>
                <td>{{ $numto->bnCommaLakh($record->pay_amount) }}</td>
                
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">কোন তথ্য পাওয়া যায় নি ।</td>
            </tr>
        @endforelse
            <tr>
                <td colspan="4" class="text-end">মোট আদায়</td>
                <td> {{ $numto->bnCommaLakh($total) }} /= </td>
            </tr>
            <tr>
                <td colspan="2" class="text-end">কথায়</td>
                <td colspan="3" class="text-end"> {{ $numto->bnWord($total) }} টাকা মাত্র </td>
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
</html> --}}
