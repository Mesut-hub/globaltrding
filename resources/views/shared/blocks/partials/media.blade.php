{{-- resources/views/shared/blocks/partials/media.blade.php --}}
@php
  $mediaType = $mediaType ?? 'image';
  $wrapperStyle = $mediaStyle ?? '';
@endphp

<div class="w-full overflow-hidden rounded-lg" style="{{ $wrapperStyle }}">

    @if ($mediaType === 'video' && $vidUrl)
        <video
            src="{{ $vidUrl }}"
            @if (!empty($posterUrl)) poster="{{ $posterUrl }}" @endif
            autoplay muted loop playsinline
            class="w-full h-full object-cover block"
            style="{{ $wrapperStyle }}"
        ></video>

    @elseif ($mediaType === 'image' && $imgUrl)
        <img
            src="{{ $imgUrl }}"
            alt=""
            class="w-full h-full object-cover block"
            style="{{ $wrapperStyle }}"
        >

    @endif
</div>