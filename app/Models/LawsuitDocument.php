<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LawsuitDocument extends Model
{
    public function lawsuit():BelongsTo
    {
        return $this->belongsTo(Lawsuit::class);
    }
    public function document():BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
