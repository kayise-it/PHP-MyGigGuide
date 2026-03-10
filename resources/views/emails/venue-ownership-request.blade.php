<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Venue Ownership Request - My Gig Guide</title>
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
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #7c3aed;
            margin-bottom: 10px;
        }
        .title {
            font-size: 28px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #6b7280;
            font-size: 16px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
            font-size: 16px;
            color: #374151;
        }
        .button {
            display: inline-block;
            background: #7c3aed;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin: 20px 0;
        }
        .button:hover {
            background: #6d28d9;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .info-box strong {
            color: #1f2937;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">My Gig Guide</div>
            <h1 class="title">New Venue Ownership Request</h1>
            <p class="subtitle">Someone wants to join your venue</p>
        </div>

        <div class="content">
            <p>Hello,</p>
            
            <p>A user has requested to be added as an owner of your venue <strong>{{ $venue->name }}</strong>.</p>

            <div class="info-box">
                <p><strong>Requester Details:</strong></p>
                <p><strong>Name:</strong> {{ $requester->name }}</p>
                <p><strong>Email:</strong> {{ $requester->email }}</p>
                <p><strong>Reason:</strong> {{ $request->reason }}</p>
                @if($request->proof_document_path)
                <p><strong>Proof Document:</strong> Attached</p>
                @endif
            </div>

            <p>To review and approve or reject this request, please log in to your account and visit the venue management page.</p>

            <div style="text-align: center;">
                <a href="{{ route('venues.show', $venue) }}" class="button">View Venue & Manage Owners</a>
            </div>

            <p>If you believe this request is legitimate, you can approve it from your venue's management page. If not, you can reject it with a reason.</p>
        </div>

        <div class="footer">
            <p>Best regards,<br>The My Gig Guide Team</p>
        </div>
    </div>
</body>
</html>

