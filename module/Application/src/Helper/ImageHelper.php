<?php

declare(strict_types=1);

namespace Application\Helper;

class ImageHelper
{
    public static function getOptimizedPath(string $imagePath): string
    {
        $webpPath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $imagePath);

        $publicPath = getcwd() . '/public/' . ltrim($webpPath, '/');

        if (file_exists($publicPath)) {
            return $webpPath;
        }

        return $imagePath;
    }

    public static function generatePicture(callable $basePath, string $imagePath, string $alt = '', string $attributes = ''): string
    {
        $webpPath = preg_replace('/\.(png|jpg|jpeg)$/i', '.webp', $imagePath);

        $html = '<picture>';
        $html .= '<source srcset="' . htmlspecialchars($basePath($webpPath)) . '" type="image/webp">';
        $html .= '<img src="' . htmlspecialchars($basePath($imagePath)) . '" alt="' . htmlspecialchars($alt) . '" ' . $attributes . '>';
        $html .= '</picture>';

        return $html;
    }
}
