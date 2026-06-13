<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Support\Filament\MultiLangKeyValue;
use Filament\Actions;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! empty($data['blocks']) && is_array($data['blocks'])) {
            $data['blocks'] = MultiLangKeyValue::normalizeBlocks($data['blocks'], [
                // extra fields used in page blocks beyond the product defaults
                'kicker', 'title', 'lead', 'text', 'heading',
                'cta_label', 'link_label', 'content',
                'row_title', 'excerpt', 'body_html', 'html',
                'panel_excerpt', 'panel_body',
                'links_title', 'label', 'hint',
            ]);
        }
        return $data;
    }
}
