@php
    $type = $block['type'] ?? null;
    $data = $block['data'] ?? [];
    $locale = app()->getLocale();
    $fallback = config('locales.default', 'en');
@endphp

@if ($type === 'richText')
    <div class="prose max-w-none">
        {!! $data['html'] ?? '' !!}
    </div>

@elseif ($type === 'image')
    @php
        $path = $data['path'] ?? null;
        $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
        $caption = $data['caption'] ?? '';
    @endphp

    @if ($url)
        <figure class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50">
            <img src="{{ $url }}" alt="" class="w-full h-auto object-cover" />
            @if ($caption)
                <figcaption class="px-4 py-3 text-sm text-slate-600">{{ $caption }}</figcaption>
            @endif
        </figure>
    @endif

@elseif ($type === 'video')
    @php
        $path = $data['path'] ?? null;
        $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
        $caption = $data['caption'] ?? '';
    @endphp

    @if ($url)
        <div class="rounded-2xl overflow-hidden border border-slate-200 bg-black">
            <video controls class="w-full h-auto">
                <source src="{{ $url }}" />
            </video>
        </div>
        @if ($caption)
            <div class="mt-2 text-sm text-slate-600">{{ $caption }}</div>
        @endif
    @endif

@elseif ($type === 'cta')
    @php
        $label = $data['label'] ?? 'Discover more';
        $url = $data['url'] ?? '#';
    @endphp

    <a href="{{ $url }}" class="inline-flex rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
        {{ $label }}
    </a>
@endif