<?php

namespace App\Support;

class SectionNormalizer
{
    public static function normalize(?string $section): string
    {
        $section = strtoupper(trim((string) ($section ?? '')));
        $section = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', $section);
        $section = preg_replace('/\s+/u', ' ', $section);

        if ($section === '') {
            return '';
        }

        if (preg_match('/^([A-Z]+(?:-[A-Z]+)?)[\s-]*(\d)[\s-]*(\d)(.*)$/', $section, $match)) {
            $prefix = $match[1];
            $year = $match[2];
            $block = $match[3];
            $suffix = trim($match[4] ?? '');

            if (preg_match('/^\(([^)]+)\)$/', $suffix, $suffixMatch)) {
                $suffix = ' (' . trim($suffixMatch[1]) . ')';
            } elseif ($suffix !== '') {
                $suffix = ' ' . $suffix;
            }

            return "{$prefix} {$year}-{$block}{$suffix}";
        }

        if (preg_match('/^([A-Z]+(?:-[A-Z]+)?)\s+(\d)\s*-\s*(\d)(.*)$/', $section, $match)) {
            $suffix = trim($match[4] ?? '');
            $suffix = $suffix !== '' ? ' ' . $suffix : '';

            return "{$match[1]} {$match[2]}-{$match[3]}{$suffix}";
        }

        return $section;
    }

    public static function key(?string $section): string
    {
        return preg_replace('/[^A-Z0-9]+/', '', self::normalize($section));
    }
}
