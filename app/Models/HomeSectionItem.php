<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HomeSectionItem extends Model
{
    public $timestamps = false;

    protected $fillable = ['home_section_id', 'item_type', 'item_id', 'sort_order'];

    /** @return BelongsTo<HomeSection, $this> */
    public function homeSection(): BelongsTo
    {
        return $this->belongsTo(HomeSection::class);
    }
}
