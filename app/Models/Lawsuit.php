<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lawsuit extends Model
{

    protected static function booted()
    {
        static::creating(function ($lawsuit) {
            if ($lawsuit->lawsuit_date) {
                $entry_date =\Carbon\Carbon::parse($lawsuit->lawsuit_date);
                $lawsuit->month_name = $entry_date->format('F');
                $lawsuit->month = (int) $entry_date->format('n');
                $lawsuit->year = (int) $entry_date->format('Y');
                $lawsuit->entry_user_id = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null;

            }
        });

        static::updated(function ($lawsuit) {
            if ($lawsuit->pay_date) {
                $pay_date = \Carbon\Carbon::parse($lawsuit->pay_date);
                $lawsuit->p_month_name = $pay_date->format('F');
                $lawsuit->p_month = (int) $pay_date->format('n');
                $lawsuit->p_year = (int) $pay_date->format('Y');
                $lawsuit->paid_user_id = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null;

            }

        });

        static::saving(function ($lawsuit) {
            if ($lawsuit->total_amount) {
                $lawsuit->discount_amount = $lawsuit->total_amount * ($lawsuit->discount / 100);
                $lawsuit->pay_amount = $lawsuit->total_amount - $lawsuit->discount_amount;
                $lawsuit->mp_percentage = 25; // Assuming a fixed percentage for MP
                $lawsuit->mp_amount = ($lawsuit->pay_amount) * ($lawsuit->mp_percentage / 100);
                $lawsuit->board_amount = $lawsuit->pay_amount - $lawsuit->mp_amount;
            }
            if($lawsuit->status === 'Released') {
                $lawsuit->discount_amount = 0;
                $lawsuit->pay_amount = 0;
                $lawsuit->mp_percentage = 25; // Assuming a fixed percentage for MP
                $lawsuit->mp_amount = 0;
                $lawsuit->board_amount = 0;
                $lawsuit->pay_date=date('d-m-Y');
                $lawsuit->p_month = date('m');
                $lawsuit->p_year = date('Y');
                $lawsuit->p_month_name = date('F');
                $lawsuit->paid_user_id = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null;
                $nesInvoice=CaseInvoice::create([
                    'lawsuit_id' => $lawsuit->id,
                    'car_no' => $lawsuit->vechicle_number,
                    'invoice_date' => date('d-m-Y'),
                    'month' => date('m'),
                    'month_name' => now()->format('F'),
                    'year' => now()->year,
                    'total_amount' => $lawsuit->total_amount,
                    'created_by' => auth()->id(),
                    'office_id' => $lawsuit->office_id,
                    'status' => $lawsuit->status,
                ]);
                
                $lawsuit->invoice_no = $nesInvoice->invoice_number;
            }
        });
    }
    public function lawsuitSections():HasMany
    {
        return $this->hasMany(LawsuitSection::class);
    }

    public function lawsuitDocuments():HasMany
    {
        return $this->hasMany(LawsuitDocument::class);
    }

    public function entryBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'entry_user_id');
    }

    public function paidBy():BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_user_id');
    }

    public function vechicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_type');
    }

}
