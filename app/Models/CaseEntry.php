<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CaseEntry extends Model
{
    protected $table = 'lawsuits';
    public $timestamps = true;

    protected static function booted()
    {

        static::creating(function ($caseEntry) {
            if ($caseEntry->lawsuit_date) {
                $entry_date = \Carbon\Carbon::parse($caseEntry->lawsuit_date);
                $caseEntry->month_name = $entry_date->format('F');
                $caseEntry->month = (int) $entry_date->format('n');
                $caseEntry->year = (int) $entry_date->format('Y');
                $caseEntry->entry_user_id = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null;
                $caseEntry->office_id = \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->office_id : null;
            }
        });

    }

    public function lawsuitSections(): HasMany
    {
        return $this->hasMany(LawsuitSection::class, 'lawsuit_id');
    }
    public function lawsuitDocuments(): HasMany
    {
        return $this->hasMany(LawsuitDocument::class, 'lawsuit_id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    public function vechicleCategory(): BelongsTo
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_type');
    }

}
