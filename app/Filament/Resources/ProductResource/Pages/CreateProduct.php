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

        return $data;
    }
}
