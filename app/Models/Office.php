<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    public function caseInvoices(): HasMany
    {
        return $this->hasMany(CaseInvoice::class, 'office_id');
    }
}
