<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product image sizes (FR-CAT-008)
    |--------------------------------------------------------------------------
    |
    | "cover" sizes crop to an exact square; "contain" sizes scale down and
    | preserve the original aspect ratio (never upscaled).
    |
    */

    'image_sizes' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'fit' => 'cover'],
        'card' => ['width' => 400, 'height' => 400, 'fit' => 'cover'],
        'detail' => ['width' => 800, 'height' => 800, 'fit' => 'contain'],
        'zoom' => ['width' => 1600, 'height' => 1600, 'fit' => 'contain'],
    ],

    'image_webp_quality' => 82,

    // Non-WebP fallback for browsers without WebP support, at "card" resolution.
    'image_fallback_quality' => 85,
];
