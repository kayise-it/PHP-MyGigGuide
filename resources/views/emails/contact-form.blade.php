<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission - My Gig Guide</title>
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
        .info-box {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 12px;
        }
        .info-label {
            font-weight: 600;
            color: #374151;
            display: inline-block;
            min-width: 100px;
        }
        .info-value {
            color: #6b7280;
        }
        .message-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .message-text {
            white-space: pre-wrap;
            color: #374151;
            font-size: 16px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 14px;
            color: #6b7280;
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-yes {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-no {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">My Gig Guide</div>
            <h1 class="title">New Contact Form Submission</h1>
            <p class="subtitle">You have received a new message from the contact form</p>
        </div>

        <div class="content">
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $name }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value"><a href="mailto:{{ $email }}" style="color: #7c3aed; text-decoration: none;">{{ $email }}</a></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Subject:</span>
                    <span class="info-value">{{ $contactSubject }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Newsletter:</span>
                    <span class="info-value">
                        <span class="badge {{ $newsletter ? 'badge-yes' : 'badge-no' }}">
                            {{ $newsletter ? 'Subscribed' : 'Not Subscribed' }}
                        </span>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Submitted:</span>
                    <span class="info-value">{{ $submittedAt->format('F j, Y \a\t g:i A') }}</span>
                </div>
            </div>

            <div class="message-box">
                <h3 style="margin: 0 0 15px 0; color: #92400e; font-size: 18px;">Message:</h3>
                <div class="message-text">{{ $contactMessage }}</div>
            </div>

            <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
                You can reply directly to this email to respond to {{ $name }}.
            </p>
        </div>

        <div class="footer">
            <p>This email was sent from the My Gig Guide contact form.</p>
            <p style="margin-top: 10px;">Best regards,<br>The My Gig Guide System</p>
        </div>
    </div>
</body>
</html>
