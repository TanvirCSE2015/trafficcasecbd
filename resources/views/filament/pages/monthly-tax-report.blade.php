@php
use Rakibhstu\Banglanumber\NumberToBangla;
$numto = new NumberToBangla();
@endphp
<x-filament-panels::page>
 {{ $this->form }}
<div style="width:100%;display:flex;justify-content:flex-end;">
    <span style="font-weight:bold;">
        মোট মামলা: {{ $numto->bnCommaLakh( $this->count ) }} টি
    </span>
</div>
<div style="width:100%;display:flex;justify-content:flex-end;margin-top:-2rem;">
    <span style="font-weight:bold;">
        মোট আদায়: {{ $numto->bnCommaLakh( $this->total ) }} টাকা
    </span>
</div>
{{ $this->table }}
</x-filament-panels::page>
