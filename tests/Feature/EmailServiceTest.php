<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\Email\TemplateEngine;

class EmailServiceTest extends TestCase
{
    public function test_template_engine_sanitizes_script(): void
    {
        $engine = new TemplateEngine();
        $html = '<div>Hello</div><script>alert(1)</script>';
        $rendered = $engine->renderHtml($html, []);
        $this->assertStringNotContainsString('<script', $rendered);
    }
}

