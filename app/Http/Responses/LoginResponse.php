<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/**
 * FR-WSH-001: lets a guest be returned to the exact page they came from
 * (e.g. a product, to finish adding it to their wishlist) after logging
 * in, via a same-origin "redirect" field carried through the login form —
 * never an open redirect to another host.
 */
class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $redirect = $this->safeRedirect($request);

        return $redirect !== null
            ? redirect()->to($redirect)
            : redirect()->intended(config('fortify.home'));
    }

    private function safeRedirect(Request $request): ?string
    {
        $redirect = $request->input('redirect');

        // Reject anything that isn't either a relative path or an absolute
        // URL on this same host — a scheme-relative "//evil.com" or a
        // different host is an open redirect, not a valid destination.
        if (! is_string($redirect) || $redirect === '' || str_starts_with($redirect, '//')) {
            return null;
        }

        $host = parse_url($redirect, PHP_URL_HOST);

        return $host === null || $host === $request->getHost() ? $redirect : null;
    }
}
