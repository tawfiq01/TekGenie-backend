<?php

use App\Models\Setting;

it('disallows admin, account, cart and checkout, and references the sitemap (TC-SET-004)', function () {
    $response = $this->get(route('robots'));

    $response->assertOk();
    expect($response->headers->get('Content-Type'))->toContain('text/plain');

    $body = $response->getContent();
    expect($body)->toContain('Disallow: /admin')
        ->toContain('Disallow: /account')
        ->toContain('Disallow: /cart')
        ->toContain('Disallow: /checkout')
        ->toContain('Sitemap: '.route('sitemap'));
});

it('merges in admin-provided extra rules alongside the mandatory defaults', function () {
    Setting::updateOrCreate(['group' => 'seo', 'key' => 'robots_txt_extra'], ['value' => 'Disallow: /internal-tool']);

    $body = $this->get(route('robots'))->getContent();

    expect($body)->toContain('Disallow: /admin')
        ->toContain('Disallow: /internal-tool');
});
