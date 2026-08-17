<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content report — My Gig Guide</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 640px; margin: 0 auto; padding: 20px; background: #f8fafc; }
        .container { background: #fff; border-radius: 12px; padding: 32px; box-shadow: 0 4px 6px rgba(0,0,0,.08); }
        h1 { font-size: 22px; margin: 0 0 8px; color: #111827; }
        .meta { color: #6b7280; font-size: 14px; margin-bottom: 24px; }
        .field { margin-bottom: 16px; }
        .label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; margin-bottom: 4px; }
        .value { font-size: 15px; color: #111827; }
        .message { background: #f3f4f6; border-radius: 8px; padding: 12px 14px; white-space: pre-wrap; }
        .btn { display: inline-block; margin-top: 20px; padding: 10px 18px; background: #6366f1; color: #fff !important; text-decoration: none; border-radius: 8px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <h1>New content report</h1>
        <p class="meta">Submitted {{ $report->created_at?->format('M j, Y g:i A') ?? 'just now' }}</p>

        <div class="field">
            <div class="label">Listing</div>
            <div class="value">{{ $report->reportableTypeLabel() }}: {{ $report->reportableTitle() }}</div>
        </div>

        <div class="field">
            <div class="label">Category</div>
            <div class="value">{{ $report->categoryLabel() }}</div>
        </div>

        <div class="field">
            <div class="label">Reporter</div>
            <div class="value">
                {{ $report->user?->name ?? 'Unknown' }}
                @if($report->user?->email)
                    &lt;{{ $report->user->email }}&gt;
                @endif
            </div>
        </div>

        @if($report->message)
            <div class="field">
                <div class="label">Note</div>
                <div class="message">{{ $report->message }}</div>
            </div>
        @endif

        <a href="{{ $adminUrl }}" class="btn">Open in admin</a>
    </div>
</body>
</html>
