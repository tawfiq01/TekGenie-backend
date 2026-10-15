<?php

namespace App\Models;

use App\Exceptions\SlugMovedException;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /** @use HasFactory<PageFactory> */
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'body', 'meta_title', 'meta_description', 'is_published', 'show_in_footer',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'show_in_footer' => 'boolean',
        ];
    }

    /** FR-SET-005: an unresolved slug that matches a recorded old one 301s to the page's current URL instead of 404ing. */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $page = parent::resolveRouteBinding($value, $field);

        if ($page !== null || $field !== 'slug') {
            return $page;
        }

        $redirect = SlugRedirect::query()->where('entity_type', 'page')->where('old_slug', $value)->first();
        $moved = $redirect ? static::find($redirect->entity_id) : null;

        if ($moved) {
            throw new SlugMovedException($moved, 'page.show');
        }

        return null;
    }
}
