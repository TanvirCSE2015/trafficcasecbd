<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseInvoice extends Model
{

    protected static function booted()
    {
        static::creating(function ($invoice) {
            // Generate a unique invoice number, e.g., INV202507230001
            $prefix = 'INV-' . now()->format('Y-m') . '-';
            $lastInvoice = self::where('invoice_number', 'like', $prefix . '%')->orderByDesc('invoice_number')->first();
            $number = $lastInvoice
                ? ((int)substr($lastInvoice->invoice_number, -6)) + 1
                : 1;
            $invoice->invoice_number = $prefix . str_pad($number, 6, '0', STR_PAD_LEFT);
        });
    }

    public function lawsuit(): BelongsTo
    {
        return $this->belongsTo(Lawsuit::class, 'lawsuit_id');
    }
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
