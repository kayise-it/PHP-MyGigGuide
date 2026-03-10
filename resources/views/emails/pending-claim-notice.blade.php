<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artist Profile Claim Pending</title>
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
        .info-box {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 style="color: #7c3aed;">🎵 Artist Profile Claim Pending</h1>
        
        <p>Hello,</p>
        
        <div class="info-box">
            <p style="margin: 0; font-weight: 600; color: #065f46; margin-bottom: 10px;">Great News!</p>
            <p style="margin: 0;">We found an artist profile '<strong>{{ $artist->stage_name }}</strong>' linked to your email address. Once you verify your email, this profile will be claimed and linked to your account!</p>
        </div>

        <h3>Artist Profile Details:</h3>
        <ul>
            <li><strong>Stage Name:</strong> {{ $artist->stage_name }}</li>
            @if($artist->genre)
            <li><strong>Genre:</strong> {{ $artist->genre }}</li>
            @endif
            @if($artist->contact_email)
            <li><strong>Contact Email:</strong> {{ $artist->contact_email }}</li>
            @endif
        </ul>

        @if($gracePeriodEnds)
        <p><strong>Claim Status:</strong> Your claim will be automatically processed after <strong>{{ $gracePeriodEnds->diffForHumans() }}</strong> ({{ $gracePeriodEnds->format('F j, Y \a\t g:i A') }}) if no disputes are raised.</p>
        @else
        <p><strong>Claim Status:</strong> Your claim will be processed immediately after email verification.</p>
        @endif

        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Verify your email address using the link in your verification email</li>
            @if($gracePeriodEnds)
            <li>Wait for the grace period to expire ({{ $gracePeriodEnds->diffForHumans() }})</li>
            @endif
            <li>Your artist profile will be automatically linked to your account</li>
        </ol>

        <p style="color: #6b7280; font-size: 14px; margin-top: 30px;">
            Best regards,<br>
            The My Gig Guide Team
        </p>
    </div>
</body>
</html>







