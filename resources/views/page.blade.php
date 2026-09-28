@extends('layouts.nad')

@section('title', $page->title)

@section('content')
    <div class="container-x mt-6 max-w-4xl">
        <h1 class="mb-6 text-2xl font-extrabold">{{ $page->title }}</h1>

        {{-- زر الموقع على خرائط غوغل — صفحة «من نحن» فقط، والرابط يديره الأدمن من الإعدادات --}}
        @if ($page->slug === 'about' && setting('maps_url'))
            <div class="card mb-6 flex items-center justify-between gap-4 p-5 sm:p-6">
                <div>
                    <h2 class="text-base font-extrabold">موقعنا على خرائط غوغل</h2>
                    <p class="text-xs text-nad-mut">تجد المتجر بسهولة — الاتجاهات والمسافة وزيارات العملاء.</p>
                </div>
                <a href="{{ setting('maps_url') }}" target="_blank" rel="noopener"
                   class="nad-btn-brass flex shrink-0 items-center gap-2 !text-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 1 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    افتح الخريطة
                </a>
            </div>
        @endif

        {{-- النص العام إن وجد --}}
        @if ($page->content)
            <div class="card prose prose-sm max-w-none p-6 leading-8 sm:p-8 [&_h3]:font-extrabold [&_strong]:text-nad-ivory">
                {!! \Illuminate\Support\Str::markdown($page->content ?? '') !!}
            </div>
        @endif

        {{-- الأقسام الديناميكية — من نحن الغنية --}}
        @forelse ($page->sections as $section)
            <section class="card mt-6 overflow-hidden p-6 sm:p-8" wire:key="section-{{ $section->id }}">
                @if ($section->heading)
                    <h2 class="mb-4 flex items-center gap-3 text-xl font-extrabold">
                        <span class="h-6 w-1.5 rounded-full bg-nad-brass"></span>
                        {{ $section->heading }}
                    </h2>
                @endif

                @php $images = $section->mediaUrls($section->images); $videos = $section->mediaUrls($section->videos); @endphp

                {{-- تخطيط نص + صور جانبًا لجانب --}}
                @if ($section->layout === 'split' && $section->body && count($images))
                    <div class="grid items-center gap-6 md:grid-cols-2">
                        <div class="whitespace-pre-line text-[15px] leading-8 text-nad-mut">{{ $section->body }}</div>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach ($images as $img)
                                <img src="{{ $img }}" alt="{{ $section->heading }}" loading="lazy"
                                     class="aspect-square w-full rounded-xl object-cover shadow-sm transition hover:scale-[1.02]">
                            @endforeach
                        </div>
                    </div>
                @else
                    @if ($section->body)
                        <div class="whitespace-pre-line text-[15px] leading-8 text-nad-mut">{{ $section->body }}</div>
                    @endif

                    @if (count($images))
                        <div class="mt-5 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                            @foreach ($images as $img)
                                <img src="{{ $img }}" alt="{{ $section->heading }}" loading="lazy"
                                     class="aspect-square w-full rounded-xl object-cover shadow-sm transition hover:scale-[1.02]">
                            @endforeach
                        </div>
                    @endif

                    @if (count($videos))
                        <div class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($videos as $vid)
                                <video src="{{ $vid }}" controls preload="metadata"
                                       class="w-full max-h-80 rounded-xl bg-nad-bg shadow-sm"></video>
                            @endforeach
                        </div>
                    @endif
                @endif
            </section>
        @empty
        @endforelse
    </div>
@endsection
