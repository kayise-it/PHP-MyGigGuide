<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Profile Claim Alert</title>
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
            background: #dc2626;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            margin: 10px 5px;
        }
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #dc2626;">⚠️ Artist Profile Claim Alert</h1>
        
        <p>Hello,</p>
        
        <div class="alert-box">
            <p style="margin: 0; font-weight: 600; color: #92400e; margin-bottom: 10px;">IMPORTANT: Someone has registered with your email address</p>
            <p style="margin: 0;">An account was created using the email address <strong>{{ $artist->contact_email }}</strong> which is linked to the artist profile '<strong>{{ $artist->stage_name }}</strong>' on My Gig Guide.</p>
        </div>

        <h3>Account Details:</h3>
        <div class="info-box">
            <p><strong>Registrant Name:</strong> {{ $user->name }}</p>
            <p><strong>Registration Email:</strong> {{ $user->email }}</p>
            <p><strong>Artist Profile:</strong> {{ $artist->stage_name }}</p>
            @if($gracePeriodEnds)
            <p><strong>Auto-Claim Date:</strong> {{ $gracePeriodEnds->format('F j, Y \a\t g:i A') }} ({{ $gracePeriodEnds->diffForHumans() }})</p>
            @endif
        </div>

        @if($gracePeriodEnds)
        <p><strong>If this is NOT you:</strong> The profile will be automatically claimed by this person after the grace period expires. Please dispute this claim immediately!</p>
        @else
        <p><strong>If this is NOT you:</strong> Please dispute this claim immediately to prevent unauthorized access to your artist profile!</p>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $disputeUrl }}" class="button">🚨 Dispute This Claim</a>
        </div>

        <p><strong>If this IS you:</strong> You can safely ignore this email. Your artist profile will be linked to your account after email verification.</p>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            Best regards,<br>
            The My Gig Guide Team
        </p>
    </div>
</body>
</html>







