<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

/**
 * Shared helper used by ProductForm, PageBlockBuilder and HomeSectionBlockBuilder.
 * Generates locale-tabbed TextInput / Textarea components for any translatable
 * field inside a Block or Repeater schema.
 */
trait HasBlockLocaleTabs
{
    /**
     * @param  string  $tabsId   Unique ID for the Tabs component.
     * @param  array   $fields   Field definitions:
     *   ['name' => 'title',    'label' => 'Title',    'type' => 'text']
     *   ['name' => 'body_html','label' => 'Body HTML','type' => 'html', 'rows' => 8]
     *   ['name' => 'excerpt',  'label' => 'Excerpt',  'type' => 'textarea', 'rows' => 3]
     */
    protected static function blockLocaleTabs(string $tabsId, array $fields): Tabs
    {
        $locales = config('locales.supported', ['en']);

        return Tabs::make($tabsId)
            ->columnSpanFull()
            ->tabs(
                collect($locales)->map(function (string $locale) use ($fields): Tab {
                    $lbl    = strtoupper($locale);
                    $schema = collect($fields)->map(function (array $field) use ($locale, $lbl) {
                        $name  = $field['name'];
                        $label = ($field['label'] ?? ucwords(str_replace('_', ' ', $name))) . " ({$lbl})";
                        $rows  = (int) ($field['rows'] ?? 4);

                        return match ($field['type'] ?? 'text') {
                            'html', 'textarea' => Textarea::make("{$name}.{$locale}")
                                ->label($label)->rows($rows),
                            default => TextInput::make("{$name}.{$locale}")
                                ->label($label),
                        };
                    })->all();

                    return Tab::make($lbl)->schema($schema);
                })->values()->all()
            );
    }
}