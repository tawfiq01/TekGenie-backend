<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 */
class Banner extends Model
{
    protected $fillable = [
        'placement', 'title', 'subtitle', 'button_label', 'link_url',
        'image_desktop_path', 'image_mobile_path', 'alt_text',
        'starts_at', 'ends_at', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function isCurrentlyScheduled(): bool
    {
        $now = now();

        return ($this->starts_at === null || $this->starts_at->lte($now))
            && ($this->ends_at === null || $this->ends_at->gte($now));
    }
}
