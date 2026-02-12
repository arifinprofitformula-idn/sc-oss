<?php

use App\Services\Email\TemplateEngine;

$engine = new TemplateEngine();

$template = "Hello {{name}}, welcome to {{app_name}}!";
$data = [
    'name' => 'John',
    'app_name' => 'EPI OSS'
];

$rendered = $engine->renderString($template, $data);
echo "Rendered: " . $rendered . "\n";
