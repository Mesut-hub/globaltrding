<?php

namespace App\Filament\Resources\HomeSections\Pages;

use App\Filament\Resources\HomeSections\HomeSectionResource;
use App\Support\Filament\MultiLangKeyValue;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHomeSection extends EditRecord
{
    protected static string $resource = HomeSectionResource::class;

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
