<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spotify — My Gig Guide</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #111827; color: #f9fafb; padding: 24px; }
        .card { max-width: 420px; background: #1f2937; border-radius: 16px; padding: 24px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,.35); }
        h1 { font-size: 1.25rem; margin: 0 0 12px; }
        p { margin: 0; line-height: 1.5; color: #d1d5db; }
        .ok { color: #34d399; }
        .bad { color: #f87171; }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="{{ $success ? 'ok' : 'bad' }}">{{ $success ? 'Connected' : 'Not connected' }}</h1>
        <p>{{ $message }}</p>
    </div>
</body>
</html>
