<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New claim to review</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8fafc;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .alert-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            background: #7c3aed;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px 10px 0;
        }
        .button-secondary {
            background: #4b5563;
        }
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-box {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #92400e; margin-top: 0;">New {{ ucfirst($entityType) }} claim to review</h1>

        <div class="alert-box">
            <p style="margin: 0; font-weight: 600; color: #92400e;">
                Someone has requested to manage <strong>{{ $entityName }}</strong>.
            </p>
        </div>

        <h3>Claimant</h3>
        <div class="info-box">
            <p><strong>Name:</strong> {{ $claimant->name ?: 'Not provided' }}</p>
            <p><strong>Email:</strong> {{ $claimant->email }}</p>
            <p><strong>User ID:</strong> {{ $claimant->id }}</p>
        </div>

        <h3>Page</h3>
        <div class="info-box">
            <p><strong>Type:</strong> {{ ucfirst($entityType) }}</p>
            <p><strong>Name:</strong> {{ $entityName }}</p>
            <p><strong>ID:</strong> {{ $entity->id }}</p>
            @if($entity->getClaimEmail())
                <p><strong>Listing email:</strong> {{ $entity->getClaimEmail() }}</p>
            @endif
        </div>

        @if($claimMessage)
            <h3>Message from claimant</h3>
            <div class="message-box">{{ $claimMessage }}</div>
        @endif

        <div style="margin: 30px 0;">
            <a href="{{ $reviewUrl }}" class="button">Review &amp; approve claim</a>
            <a href="{{ $pendingListUrl }}" class="button button-secondary">All pending {{ $entityType }} claims</a>
        </div>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            My Gig Guide admin notification
        </p>
    </div>
</body>
</html>
