{{-- التخطيط الجديد — هوية نداف المعتمدة (nad.html): فحم ليلي + نحاسي + El Messiri/Almarai --}}
@php
    // الوضع الداكن هو الافتراضي. تُقرأ الرغبة من كوكي ليُرسم الوضع الصحيح
    // من أول إطار — بلا وميض فاتح ثم انقلاب إلى داكن.
    $nadTheme = in_array(request()->cookie('nad_theme'), ['dark', 'light'], true)
        ? request()->cookie('nad_theme')
        : 'dark';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}"
      data-theme="{{ $nadTheme }}">
<head>
    <meta charset="utf-8">
    <script>
        // احتياط للصفحات المخزّنة كاملة: صحّح الوضع قبل رسم أي شيء
        (function () {
            try {
                var m = document.cookie.match(/(?:^|;\s*)nad_theme=(dark|light)/);
                if (m) document.documentElement.dataset.theme = m[1];
            } catch (e) {}
        })();
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', app()->getLocale() === 'en' ? setting('store_name_en', 'NADAF') : setting('store_name_ar', 'نداف'))</title>
    <meta name="description" content="@yield('description', __('footer.tagline'))">
    {{-- وسوم المشاركة الاجتماعية: كل صفحة تمرر صورتها، والافتراضي أيقونة المتجر PNG (واتساب لا يدعم SVG) --}}
    <meta property="og:site_name" content="{{ app()->getLocale() === 'en' ? setting('store_name_en', 'NADAF') : setting('store_name_ar', 'نداف') }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', app()->getLocale() === 'en' ? setting('store_name_en', 'NADAF') : setting('store_name_ar', 'نداف'))">
    <meta property="og:description" content="@yield('description', __('footer.tagline'))">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', url('icons/icon-512.png'))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#0F1319">
    {{-- منع التمرير الأفقي على الجوال — السلايدر/الفيديو أثناء التحميل يدفع الصفحة عرضاً --}}
    <style>html, body { overflow-x: clip; }</style>
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- خطوط نداف: يقرأها Vite من fonts-manifest.json (يولّدها laravel-vite-plugin/fonts)
         — لا اسم ملف مبنّي مكتوب يدويًا، فلا ينكسر عند تغيير مجموعة الأوزان --}}
    {{ \Illuminate\Support\Facades\Vite::fonts() }}
    @livewireStyles
