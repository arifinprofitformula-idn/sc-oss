<?php
declare(strict_types=1);

namespace App\Services\Email;

use Illuminate\Support\Str;

class TemplateEngine
{
    public function renderString(string $template, array $data): string
    {
        $safe = $this->sanitize($template);
        return $this->merge($safe, $data);
    }

    public function renderHtml(string $template, array $data): string
    {
        $safe = $this->sanitizeHtml($template);
        $merged = $this->merge($safe, $data);
        return $merged;
    }

    protected function merge(string $text, array $data): string
    {
        $map = [];
        foreach ($data as $key => $value) {
            $map['${' . $key . '}'] = (string) $value;
            $map['{{' . $key . '}}'] = (string) $value;
        }
        return strtr($text, $map);
    }

    protected function sanitize(string $text): string
    {
        return trim($text);
    }

    protected function sanitizeHtml(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        return $html ?? '';
    }
}

