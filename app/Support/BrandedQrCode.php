<?php

namespace App\Support;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class BrandedQrCode
{
    public static function png(string $url, int $scale = 6): string
    {
        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_H,
            'scale' => $scale,
            'imageBase64' => false,
        ]);

        $png = (new QRCode($options))->render($url);
        $png = self::recolorDarkModules($png);

        return self::applyLogoToPng($png);
    }

    public static function pngWithLabel(string $url, array $labelLines, int $scale = 8): string
    {
        $png = self::png($url, $scale);

        if (!function_exists('imagecreatefromstring')) {
            return $png;
        }

        $qr = @imagecreatefromstring($png);
        if (!$qr) {
            return $png;
        }

        $qrWidth = imagesx($qr);
        $qrHeight = imagesy($qr);
        $padding = 44;
        $lineGap = 12;
        $titleFontSize = 22;
        $metaFontSize = 15;
        $fallbackTitleFont = 5;
        $fallbackMetaFont = 4;
        $fontPath = self::fontPath(false);
        $boldFontPath = self::fontPath(true) ?: $fontPath;
        $useTrueType = function_exists('imagettfbbox') && function_exists('imagettftext') && $fontPath;
        $maxTextWidth = $qrWidth + ($padding * 2);

        $lines = [];
        foreach ($labelLines as $index => $line) {
            $text = trim((string) $line);
            if ($text === '') {
                continue;
            }

            $isTitle = $index === 0;
            $activeFontPath = $isTitle ? $boldFontPath : $fontPath;
            $activeFontSize = $isTitle ? $titleFontSize : $metaFontSize;
            $fallbackFont = $isTitle ? $fallbackTitleFont : $fallbackMetaFont;
            $wrappedLines = self::wrapText(
                $text,
                $maxTextWidth - ($padding * 2),
                $useTrueType ? $activeFontPath : null,
                $activeFontSize,
                $fallbackFont
            );

            foreach ($wrappedLines as $wrappedLine) {
                $lines[] = [
                    'text' => $wrappedLine,
                    'is_title' => $isTitle,
                    'font_path' => $useTrueType ? $activeFontPath : null,
                    'font_size' => $activeFontSize,
                    'fallback_font' => $fallbackFont,
                ];
            }
        }

        if (empty($lines)) {
            imagedestroy($qr);
            return $png;
        }

        $textHeight = 0;
        foreach ($lines as $line) {
            $textHeight += self::textHeight($line['text'], $line['font_path'], $line['font_size'], $line['fallback_font']);
        }
        $textHeight += max(0, count($lines) - 1) * $lineGap;

        $canvasWidth = $qrWidth + ($padding * 2);
        $canvasHeight = $qrHeight + $textHeight + ($padding * 3);
        $canvas = imagecreatetruecolor($canvasWidth, $canvasHeight);

        if (!$canvas) {
            imagedestroy($qr);
            return $png;
        }

        $white = imagecolorallocate($canvas, 255, 255, 255);
        $purple = imagecolorallocate($canvas, 92, 41, 124);
        $dark = imagecolorallocate($canvas, 31, 41, 55);
        $muted = imagecolorallocate($canvas, 100, 116, 139);

        imagefill($canvas, 0, 0, $white);

        $y = $padding;
        foreach ($lines as $index => $line) {
            $color = $line['is_title'] ? $purple : ($index === 1 ? $dark : $muted);
            self::drawCenteredText(
                $canvas,
                $line['text'],
                $canvasWidth,
                $y,
                $color,
                $line['font_path'],
                $line['font_size'],
                $line['fallback_font']
            );
            $y += self::textHeight($line['text'], $line['font_path'], $line['font_size'], $line['fallback_font']) + $lineGap;
        }

        imagecopy($canvas, $qr, $padding, $y + $padding, 0, 0, $qrWidth, $qrHeight);

        ob_start();
        imagepng($canvas);
        $labeledPng = (string) ob_get_clean();

        imagedestroy($canvas);
        imagedestroy($qr);

        return $labeledPng !== '' ? $labeledPng : $png;
    }

    public static function svgDataUri(string $url, int $scale = 8): string
    {
        $options = new QROptions([
            'version' => 10,
            'outputType' => QRCode::OUTPUT_MARKUP_SVG,
            'eccLevel' => QRCode::ECC_H,
            'scale' => $scale,
            'imageBase64' => false,
            'drawLightModules' => true,
        ]);

        $qrCodeSvg = (new QRCode($options))->render($url);

        return 'data:image/svg+xml;base64,' . base64_encode($qrCodeSvg);
    }

    public static function pngDataUri(string $url, int $scale = 8): string
    {
        return 'data:image/png;base64,' . base64_encode(self::png($url, $scale));
    }

    public static function logoDataUri(): ?string
    {
        $path = self::logoPath();
        if (!$path) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($path));
    }

    private static function applyLogoToPng(string $png): string
    {
        $logoPath = self::logoPath();
        if (!$logoPath || !function_exists('imagecreatefromstring')) {
            return $png;
        }

        $qr = @imagecreatefromstring($png);
        $logo = @imagecreatefromstring((string) file_get_contents($logoPath));

        if (!$qr || !$logo) {
            if ($qr) {
                imagedestroy($qr);
            }
            if ($logo) {
                imagedestroy($logo);
            }

            return $png;
        }

        imagealphablending($qr, true);
        imagesavealpha($qr, true);

        $qrWidth = imagesx($qr);
        $qrHeight = imagesy($qr);
        $logoWidth = imagesx($logo);
        $logoHeight = imagesy($logo);
        $targetLogoSize = (int) round(min($qrWidth, $qrHeight) * 0.22);

        $scale = min($targetLogoSize / max($logoWidth, 1), $targetLogoSize / max($logoHeight, 1));
        $targetWidth = max(1, (int) round($logoWidth * $scale));
        $targetHeight = max(1, (int) round($logoHeight * $scale));
        $badgeSize = max($targetWidth, $targetHeight) + max(16, (int) round($targetLogoSize * 0.18));
        $badgeX = (int) round(($qrWidth - $badgeSize) / 2);
        $badgeY = (int) round(($qrHeight - $badgeSize) / 2);
        $logoX = (int) round(($qrWidth - $targetWidth) / 2);
        $logoY = (int) round(($qrHeight - $targetHeight) / 2);

        $white = imagecolorallocate($qr, 255, 255, 255);
        imagefilledellipse(
            $qr,
            (int) round($badgeX + ($badgeSize / 2)),
            (int) round($badgeY + ($badgeSize / 2)),
            $badgeSize,
            $badgeSize,
            $white
        );

        imagecopyresampled(
            $qr,
            $logo,
            $logoX,
            $logoY,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $logoWidth,
            $logoHeight
        );

        ob_start();
        imagepng($qr);
        $brandedPng = (string) ob_get_clean();

        imagedestroy($qr);
        imagedestroy($logo);

        return $brandedPng !== '' ? $brandedPng : $png;
    }

    private static function recolorDarkModules(string $png): string
    {
        if (!function_exists('imagecreatefromstring')) {
            return $png;
        }

        $image = @imagecreatefromstring($png);
        if (!$image) {
            return $png;
        }

        imagealphablending($image, true);
        imagesavealpha($image, true);

        $width = imagesx($image);
        $height = imagesy($image);
        $purple = imagecolorallocate($image, 58, 0, 80);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $isDark = ($rgba['red'] ?? 255) < 80
                    && ($rgba['green'] ?? 255) < 80
                    && ($rgba['blue'] ?? 255) < 80
                    && ($rgba['alpha'] ?? 0) < 120;

                if ($isDark) {
                    imagesetpixel($image, $x, $y, $purple);
                }
            }
        }

        ob_start();
        imagepng($image);
        $recoloredPng = (string) ob_get_clean();

        imagedestroy($image);

        return $recoloredPng !== '' ? $recoloredPng : $png;
    }

    private static function wrapText(string $text, int $maxWidth, ?string $fontPath, int $fontSize, int $fallbackFont): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = trim($line . ' ' . $word);
            if ($line !== '' && self::textWidth($candidate, $fontPath, $fontSize, $fallbackFont) > $maxWidth) {
                $lines[] = $line;
                $line = $word;
                continue;
            }
            $line = $candidate;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    private static function drawCenteredText($image, string $text, int $canvasWidth, int $y, int $color, ?string $fontPath, int $fontSize, int $fallbackFont): void
    {
        $width = self::textWidth($text, $fontPath, $fontSize, $fallbackFont);
        $x = (int) round(($canvasWidth - $width) / 2);

        if ($fontPath && function_exists('imagettftext')) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $text);
            $baseline = $y + abs($box[5] ?? 0);
            imagettftext($image, $fontSize, 0, max(0, $x), $baseline, $color, $fontPath, $text);
            return;
        }

        imagestring($image, $fallbackFont, max(0, $x), $y, $text, $color);
    }

    private static function textWidth(string $text, ?string $fontPath, int $fontSize, int $fallbackFont): int
    {
        if ($fontPath && function_exists('imagettfbbox')) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $text);
            return abs(($box[2] ?? 0) - ($box[0] ?? 0));
        }

        return imagefontwidth($fallbackFont) * strlen($text);
    }

    private static function textHeight(string $text, ?string $fontPath, int $fontSize, int $fallbackFont): int
    {
        if ($fontPath && function_exists('imagettfbbox')) {
            $box = imagettfbbox($fontSize, 0, $fontPath, $text);
            return abs(($box[7] ?? 0) - ($box[1] ?? 0));
        }

        return imagefontheight($fallbackFont);
    }

    private static function fontPath(bool $bold = false): ?string
    {
        $candidates = $bold
            ? [
                'C:/Windows/Fonts/segoeuib.ttf',
                'C:/Windows/Fonts/arialbd.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
                '/usr/share/fonts/truetype/liberation2/LiberationSans-Bold.ttf',
            ]
            : [
                'C:/Windows/Fonts/segoeui.ttf',
                'C:/Windows/Fonts/arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation2/LiberationSans-Regular.ttf',
            ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }

    private static function logoPath(): ?string
    {
        $candidates = [
            public_path('storage/images/logo_color.png'),
            storage_path('app/public/images/logo_color.png'),
            public_path('storage/images/new/logo_color.png'),
            storage_path('app/public/images/new/logo_color.png'),
            public_path('storage/images/logo light.png'),
            storage_path('app/public/images/logo light.png'),
            public_path('storage/images/logo.png'),
            storage_path('app/public/images/logo.png'),
            public_path('storage/images/favicon.png'),
            storage_path('app/public/images/favicon.png'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
