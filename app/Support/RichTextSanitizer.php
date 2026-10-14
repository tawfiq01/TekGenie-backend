<?php

namespace App\Support;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Sanitises product descriptions to the VR-11 allow-list: p, h2–h4, ul, ol,
 * li, strong, em, a[href], img[src,alt] from own storage, table elements.
 */
final class RichTextSanitizer
{
    public static function sanitize(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $config = (new HtmlSanitizerConfig)
            ->allowElement('p')
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('h4')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('a', ['href'])
            ->allowElement('img', ['src', 'alt'])
            ->allowElement('table')
            ->allowElement('thead')
            ->allowElement('tbody')
            ->allowElement('tr')
            ->allowElement('th')
            ->allowElement('td')
            ->allowLinkSchemes(['https'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->allowMediaHosts(array_filter([parse_url((string) config('app.url'), PHP_URL_HOST)]));

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
