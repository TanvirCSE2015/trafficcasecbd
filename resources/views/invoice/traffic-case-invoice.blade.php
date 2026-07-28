@php
use Rakibhstu\Banglanumber\NumberToBangla;
$numto = new NumberToBangla();
function en2bn($number) {
    $en = ['0','1','2','3','4','5','6','7','8','9','January','February','March','April','May','June','July','August','September','October','November','December'];
    $bn = ['০','১','২','৩','৪','৫','৬','৭','৮','৯','জানুয়ারি','ফেব্রুয়ারি','মার্চ','এপ্রিল','মে','জুন','জুলাই','আগস্ট','সেপ্টেম্বর','অক্টোবর','নভেম্বর','ডিসেম্বর'];
    return str_replace($en, $bn, $number);
}
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
  <meta charset="UTF-8">
  <title>Receipt</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  @media print {
    @page {
    margin-top: 0px;
    margin-bottom: 10px;
    /*padding-top: 40px*/
    }
    body {
        padding-top: 72px;
        padding-bottom: 72px ;
    }
}
    body {
      font-family: 'Kalpurush', 'Siyam Rupali', 'SolaimanLipi', sans-serif;
    }
    .dashed {
      border-bottom: 1px dashed #000;
      min-height: 24px;
    }
    .label {
      font-weight: bold;
    }
  </style>
</head>
<body class="container" onload="printReport()">

  <div class="row mb-2">
    <div class="col-6">
      ফরম নং ক্যান্ট ৪-বি <br>
      [ধারা ২৪ (১)]
    </div>
    <div class="col-6 text-end">
      তারিখ: <span class="dashed">{{ en2bn($lawsuit->pay_date) }}{{ 'ইং' }}</span>
    </div>
  </div>

<div class="row align-items-center mb-3">
  <!-- Left: Logo -->
  <div class="col-2 text-start">
    <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 60px;">
  </div>

  <!-- Center: Titles -->
  <div class="col-8 text-center">
    <h5 class="fw-bold mb-1">ক্যান্টনমেন্ট বোর্ড, ঢাকা ক্যান্টনমেন্ট</h5>
    <p class="fw-bold mb-0">মূল রশিদ</p>
  </div>

  <!-- Right: QR Code -->
  <div class="col-2 text-end">
    {!! QrCode::size(60)
    ->encoding('UTF-8')
    ->generate(
        'রশিদ নং: ' . en2bn($lawsuit->invoice_no) . "\n" .
        'মোট টাকা: ' . en2bn($lawsuit->pay_amount) . '/='
    )
!!}
  </div>
</div>


  <div class="row mb-2">
    <div class="col-6">
      রশিদ নং: <span class="fw-bold">{{ en2bn($lawsuit->invoice_no) }}</span>
    </div>
  </div>

  <div class="mb-2">
    <p>
        <span class="label">গাড়ী নং:</span>
        <span class="d-inline-block dashed text-center" style="min-width: 68%;">{{ en2bn($lawsuit->vechicle_number) }}</span>
        <span>এর বিপরীতে প্রাপ্ত</span>
  </p>
  </div>

  <div class="mb-2">
   <span class=""> টাকা (কথায়):</span>
    <span class="d-inline-block dashed text-center" style="min-width: 82%;">{{$numto->bnWord($lawsuit->pay_amount )}}{{ ' টাকা মাত্র' }}</span>
  </div>

  <div class="mb-2">
    বিষয়: <span class="d-inline-block dashed text-center" style="min-width: 91%;">জরিমানা</span>
  </div>

  {{-- <div class="mb-3">
    <div class="dashed w-100"></div>
  </div> --}}

  <div class="mb-4">
    টাকা: <div class="dashed w-25 d-inline-block text-center">{{ $numto->bnCommaLakh($lawsuit->pay_amount )}}{{ '/=' }}</div>
  </div>

<div class="col-sm-12 d-flex justify-content-end align-items-end mb-5">

    <div class="text-end">
        <p class="text-center" style="margin-bottom: 0rem;">ক্যান্টনমেন্ট এক্সিকিউটিভ অফিসার<br>
        ঢাকা ক্যান্টনমেন্ট</p>
        <p class="fs-6" style="font-size: 12px!important;">( সিস্টেম থেকে প্রিন্টকৃত কপি,কোন স্বাক্ষরের প্রয়োজন নেই )</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
