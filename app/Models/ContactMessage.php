<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactMessage extends Model
{
    protected $fillable = ['name', 'phone', 'email', 'subject', 'message', 'ip_address', 'handled_at', 'handled_by'];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Admin, $this> */
    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }
}
