<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * FR-SET-005: thrown from a model's resolveRouteBinding() when a slug isn't
 * found but matches a recorded old slug — caught in bootstrap/app.php and
 * rendered as a 301 to the entity's current URL, ahead of the 404 that an
 * ordinary failed binding would otherwise produce.
 */
class SlugMovedException extends RuntimeException
{
    public function __construct(
        public readonly Model $entity,
        public readonly string $routeName,
    ) {
        parent::__construct("Slug moved — redirecting via route [{$routeName}].");
    }
}
