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
            তারিখ: {{ en2bn(\Carbon\Carbon::parse($from)->format('d-m-Y')) }} ইং - {{ en2bn(\Carbon\Carbon::parse($to)->format('d-m-Y')) }} ইং
        </p>
    </div>
    <div class="summary-box">
        <table>
            <tr>
                <td><b>জরিমানা  </b></td>
                <td>{{ $numto->bnCommaLakh($records->sum('total_amount')) }} /=</td>
            </tr>
            <tr>
                <td><b>মওকুফ: </b></td>
                <td>{{ $numto->bnCommaLakh($records->sum('discount_amount') + $records->where('status',  'Released')->sum('total_amount')) }} /=</td>
            </tr>
             <tr>
                <td><b>আদায়:</b></td>
                <td>{{ $numto->bnCommaLakh($records->sum('pay_amount')) }} /=</td>
            </tr>
            {{-- <tr>
                <td><b>Absent:   </b></td>
                <td>{{ $absent}}</td>
            </tr>
            <tr>
                <td><b>Total :   </b></td>
                <td>{{ $present + $late + $leave + $absent}}</td>
            </tr> --}}
        </table>
       
    </div>
</div>

{{-- <h3>Monthly Attendance Report</h3> --}}

<table>
    <thead>
        <tr>
            <th>#.</th>
            <th>তারিখ</th>
            <th>গাড়ির নাম্বার</th>
            <th>জরিমানা</th>
            <th>মওকুফ শতাংশ</th>
            <th>মওকুফ পরিমাণ</th>
            <th>আদায়</th>
            <th>স্ট্যাটাস</th>
        </tr>
    </thead>

    <tbody>
       @foreach($records as $key => $record)
        

        <tr>
            <td>{{ en2bn($key + 1) }}</td>
            <td>{{ en2bn(\Carbon\Carbon::parse($record->invoice_date)->format('d-m-Y')) }}</td>
            <td>{{ en2bn($record->car_no) }}</td>
            <td>{{ en2bn($record->total_amount) }}</td>
            <td>{{ en2bn($record->discount) }} %</td>
            <td>{{ en2bn($record->discount_amount) }}</td>
            <td>{{ en2bn($record->pay_amount) }}</td>
            <td>{{ en2bn($record->status=='Released' ? 'অব্যাহতি' : 'মওকুফ') }}</td>
        </tr>
        @endforeach
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