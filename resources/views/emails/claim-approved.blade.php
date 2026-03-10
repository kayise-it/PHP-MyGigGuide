<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claim Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #ecfdf5; border-left: 4px solid #10b981; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
        <h2 style="color: #065f46; margin: 0;">✓ Artist Profile Claim Approved!</h2>
    </div>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Great news! Your claim to the artist profile <strong>'{{ $artist->stage_name }}'</strong> has been approved by our admin team.</p>
    
    <p>The artist profile has been successfully linked to your account. You can now manage your artist profile, update your information, and add events.</p>
    
    <p><a href="{{ route('dashboard.artist') }}" style="background: #10b981; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; display: inline-block; margin-top: 20px;">Go to Artist Dashboard</a></p>
    
    <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
        Best regards,<br>
        The My Gig Guide Team
    </p>
</body>
</html>







