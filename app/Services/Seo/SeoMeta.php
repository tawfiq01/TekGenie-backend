<?php

namespace App\Services\Seo;

/** FR-SET-002: resolved title/description/canonical/Open Graph tags for one page, plus any JSON-LD blocks to embed. */
readonly class SeoMeta
{
    /** @param  array<int, array<string, mixed>>  $jsonLd */
    public function __construct(
        public string $title,
        public string $description,
        public string $canonical,
        public ?string $ogImage = null,
        public string $ogType = 'website',
        public array $jsonLd = [],
    ) {}

    /** @param  array<int, array<string, mixed>>  $jsonLd */
    public function withJsonLd(array $jsonLd): self
    {
        return new self($this->title, $this->description, $this->canonical, $this->ogImage, $this->ogType, $jsonLd);
    }
}
