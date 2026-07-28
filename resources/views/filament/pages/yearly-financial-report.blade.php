@php
use Rakibhstu\Banglanumber\NumberToBangla;
$numto = new NumberToBangla();
@endphp
<x-filament-panels::page>
 {{ $this->form }}
<div style="width:100%;display:flex;justify-content:flex-end;">
    <span style="font-weight:bold;">
        মোট আদায়: {{ $numto->bnCommaLakh( $this->total ) }} টাকা
    </span>
    
</div>
<div style="width:100%;display:flex;justify-content:flex-end;">
    
    <span style="font-weight:bold;">
        বোর্ড: {{ $numto->bnCommaLakh( $this->total-($this->total * .25) ) }} টাকা
    </span>
    
</div>
<div style="width:100%;display:flex;justify-content:flex-end;">
    
    <span style="font-weight:bold;">
        এমপি: {{ $numto->bnCommaLakh($this->total * .25) }} টাকা
    </span>
    
</div>
{{ $this->table }}
</x-filament-panels::page>