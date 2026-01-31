<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: {{ $emailTemplate->subject }}</title>
</head>
<body style="font-family: sans-serif; padding: 20px;">
    <div style="max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px;">
        <h2>Subject: {{ $emailTemplate->subject }}</h2>
        <hr>
        <div>
            {!! $content !!}
        </div>
    </div>
</body>
</html>
