<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
         * ⚠️ إلزامي خلف أي وكيل: نفق Cloudflare · موازن حِمل · CDN · استضافة
         * ببروكسي.
         *
         * الوكيل **ينهي TLS** ثم يُمرّر الطلب إلى التطبيق بـ`http` ويضع الأصل
         * في ترويسة `X-Forwarded-Proto`. وبلا هذه الثقة لا يعرف Laravel أن
         * الزائر على `https`، فيولّد **كل** الأصول بـ`http://` على صفحة
         * `https://` — والمتصفح يحجبها (mixed content) ⇒ يظهر الموقع بلا
         * تنسيقات ولا صور ولا جافاسكربت. عطل كامل بلا رسالة خطأ واحدة.
         *
         * والمقايضة: `at: '*'` يثق بأي وكيل، فيصير `X-Forwarded-For` قابلًا
         * للتزييف. أثرُه محدود هنا (تسجيل عنوان الزائر)، ولا يمسّ المصادقة
         * ولا الجلسات. وعلى استضافة بلا وكيل يمكن تضييقها بعنوان ثابت.
         */
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Laravel نفسه يكتشف تجاوز post_max_size في ValidatePostSize ويرمي
        // PostTooLargeException، لكن صفحته الافتراضية عامة ولا تذكر الحدّ
        // ولا سببه. والصفحة الصامتة كانت أصل الحيرة عند المالك: الرفع يفشل
        // بلا أن يقول له أحد إن السبب حجم الملف. فنشرحها هنا.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $limit = \App\Support\UploadLimits::human(\App\Support\UploadLimits::effectiveBytes());

            $message = 'حجم الملف المرفوع أكبر من الحد الذي يسمح به السيرفر ('.$limit.' للفيديو الواحد). '
                .'اضغط الفيديو قبل رفعه، أو ارفع مقطعًا أقصر.';

            // مسار رفع Livewire ينتظر JSON ليعرض الرسالة تحت الحقل
            if ($request->expectsJson() || $request->is('livewire/*')) {
                return response()->json(['message' => $message], 413);
            }

            return response()->view('errors.413', [], 413);
        });
    })->create();

// ═══ تخطيط الاستضافة: المشروع داخل جذر الويب ═══
//
// على الاستضافة المشتركة تقيّد `open_basedir` PHP داخل مجلد `htdocs` فقط، فلا
// يستطيع التطبيق الخروج إلى مستوى أعلى. لذلك نُنشر المشروع كاملاً داخل `htdocs`،
// فيصبح جذر الويب (htdocs) هو نفسه جذر التطبيق.
//
// بدون هذا السطر: `public_path()` يُرجع `htdocs/public` بينما روابط `asset()`
// وأصول Vite وقرص الملفات العامة تشير إلى الجذر ⇒ 404 لكل صورة وخط وجافاسكربت.
// عطل بصري صامت بلا رسالة خطأ واحدة.
$app->usePublicPath($app->basePath());
