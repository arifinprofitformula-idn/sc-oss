<?php
declare(strict_types=1);

namespace App\Services\Email;

class TrackingService
{
    public function injectPixel(string $html, string $messageId): string
    {
        $pixelUrl = route('email.track.open', ['id' => $messageId]);
        $pixel = '<img src="' . e($pixelUrl) . '" alt="" width="1" height="1" style="display:none" />';
        if (str_contains($html, '</body>')) {
            return str_replace('</body>', $pixel . '</body>', $html);
        }
        return $html . $pixel;
    }

    public function rewriteLinks(string $html, string $messageId): string
    {
        return preg_replace_callback('/href="([^"]+)"/i', function ($matches) use ($messageId) {
            $url = $matches[1];
            $track = route('email.track.click', ['id' => $messageId]) . '?u=' . urlencode($url);
            return 'href="' . e($track) . '"';
        }, $html) ?? $html;
    }
}

