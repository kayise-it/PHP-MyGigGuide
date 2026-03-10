<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address - My Gig Guide</title>
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
        .highlight {
            background: #fef3c7;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #f59e0b;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">My Gig Guide</div>
            <h1 class="title">Verify Your Email Address</h1>
            <p class="subtitle">Complete your account setup</p>
        </div>

        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            @if($unclaimedArtist ?? null)
                <div class="highlight">
                    <p style="margin: 0; font-weight: 600; color: #92400e; margin-bottom: 10px;">🎵 Artist Profile Found!</p>
                    <p style="margin: 0;">We found an artist profile '<strong>{{ $unclaimedArtist->stage_name }}</strong>' linked to your email address. Once you verify your email, this profile will be automatically claimed and linked to your account!</p>
                </div>
            @endif
            
            <p>Thank you for registering with My Gig Guide! To complete your account setup{{ $unclaimedArtist ?? null ? ' and claim your artist profile' : '' }}, please verify your email address.</p>

            <p>Click the button below to verify your email address:</p>

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">{{ $unclaimedArtist ?? null ? 'Verify & Claim Profile' : 'Verify Email Address' }}</a>
            </div>

            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p style="word-break: break-all; background: #f3f4f6; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 14px;">
                {{ $verificationUrl }}
            </p>

            <p>This verification link will expire in 24 hours for security reasons.</p>

            <p>If you didn't create an account with My Gig Guide, you can safely ignore this email.</p>
        </div>

        <div class="footer">
            <p>Best regards,<br>The My Gig Guide Team</p>
        </div>
    </div>
</body>
</html>



