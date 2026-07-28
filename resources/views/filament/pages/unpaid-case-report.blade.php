@php
use Rakibhstu\Banglanumber\NumberToBangla;
$numto = new NumberToBangla();
@endphp
<x-filament-panels::page>
{{ $this->form }}
<div style="width:100%;display:flex;justify-content:flex-end;">
    <span style="font-weight:bold;">
        মোট মামলা: {{ $numto->bnCommaLakh( $this->getTableQuery()->count() )}} টি
    </span>
</div>
<div style="width:100%;display:flex;justify-content:flex-end;">
    <span style="font-weight:bold;">
        মোট অনাদায়: {{ $numto->bnCommaLakh( $this->getTableQuery()->sum('total_amount')) }} টাকা
    </span>
</div>
{{ $this->table }}
</x-filament-panels::page>
