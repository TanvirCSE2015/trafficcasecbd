<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawsuitSection extends Model
{
    public function lawsuit():BelongsTo
    {
        return $this->belongsTo(Lawsuit::class);
    }

    public function section():BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function caseEntry(): BelongsTo
    {
        return $this->belongsTo(CaseEntry::class, 'lawsuit_id');
    }
}
