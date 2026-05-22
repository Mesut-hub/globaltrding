<?php

namespace App\Support\Filament;

final class MultiLangKeyValue
{
    private const BLOCK_MULTILANG_FIELDS = [
        'heading',
        'title',
        'excerpt',
        'body_html',
        'left_title',
        'left_html',
        'right_title',
        'right_html',
        'cta_label',
    ];
    /**
     * Normalize any stored value (null|string|array) into an associative array suitable for Filament KeyValue.
     */
    public static function normalize(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        // If it's a JSON string, decode it
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        // If Filament gives list format like: [ ['key'=>'en','value'=>'..'], ... ]
        if (is_array($value) && array_is_list($value)) {
            $assoc = [];
            foreach ($value as $row) {
                if (is_array($row) && isset($row['key'])) {
                    $k = trim((string) $row['key']);
                    $v = (string) ($row['value'] ?? '');
                    if ($k !== '') {
                        $assoc[$k] = $v;
                    }
                }
            }
            return $assoc;
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Ensure we always store an associative array (never null).
     */
    public static function dehydrate(mixed $state): array
    {
        $arr = self::normalize($state);

        // Remove empty keys and normalize values to strings
        $out = [];
        foreach ($arr as $k => $v) {
            $k = trim((string) $k);
            if ($k === '') {
                $out[$k] = (string) $v;
            }
        }

        return $out;
    }

    public static function normalizeBlocks(array $blocks, array $extraFields = []): array
    {
        $fields = array_unique(array_merge(self::BLOCK_MULTILANG_FIELDS, $extraFields));

        return array_map(
            fn (array $block): array => self::normalizeBlockItem($block, $fields),
            $blocks
        );
    }

    // ─────────────────────────────────────────────────────────────────────────

    private static function normalizeBlockItem(array $block, array $fields): array
    {
        $data = is_array($block['data'] ?? null) ? $block['data'] : [];

        // Normalize top-level KeyValue fields
        foreach ($fields as $field) {
            if (array_key_exists($field, $data) && ! is_array($data[$field])) {
                $data[$field] = self::normalize($data[$field]);
            }
        }

        // Normalize nested repeaters (items → pdcards cards, rows → docDropdown rows)
        foreach (['items', 'rows'] as $repeaterKey) {
            if (! empty($data[$repeaterKey]) && is_array($data[$repeaterKey])) {
                $data[$repeaterKey] = array_map(function (array $item) use ($fields): array {
                    foreach ($fields as $field) {
                        if (array_key_exists($field, $item) && ! is_array($item[$field])) {
                            $item[$field] = self::normalize($item[$field]);
                        }
                    }
                    return $item;
                }, $data[$repeaterKey]);
            }
        }

        $block['data'] = $data;
        return $block;
    }
}