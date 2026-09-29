<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }

    /**
     * تطبيع نوع المنتج عند التعديل: عدة صفوف ألوان/مقاسات مع النوع
     * «قطعة واحدة» يُرفع تلقائياً إلى «عدة ألوان أو مقاسات» — نفس منطق الإنشاء.
     * مع تطهير مدخلات الجوال (بايتات تالفة/أرقام عربية) قبل الحفظ.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $rows = $data['variants'] ?? [];

        if (is_array($rows) && count($rows) > 1 && ($data['product_type'] ?? null) === Product::TYPE_SINGLE) {
            $data['product_type'] = Product::TYPE_VARIANT;
        }

        $data['variants'] = static::sanitizeVariants($data['variants'] ?? []);

        return $data;
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
