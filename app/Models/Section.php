<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    public function lawsuitSections():HasMany
    {
        return $this->hasMany(LawsuitSection::class);
    }

}
