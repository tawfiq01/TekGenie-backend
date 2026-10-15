<x-layouts.app>
    <div class="space-y-16">
        @foreach ($sections as $section)
            @php($items = $data[$section->type->value] ?? null)

            @switch($section->type)
                @case (\App\Enums\HomeSectionType::HeroBanner)
                    @if ($items && $items->isNotEmpty())
                        <section>
                            @foreach ($items as $banner)
                                @php($isLcpCandidate = $loop->parent->first && $loop->first)
                                <a href="{{ $banner->link_url ?? '#' }}" class="block overflow-hidden rounded-[var(--radius-xl)] bg-bg-subtle">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_desktop_path) }}"
                                         alt="{{ $banner->alt_text }}" class="hidden w-full sm:block"
                                         loading="{{ $isLcpCandidate ? 'eager' : 'lazy' }}"
                                         @if ($isLcpCandidate) fetchpriority="high" @endif>
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($banner->image_mobile_path ?? $banner->image_desktop_path) }}"
                                         alt="{{ $banner->alt_text }}" class="w-full sm:hidden"
                                         loading="{{ $isLcpCandidate ? 'eager' : 'lazy' }}"
                                         @if ($isLcpCandidate) fetchpriority="high" @endif>
                                </a>
                            @endforeach
                        </section>
                    @endif
                    @break

                @case (\App\Enums\HomeSectionType::CategoryGrid)
                    @if ($items && $items->isNotEmpty())
                        <section>
                            <div class="flex items-center justify-between">
                                <h2 class="text-xl font-bold text-ink">{{ $section->title }}</h2>
                                <a href="{{ route('shop.index') }}" class="text-sm text-link hover:underline">All &rarr;</a>
                            </div>
                            <div class="mt-4 grid grid-cols-3 gap-4 sm:grid-cols-4 lg:grid-cols-8">
                                @foreach ($items as $category)
                                    <a href="{{ route('category.show', $category) }}" class="text-center">
                                        <div class="aspect-square overflow-hidden rounded-[var(--radius-md)] bg-bg-subtle">
                                            @if ($category->image_path)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($category->image_path) }}" alt="{{ $category->name }}" loading="lazy" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <p class="mt-1 text-xs text-ink-soft">{{ $category->name }}</p>
                                    </a>
                                @endforeach
                            </div>
                        </section>
                    @endif
                    @break

                @case (\App\Enums\HomeSectionType::FeaturedProducts)
                @case (\App\Enums\HomeSectionType::LatestProducts)
                @case (\App\Enums\HomeSectionType::BestSellers)
                    @if ($items && $items->isNotEmpty())
                        <section>
                            <h2 class="text-xl font-bold text-ink">{{ $section->title }}</h2>
                            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($items as $product)
                                    <x-storefront.product-card :product="$product" />
                                @endforeach
                            </div>
                        </section>
                    @endif
                    @break

                @case (\App\Enums\HomeSectionType::PromoBanner)
                    @if ($items && $items->isNotEmpty())
                        <section class="rounded-[var(--radius-xl)] bg-accent-soft p-8 text-center">
                            @foreach ($items as $banner)
                                <h2 class="text-xl font-bold text-ink">{{ $banner->title }}</h2>
                                <p class="mt-1 text-ink-soft">{{ $banner->subtitle }}</p>
                                @if ($banner->link_url && $banner->button_label)
                                    <a href="{{ $banner->link_url }}" class="mt-4 inline-block rounded-[var(--radius-pill)] bg-ink px-5 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                                        {{ $banner->button_label }}
                                    </a>
                                @endif
                            @endforeach
                        </section>
                    @endif
                    @break

                @case (\App\Enums\HomeSectionType::TrustBadges)
                    <section class="grid grid-cols-1 gap-4 rounded-[var(--radius-lg)] border border-border p-6 text-center text-sm text-ink-soft sm:grid-cols-3">
                        <p>✓ Cash on delivery</p>
                        <p>✓ Genuine products</p>
                        <p>✓ Easy returns</p>
                    </section>
                    @break

                @case (\App\Enums\HomeSectionType::Newsletter)
                    <section class="rounded-[var(--radius-lg)] border border-border p-6">
                        <h2 class="text-lg font-semibold text-ink">{{ $section->title }}</h2>
                        <p class="mt-1 text-sm text-ink-soft">Get updates on new arrivals and deals.</p>

                        @if (session('newsletter_status'))
                            <p class="mt-3 text-sm text-success">{{ session('newsletter_status') }}</p>
                        @endif

                        <form method="POST" action="{{ route('newsletter.store') }}" class="mt-4 flex max-w-md gap-2">
                            @csrf
                            <input type="email" name="email" required placeholder="you@example.com"
                                   class="flex-1 rounded-[var(--radius-pill)] border border-border px-4 py-2 text-sm focus:border-link focus:outline-none">
                            <button type="submit" class="rounded-[var(--radius-pill)] bg-ink px-4 py-2 text-sm font-semibold text-white hover:bg-ink-soft">
                                Subscribe
                            </button>
                        </form>
                        @error('email')
                            <p class="mt-2 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </section>
                    @break
            @endswitch
        @endforeach
    </div>
</x-layouts.app>
