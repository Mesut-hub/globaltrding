<?php

namespace App\Support\Filament;

final class MultiLangKeyValue
{
    /**
     * Normalize any stored value (null|string|array) into an associative array suitable for Filament KeyValue.
     */
    public static function normalize($value): array
    {
        if ($value === null) return [];

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
                    if ($k !== '') $assoc[$k] = $v;
                }
            }
            return $assoc;
        }

        return is_array($value) ? $value : [];
    }

    /**
     * Ensure we always store an associative array (never null).
     */
    public static function dehydrate($state): array
    {
        $arr = self::normalize($state);

        // Remove empty keys and normalize values to strings
        $out = [];
        foreach ($arr as $k => $v) {
            $k = trim((string) $k);
            if ($k === '') continue;
            $out[$k] = (string) $v;
        }

        return $out;
    }
}