</head>
<body class="nad-page flex min-h-screen flex-col antialiased">
<div class="nad-grain" aria-hidden="true"></div>

    @include('partials.flash')

    {{-- ═══ الترويسة ═══
         x-data على الترويسة نفسها — لأن زر الهمبرغر ولوحة قائمة الجوال
         **شقيقات** لا يربطهما نطاق Alpine لو كان على الزر وحده (هذا كان
         سبب «القائمة لا تعمل» على الجوال). --}}
    <header x-data="{ open: false }" class="sticky top-0 z-50" style="background:rgba(15,19,25,.87);backdrop-filter:blur(12px);border-bottom:1px solid rgba(210,162,78,.16)">
        <div class="container-x flex h-[58px] items-center gap-3">
            <button @click="open = !open"
                    class="rounded-lg p-2 text-nad-ivory/80 hover:text-nad-champ lg:hidden" aria-label="{{ __('nav.menu') }}">
                <x-shop-icon name="menu" class="h-6 w-6" />
            </button>

            @php
                // شعار المالك المرفوع من «الإعدادات ← الهوية البصرية» يحلّ محلّ
                // النصّ «نداف ◆ أناقة تليق بك — ...». وحقل الإعدادات يَعِد صراحةً
                // بأنه «يُستخدم في الهيدر والفوتر بدل الشعار الافتراضي» — والوعد
                // كان غير منفَّذ في المتجر، فرفع المالك شعاره ولم يره.
                // والشعار المرفوع يحمل الدرع والاسم معًا، فيحلّ محلّ الصورة
                // والنصّ معًا لا محلّ الصورة وحدها.
                $nadLogoPath = setting('logo_path');
                $nadLogo = $nadLogoPath
                    ? \Illuminate\Support\Facades\Storage::disk('public')->url($nadLogoPath)
                    : null;
            @endphp

            <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2 whitespace-nowrap">
                @if ($nadLogo)
                    <img src="{{ $nadLogo }}" alt="{{ app()->getLocale() === 'en' ? setting('store_name_en', 'NADAF') : setting('store_name_ar', 'نداف') }}"
                         class="h-11 w-auto max-w-[200px] object-contain">
                @else
                    <span class="font-display text-2xl font-bold text-nad-ivory">{{ app()->getLocale() === 'en' ? setting('store_name_en', 'NADAF') : setting('store_name_ar', 'نداف') }}</span>
                    <span class="text-[9px] text-nad-brass">◆</span>
                    <span class="hidden text-xs text-nad-mut sm:block">{{ __('footer.tagline') }}</span>
                @endif
            </a>

            {{-- ═══ السلة — مكان حقل البحث سابقاً: بارزة في وسط الترويسة ═══
                 البحث صار أيقونة تنفتح بالنقر (يمين المجموعة) بطلب المالك. --}}
            <livewire:header-cart />

            <div class="ms-auto flex items-center gap-2">
                <button type="button"
                        class="nad-theme"
                        onclick="nadSetTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark')"
                        aria-label="تبديل الوضع الداكن والفاتح"
                        title="الوضع الداكن / الفاتح">
                    <svg class="i-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4.2"/><path d="M12 2.6v2.2M12 19.2v2.2M2.6 12h2.2M19.2 12h2.2M5.3 5.3l1.6 1.6M17.1 17.1l1.6 1.6M18.7 5.3l-1.6 1.6M6.9 17.1l-1.6 1.6"/></svg>
                    <svg class="i-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M20 14.2A8.2 8.2 0 0 1 9.8 4 8.4 8.4 0 1 0 20 14.2z"/></svg>
                </button>

                {{-- مبدّل العملة — مخفي بطلب المالك (كان بجانب حقل البحث) --}}
                <div class="hidden">
                    <a href="{{ route('currency.switch', 'usd') }}"
                       class="px-3 py-1.5 text-[10.5px] font-bold tracking-widest transition {{ session('currency', 'usd') === 'usd' ? 'bg-nad-bg text-nad-champ' : 'text-nad-mut' }}">USD $</a>
                    <a href="{{ route('currency.switch', 'syp') }}"
                       class="px-3 py-1.5 text-[10.5px] font-bold tracking-widest transition {{ session('currency') === 'syp' ? 'bg-nad-bg text-nad-champ' : 'text-nad-mut' }}">SYP ل.س</a>
                </div>

                {{-- أيقونة البحث — لكل المقاسات: تفتح حقل البحث عند النقر --}}
                <div x-data="{ searchOpen: false }" class="relative">
                    <button type="button"
                            class="rounded-lg p-2 text-nad-ivory/80 hover:text-nad-champ"
                            aria-label="{{ __('nav.search_placeholder') }}"
                            @click="searchOpen = !searchOpen">
                        <x-shop-icon name="search" class="h-5 w-5" />
                    </button>
                    <div x-show="searchOpen" x-cloak x-transition
                         class="absolute end-0 top-full z-[70] mt-3 w-[300px] max-w-[86vw] rounded-xl border border-nad-line2 bg-nad-surface p-2 shadow-2xl">
                        <form action="{{ route('search') }}" method="GET" class="nad-search !flex">
                            <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('nav.search_placeholder') }}" autofocus>
                            <x-shop-icon name="search" class="pointer-events-none h-4 w-4 text-nad-dim" />
                        </form>
                    </div>
                </div>

                {{-- تسجيل الدخول/إنشاء حساب مخفيان من واجهة المتجر بطلب المالك —
                    الدخول للأدمن عبر /admin حصراً --}}
            </div>
        </div>

        {{-- ═══ شريط التنقّل — للشاشات الكبيرة فقط؛ الموبايل يستخدم قائمة همبرغر ═══ --}}

        <nav class="hidden border-t border-nad-line2/70 lg:block" aria-label="{{ __('nav.menu') }}">
            <div class="container-x nad-navrow">
                @php
                    // أيقونة كل صفحة بحسب معناها لا بحسب ترتيبها
                    $navPageIcon = fn (string $slug) => match (true) {
                        str_contains($slug, 'exchange'), str_contains($slug, 'return') => 'refresh',
                        str_contains($slug, 'faq'), str_contains($slug, 'question') => 'question',
                        default => 'info',
                    };
                @endphp

                <a href="{{ route('home') }}"
                   class="nad-navi {{ request()->routeIs('home') ? 'is-on' : '' }}">
                    <x-shop-icon name="home" class="h-4 w-4" />
                    <span>{{ __('nav.home') }}</span>
                </a>

                @if ($headerCategories->isNotEmpty())
                    <div class="relative" x-data="{ open: false }" @mouseleave="open = false">
                        <button type="button" class="nad-navi" @click="open = !open" @mouseenter="open = true">
                            <x-shop-icon name="grid" class="h-4 w-4" />
                            <span>{{ __('nav.categories') }}</span>
                            <x-shop-icon name="chevron" class="h-3 w-3 rotate-90" />
                        </button>
                        <div x-show="open" x-cloak x-transition.opacity
                             class="absolute start-0 top-full z-[60] mt-1 min-w-[210px] overflow-hidden rounded-xl border border-nad-line2 bg-nad-surface py-1.5 shadow-2xl">
                            @foreach ($headerCategories as $cat)
                                <a href="{{ route('category.show', $cat->slug) }}"
                                   class="flex items-center justify-between gap-3 px-4 py-2 text-[13px] font-bold text-nad-ivory transition hover:bg-nad-surface2 hover:text-nad-champ">
                                    <span>{{ $cat->name }}</span>
                                    <small class="text-[11px] text-nad-dim">{{ $cat->products_count ?? '' }}</small>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @foreach ($footerPages as $page)
                    <a href="{{ route('page.show', $page->slug) }}"
                       class="nad-navi {{ request()->routeIs('page.show') && request()->route('slug') === $page->slug ? 'is-on' : '' }}">
                        <x-shop-icon name="{{ $navPageIcon($page->slug) }}" class="h-4 w-4" />
                        <span>{{ $page->title }}</span>
                    </a>
                @endforeach

                <a href="{{ route('contact') }}"
                   class="nad-navi {{ request()->routeIs('contact') ? 'is-on' : '' }}">
                    <x-shop-icon name="phone" class="h-4 w-4" />
                    <span>{{ __('nav.contact') }}</span>
                </a>

                {{-- تثبيت التطبيق — يظهر فقط عندما يتاح التثبيت الفعلي (كروم/إيدج) --}}
                <button type="button" class="nad-install hidden nad-navi !text-nad-champ">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4" aria-hidden="true"><path d="M12 3v11m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    <span>تثبيت التطبيق</span>
                </button>
            </div>
        </nav>

        {{-- قائمة الجوال بنمط nad --}}
        <div x-show="open" x-cloak x-transition class="border-t border-nad-line bg-nad-bg lg:hidden">
            <form action="{{ route('search') }}" method="GET" class="nad-search m-3 !flex">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="{{ __('nav.search_placeholder') }}">
                <x-shop-icon name="search" class="h-4 w-4 text-nad-dim" />
            </form>
            <nav class="flex flex-col pb-3">
                <a href="{{ route('home') }}" class="px-4 py-2.5 text-sm font-bold text-nad-ivory/90 hover:bg-nad-surface">{{ __('nav.home') }}</a>
                @foreach ($headerCategories as $cat)
                    <a href="{{ route('category.show', $cat->slug) }}" class="px-4 py-2.5 text-sm font-bold text-nad-ivory/90 hover:bg-nad-surface">{{ $cat->name }}</a>
                @endforeach
                <a href="{{ route('contact') }}" class="px-4 py-2.5 text-sm font-bold text-nad-ivory/90 hover:bg-nad-surface">{{ __('nav.contact') }}</a>
                @foreach ($footerPages as $page)
                    <a href="{{ route('page.show', $page->slug) }}" class="px-4 py-2.5 text-sm font-bold text-nad-mut hover:bg-nad-surface">{{ $page->title }}</a>
                @endforeach
                @auth
                    <div class="my-2 border-t border-nad-line2"></div>
                    <a href="{{ route('account.profile') }}" class="px-4 py-2.5 text-sm font-bold text-nad-ivory/90 hover:bg-nad-surface">{{ __('account.profile') }}</a>
                    <a href="{{ route('account.orders') }}" class="px-4 py-2.5 text-sm font-bold text-nad-ivory/90 hover:bg-nad-surface">{{ __('account.my_orders') }}</a>
                    @if (auth()->user()->isAdmin())
                        <a href="{{ url('/admin') }}" class="px-4 py-2.5 text-sm font-bold text-nad-brass hover:bg-nad-surface">{{ __('nav.admin_panel') }}</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-full px-4 py-2.5 text-start text-sm font-bold text-red-300 hover:bg-nad-surface">{{ __('nav.logout') }}</button>
                    </form>
                @endauth

                {{-- تثبيت التطبيق — يظهر فقط عندما يتاح التثبيت الفعلي (كروم/أندرويد) --}}
                <button type="button" class="nad-install hidden mt-2 flex w-full items-center gap-3 border-t border-nad-line2 px-4 pt-3 pb-4 text-sm font-extrabold text-nad-champ">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5" aria-hidden="true"><path d="M12 3v11m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                    تثبيت التطبيق
                </button>
            </nav>
        </div>
    </header>

    <livewire:checkout-modal />

    {{-- النافذة الإلزامية لبيانات العميل — تخدم كل أزرار الطلب والواتساب --}}
    <livewire:customer-details-modal />

    <main class="flex-1">
        @yield('content')
    </main>

    @include('partials.footer-nad')

    {{-- الشات العائم بنمط nad --}}
    <livewire:floating-chat />

    {{-- ═══ محرّك تثبيت PWA ═══
         لا أزرار ثابتة — ثلاثة مواضع تظهر معاً **فقط عندما يتاح التثبيت الفعلي**
         (حدث beforeinstallprompt في كروم/إيدج — حاسوب وأندرويد):
         1) الهيدر بجانب «اتصل بنا»  2) قائمة الجوال المنسدلة  3) التذييل فوق المطوّر.
         النقر على أي منها = نافذة تثبيت المتصفح الحقيقية. آيفون لا يسمح إطلاقاً
         بتثبيت برمجي فلا يظهر الزر هناك أصلاً. --}}
    <script>
        (function () {
            var deferred = null;

            var show = function () {
                document.querySelectorAll('.nad-install').forEach(function (el) {
                    el.classList.remove('hidden');
                });
            };
            var hideAll = function () {
                document.querySelectorAll('.nad-install').forEach(function (el) {
                    el.classList.add('hidden');
                });
            };

            window.addEventListener('beforeinstallprompt', function (e) {
                e.preventDefault();
                deferred = e;
                show();
            });
            window.addEventListener('appinstalled', hideAll);

            document.addEventListener('click', function (e) {
                var b = e.target && e.target.closest && e.target.closest('.nad-install');
                if (!b || !deferred) return;

                deferred.prompt();
                deferred.userChoice.finally(function () {
                    deferred = null;
                    hideAll();
                });
            });
        })();
    </script>

    {{-- تنبيه انقطاع الإنترنت --}}
    <div x-data="{ online: navigator.onLine }"
         @online.window="online = true"
         @offline.window="online = false"
         x-show="!online" x-cloak x-transition.opacity
         class="fixed inset-x-0 top-0 z-[100] bg-nad-ox py-2.5 text-center text-sm font-extrabold text-white shadow-lg">
        ⚠️ {{ __('common.offline') }}
    </div>

    @livewireScripts
    
</body>
</html>
