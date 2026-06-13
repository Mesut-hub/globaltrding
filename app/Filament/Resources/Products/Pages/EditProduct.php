<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use App\Support\Filament\MultiLangKeyValue;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Normalize all Builder block data before Filament fills the form.
     *
     * WHY THIS IS REQUIRED:
     * Filament's KeyValueStateCast::get() calls array_key_first() without a
     * null-guard. In PHP 8.3+, array_key_first(null) throws a TypeError.
     * When a product was saved before multilingual fields were added, those
     * KeyValue fields are stored as null in the block data. The ->default([])
     * on the KeyValue component only applies when the key is *absent*; when it
     * is present-but-null the cast receives null and crashes.
     *
     * This method converts all null → [] for every known multilingual field
     * inside the four PDP Builder columns, at the PHP level, before the cast
     * ever runs.
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $builderColumns = [
            'pdp_overview_blocks',
            'pdp_properties_blocks',
            'pdp_documents_blocks',
            'pdp_sustainability_blocks',
        ];

        foreach ($builderColumns as $column) {
            if (! empty($data[$column]) && is_array($data[$column])) {
                $data[$column] = MultiLangKeyValue::normalizeBlocks($data[$column]);
            }
        }

        return $data;
    }
}