{{-- 5) LATEST NEWS SLIDER (keep your existing one) --}}
<section class="border-t border-slate-200 mt-4">
        <div class="mx-auto max-w-7xl px-4 py-10">
            <div class="flex items-end justify-between gap-4">
                <h2 class="text-2xl font-semibold tracking-tight">
                    Latest News
                </h2>

                <a href="/{{ $locale }}/news"
                   class="text-sm text-slate-600 hover:text-slate-900 hover:underline">
                    View all →
                </a>
            </div>

            @if (!empty($news) && count($news))
                <div class="mt-6 overflow-x-auto">
                    <div class="flex gap-4 snap-x snap-mandatory">
                        @foreach ($news as $post)
                            @php
                                $fallback = config('locales.default', 'en');
                                $title = data_get($post->title, $locale) ?: data_get($post->title, $fallback) ?: '';
                                $excerpt = data_get($post->excerpt, $locale) ?: data_get($post->excerpt, $fallback) ?: '';
                            @endphp

                            <a href="/{{ $locale }}/news/{{ $post->slug }}"
                               class="snap-start shrink-0 w-[85%] sm:w-[60%] lg:w-[38%] rounded-xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-sm transition bg-white">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="text-xs text-slate-500">
                                        @if ($post->published_at)
                                            {{ $post->published_at->format('Y-m-d') }}
                                        @endif
                                    </div>

                                    @if ($post->is_featured)
                                        <span class="text-xs rounded-full bg-amber-100 text-amber-900 px-2 py-0.5">
                                            Featured
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-3 text-lg font-semibold leading-snug">
                                    {{ $title }}
                                </div>

                                @if ($excerpt)
                                    <div class="mt-2 text-sm text-slate-600">
                                        {{ $excerpt }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>

                <p class="mt-3 text-xs text-slate-500">
                    Tip: scroll horizontally to see more news.
                </p>
            @else
                <p class="mt-6 text-slate-600">
                    No news published yet.
                </p>
            @endif
        </div>
    </section>