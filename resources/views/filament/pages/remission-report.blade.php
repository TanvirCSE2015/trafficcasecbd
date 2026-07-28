@php
use Rakibhstu\Banglanumber\NumberToBangla;
$numto = new NumberToBangla();
@endphp
<x-filament-panels::page>
    {{ $this->form }}
<div style="width:100%;display:flex;justify-content:flex-end;">
    <span style="font-weight:bold;">
        মোট জরিমানা: {{ $numto->bnCommaLakh( $this->getTableQuery()->sum('total_amount')) }} টাকা
    </span>
</div>
<div style="width:100%;display:flex;justify-content:flex-end; margin-top:-2rem;">
    
    <span style="font-weight:bold;">
        মোট মওকুফ: {{ $numto->bnCommaLakh( $this->getTableQuery()->sum('discount_amount')
         + $this->getTableQuery()->where('status', 'Released')->sum('total_amount'))}} টাকা
    </span>
</div>
<div style="width:100%;display:flex;justify-content:flex-end; margin-top:-2.4rem;">
    <span style="font-weight:bold;">
        মোট আদায়: {{ $numto->bnCommaLakh( $this->getTableQuery()->sum('pay_amount')) }} টাকা
    </span>
</div>
    {{ $this->table }}
</x-filament-panels::page>
