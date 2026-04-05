@php
    $blocks = $blocks ?? [];
@endphp

@foreach ($blocks as $block)
    @php $type = $block['type'] ?? null; $data = $block['data'] ?? []; @endphp

    @if ($type === 'richText')
        <section class="prose prose-slate max-w-none">
            @if (!empty($data['heading']))
                <h2>{{ $data['heading'] }}</h2>
            @endif
            {!! $data['html'] ?? '' !!}
        </section>

    @elseif ($type === 'image')
        @php
            $path = $data['path'] ?? null;
            $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
        @endphp

        @if ($url)
            <figure class="my-8">
                <img src="{{ $url }}" alt="" class="w-full rounded-xl border border-slate-200">
                @if (!empty($data['caption']))
                    <figcaption class="mt-2 text-sm text-slate-600">{{ $data['caption'] }}</figcaption>
                @endif
            </figure>
        @endif

    @elseif ($type === 'video')
        @php
            $path = $data['path'] ?? null;
            $url = $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : null;
        @endphp

        @if ($url)
            <figure class="my-8">
                @if (str_ends_with($url, '.mp4') || str_ends_with($url, '.webm'))
                    <video controls class="w-full rounded-xl border border-slate-200">
                        <source src="{{ $url }}">
                    </video>
                @else
                    <img src="{{ $url }}" alt="" class="w-full rounded-xl border border-slate-200">
                @endif

                @if (!empty($data['caption']))
                    <figcaption class="mt-2 text-sm text-slate-600">{{ $data['caption'] }}</figcaption>
                @endif
            </figure>
        @endif

    @elseif ($type === 'cta')
        @if (!empty($data['url']) && !empty($data['label']))
            <div class="my-8">
                <a href="{{ $data['url'] }}"
                   class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-white hover:bg-slate-800">
                    {{ $data['label'] }}
                </a>
            </div>
        @endif
    @endif
@endforeach