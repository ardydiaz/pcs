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

        return self::applyLogoToPng($png);
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
        $targetLogoSize = (int) round(min($qrWidth, $qrHeight) * 0.26);

        $scale = min($targetLogoSize / max($logoWidth, 1), $targetLogoSize / max($logoHeight, 1));
        $targetWidth = max(1, (int) round($logoWidth * $scale));
        $targetHeight = max(1, (int) round($logoHeight * $scale));
        $padding = max(10, (int) round($targetLogoSize * 0.24));
        $boxWidth = $targetWidth + ($padding * 2);
        $boxHeight = $targetHeight + ($padding * 2);
        $boxX = (int) round(($qrWidth - $boxWidth) / 2);
        $boxY = (int) round(($qrHeight - $boxHeight) / 2);

        $white = imagecolorallocate($qr, 255, 255, 255);
        imagefilledrectangle($qr, $boxX, $boxY, $boxX + $boxWidth, $boxY + $boxHeight, $white);

        imagecopyresampled(
            $qr,
            $logo,
            $boxX + $padding,
            $boxY + $padding,
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
