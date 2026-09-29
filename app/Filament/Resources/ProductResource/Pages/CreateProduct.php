<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * تطبيع نوع المنتج: إن أُضيفت عدة صفوف ألوان/مقاسات مع بقاء النوع
     * «قطعة واحدة» (سهل الوقوع فيه على الجوال)، نرفعه تلقائياً إلى
     * «عدة ألوان أو مقاسات» بدل رفض الحفظ بخطأ maxItems محيّر.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $rows = $data['variants'] ?? [];

        if (is_array($rows) && count($rows) > 1 && ($data['product_type'] ?? null) === Product::TYPE_SINGLE) {
            $data['product_type'] = Product::TYPE_VARIANT;
        }

        $data['variants'] = static::sanitizeVariants($data['variants'] ?? []);

        return $data;
    }

    /**
     * تطهير صفوف المتغيرات من مدخلات الجوال:
     *  - mb_scrub ينقّح البايتات التالفة التي يرفضها MariaDB بخطأ 1366
     *  - الأرقام العربية-الهندية (٠-٩ / ۰-۹) تُحوَّل لإنجليزية في SKU والمقاس
     */
    public static function sanitizeVariants(array $rows): array
    {
        return array_map(function ($row) {
            if (! is_array($row)) {
                return $row;
            }

            foreach (['sku', 'size', 'color'] as $field) {
                if (isset($row[$field]) && is_string($row[$field])) {
                    $row[$field] = static::normalizeText($row[$field]);
                }
            }

            return $row;
        }, $rows);
    }

    /** تنقية نص: إزالة البايتات غير UTF-8 الصالحة + توحيد الأرقام العربية-الهندية */
    public static function normalizeText(string $value): string
    {
        if (function_exists('mb_scrub')) {
            $value = mb_scrub($value, 'UTF-8');
        }

        $map = [
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
        ];

        return trim(strtr($value, $map));
    }
}
