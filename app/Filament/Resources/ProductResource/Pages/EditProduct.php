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
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $rows = $data['variants'] ?? [];

        if (is_array($rows) && count($rows) > 1 && ($data['product_type'] ?? null) === Product::TYPE_SINGLE) {
            $data['product_type'] = Product::TYPE_VARIANT;
        }

        return $data;
    }
}
