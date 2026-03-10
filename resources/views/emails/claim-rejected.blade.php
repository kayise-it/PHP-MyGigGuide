<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Claim Rejected</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: #fef2f2; border-left: 4px solid #ef4444; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
        <h2 style="color: #991b1b; margin: 0;">Artist Profile Claim Rejected</h2>
    </div>
    
    <p>Hello {{ $user->name }},</p>
    
    <p>Unfortunately, your claim to the artist profile <strong>'{{ $artist->stage_name }}'</strong> has been rejected by our admin team.</p>
    
    @if($reason)
    <div style="background: #f3f4f6; padding: 15px; border-radius: 8px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Reason:</strong></p>
        <p style="margin: 5px 0 0 0;">{{ $reason }}</p>
    </div>
    @endif
    
    <p>If you believe this is an error or have additional information to support your claim, please contact our support team.</p>
    
    <p style="margin-top: 30px; color: #6b7280; font-size: 14px;">
        Best regards,<br>
        The My Gig Guide Team
    </p>
</body>
</html>







